<?php

require_once 'vendor/autoload.php';
require 'compile.php';
require 'test.php';
require 'lookup.php';

function faker_from_regex($regex)
{
	$faker = Faker\Factory::create();
	if ($regex == '[a-zA-Z]+')
		return $faker->word();
	else if ($regex == '[a-zA-Z_]+')
		return $faker->word().'_'.$faker->word();
	else if ($regex == '\d+')
		return $faker->randomNumber(3);
	else if ($regex == '\w+')
		return $faker->word().'_'.$faker->word().$faker->randomNumber(3);
	else if ($regex == '[\w\d]+')
		return $faker->word().'_'.$faker->word().$faker->randomNumber(3);
}

function run_tests($num_routes, $num_tests=20000)
{
	$script_start_time = microtime(true);

	$gen_time = 0;
	$compile_time = 0;

	$start_time = microtime(true);
	$routes = gen_test_routes($num_routes);
	$gen_time = microtime(true) - $start_time;

	$start_time = microtime(true);
	$compiled_routes = compile_routes($routes);
	$compile_time = microtime(true) - $start_time;

	$faker_start_time = microtime(true);
	$faker_time = 0;
	$route_keys = array_keys($routes);
	$fake_requests = [];
	for ($i=0; $i<$num_tests; $i++)
	{
		$j = array_rand($route_keys);

		$route = explode(' /', $route_keys[$j]);
		$method = $route[0];
		$route = explode('/', $route[1]);

		foreach ($route as $k => $r) 
		{
			if (preg_match('/\{(\w+):(.+)\}/', $r, $matches))
			{
				$var = $matches[1];
				$exp = $matches[2];
				$data = faker_from_regex($exp);
				$route[$k] = $data;
			}
		}

		$fake_requests[] = [$method, '/'. implode('/', $route)];
	}
	$faker_time += microtime(true) - $faker_start_time;

	$start_time = microtime(true);
	$options = [];
	$fast_route = FastRoute\simpleDispatcher(function($fast_route) use ($routes) {
	    foreach ($routes as $route => $handler) {
	    	$route = explode(' ', $route);
	        $fast_route->addRoute($route[0], $route[1], $handler);
	    }
	}, $options);
	$add_route_time = microtime(true) - $start_time; 

	$foxy_route_start_time = microtime(true);
	foreach ($fake_requests as $fake_request)
		lookup($compiled_routes, $fake_request[0].' '.$fake_request[1]);
	$foxy_route_time = microtime(true) - $foxy_route_start_time;

	$fast_route_start_time = microtime(true);
	foreach ($fake_requests as $fake_request)
		$fast_route->dispatch($fake_request[0], $fake_request[1]);
	$fast_route_time = microtime(true) - $fast_route_start_time;

	$start_time = microtime(true);
	$h = fopen('routes.php','w');
	fwrite($h, "<?php\nreturn ". var_export($routes, true) .";\n");
	fclose($h);

	$h = fopen('compiled_routes.php','w');
	fwrite($h, "<?php\nreturn ". var_export($compiled_routes, true) .";\n");
	fclose($h);

	$h = fopen('fake_requests.php','w');
	fwrite($h, "<?php\nreturn ". var_export($fake_requests, true) .";\n");
	fclose($h);
	$file_write_time = microtime(true) - $start_time;

	$script_time = microtime(true) - $script_start_time;

	printf("\n\n");
	printf("Generated $num_routes test routes in %.5fs (%.2f%%)\n", $gen_time, $gen_time*100/$script_time);
	printf("Compiled $num_routes test routes in %.5fs (%.2f%%)\n", $compile_time, $compile_time*100/$script_time);
	printf("Generated $num_tests fake requests in %.5fs (%.2f%%)\n", $faker_time, $faker_time*100/$script_time);
	printf("Wrote files in %.5fs (%.2f%%)\n", $file_write_time, $file_write_time*100/$script_time);
	printf("Script ran in %.5fs\n", $script_time);
	printf("\n");
	printf("Fast Route Statistics\n");
	printf("=====================\n");
	printf("Routed $num_tests requests in %.5fs (%.2f%%)\n", $fast_route_time, $fast_route_time*100/$script_time);
	printf("Average routing time: %.2f us\n", $fast_route_time*1000000/$num_tests);
	printf("\n");
	printf("Foxy Route Statistics\n");
	printf("=====================\n");
	printf("Routed $num_tests requests in %.5fs (%.2f%%)\n", $foxy_route_time, $foxy_route_time*100/$script_time);
	printf("Average routing time: %.2f us\n", $foxy_route_time*1000000/$num_tests);

	return [$num_routes, $foxy_route_time, $fast_route_time];
}

