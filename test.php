<?php

require_once 'vendor/autoload.php';

function gen_test_routes($num_routes=100, $min_depth=1, $max_depth=10)
{
	if ($min_depth >= $max_depth)
		die("min_depth must be <= max_depth");

	$routes = [];
	$faker = Faker\Factory::create();

	$methods = ['GET','POST','PUT','DELETE'];
	$depths = range($min_depth, $max_depth);
	$regexes = ['','','','\d+'];

	for ($i=0; $i<$num_routes; $i++)
	{
		$depth = $depths[array_rand($depths)];
		$method = $methods[array_rand($methods)];

		$words = [];
		$route = '';
		for ($j=0; $j<$depth; $j++)
		{
			// ensure all the words for this route are distinct
			/*
			do {
				$word = $faker->word();
			} while (isset($words[$word]));
			*/

			$word = $faker->word();

			$words[$word] = true;


			$regex = $regexes[array_rand($regexes)];
			if ($regex)
			{
				$route .= '/{arg'. $j .':'. $regex .'}';
			}
			else
			{
				$route .= "/$word";
			}
		}

		$routes["$method $route"] = ['C'.$faker->firstName.$faker->lastName, implode('_', $faker->words())];
	}

	return $routes;
}