<?php

function lookup($compiled_routes, $route, &$args=null)
{
	$p = explode(' /', $route);
	$method = $p[0];
	$uri = explode('/', $p[1]);
	$size = sizeof($uri);

	$args = [];
	$c = &$compiled_routes[$method][$size];

	foreach ($uri as $u)
	{
		// if exact match is found
		if (isset($c[$u]))
		{
			$c = &$c[$u];
			continue;
		}

		// if no exact match, look for regex keys at the current depth
		foreach ($c as $k => $v)
		{
			// if a regex key is found,
			if (preg_match('/\{(\w+):(.+)\}/', $k, $matches))
			{
				// extract the reg $exp from the array key
				$var = $matches[1];
				$exp = $matches[2];

				// if the uri part $u matches the reg $exp in the array key,
				// capture the value
				if (preg_match("/^$exp$/", $u, $matches))
				{
					$args[$var] = $matches[0];
					$c = &$c[$k];
					break;
				}
			}
		}
	}
	
	//$c['args'] = $args;
	return $c;
}