$num_routes = 2000;
$num_tests = 20000;

$results = [];
for ($i=100; $i<=5000; $i+=100)
	$results[] = run_tests($num_routes=$i);

$h = fopen('results.csv','w');
foreach ($results as $r)
	fputcsv($h, $r);
fclose($h);

/*
if (!file_exists('routes.php'))
{
	$start_time = microtime(true);
	$routes = gen_test_routes($num_routes);
	$gen_time = microtime(true) - $start_time;

	$h = fopen('routes.php','w');
	fwrite($h, "<?php\nreturn ". var_export($routes, true) .";\n");
	fclose($h);

	$start_time = microtime(true);
	$compiled_routes = compile_routes($routes);
	$compile_time = microtime(true) - $start_time;

	$h = fopen('compiled.php','w');
	fwrite($h, "<?php\nreturn ". var_export($compiled_routes, true) .";\n");
	fclose($h);

	printf("Created 'routes.php' and 'compiled.php'\n");
}
else if (!file_exists('compiled.php'))
{
	$routes = include('routes.php');
	
	$start_time = microtime(true);
	$compiled_routes = compile_routes($routes);
	$compile_time = microtime(true) - $start_time;

	$h = fopen('compiled.php','w');
	fwrite($h, "<?php\nreturn ". var_export($compiled_routes, true) .";\n");
	fclose($h);

	printf("Created 'compiled.php' from 'routes.php'\n");
}
else
{
	printf("Found 'compiled.php' and 'routes.php'\n");
}

ob_start();
$routes = include('routes.php');
$compiled_routes = include('compiled.php');
ob_end_clean();
*/


/*
// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = trim(rawurldecode($uri));

echo "$httpMethod $uri<br>";

$uri = explode('/', $uri);

echo sizeof($uri)."<br>";
*/

/*
// manual test data
$routes_orig = [
	'GET /' => ['MainController', 'someMethod'],
	'GET /hello' => ['MainContr', 'helloMetho'],
	'GET /hello/hi' => ['SomeController', 'hi'],
	'GET /hello/{id:\w+}' => ['FunContr', 'he'],
	'GET /hello/hi/bye' => ['EtcControl', 'wh'],
	'GET /hello/{id:\w+}/bye' => ['EtcControl', 'wa'],
	'POST /register/submit' => ['RegCon', 'submit'],
];

$routes_compiled = [
	'GET' => [
		1 => [
			'' => ['MainController', 'someMethod'],
			'hello' => ['MainContr', 'helloMetho'],
		],
		2 => [
			'hello' => [
				'hi' => ['SomeController', 'hi'],
				'{id:\w+}' => ['FunContr', 'he'],
			],
		],
		3 => [
			'hello' => [
				'hi' => [
					'bye' => ['EtcControl', 'wh'],
				],
				'{id:\w+}' => [
					'bye' => ['EtcControl', 'wa'],
				],
			]
		]
	],
	'POST' => [
		2 => [
			'register' => [
				'submit' => ['RegCon', 'submit'],
			],
		],
	],
];


$compiled_routes = compile_lookup($routes_orig);
var_dump($routes_compiled === $compiled_routes);
echo "<br>";

*/