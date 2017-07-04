<?php

function compile_routes(array $routes)
{
	$c = [];
	foreach (array_keys($routes) as $route)
	{
		$route = trim($route);
		$route = preg_replace('/\s{2,}/', ' ', $route);
		preg_match('~^(GET|POST|PUT|DELETE) (/.*)~', $route, $matches);
		$method = $matches[1];
		$uri = explode('/', $matches[2]);
		$count = sizeof($uri)-1;
		$handler = $routes[$route];
		
		if (!isset($c[$method]))
			$c[$method] = [];

		if (!isset($c[$method][$count]))
			$c[$method][$count] = [];

		$d = &$c[$method][$count];
		for ($i=1; $i<=$count; $i++)
		{
			$urii = $uri[$i];
			
			if (!isset($d[$urii]))
				$d[$urii] = [];
			$d = &$d[$urii];
		}

		$d = $handler;
	}
	
	return $c;
}