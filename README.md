# FoxyRoute

## Benchmark Results

[View benchmark results](https://goo.gl/8XfYLj). Benchmark run using PHP 7.1. **The benchmark needs to be rerun using PHP 5.6 for further analysis.**

    $ php --version
    PHP 7.1.1 (cli) (built: Jan 18 2017 18:51:14) ( ZTS MSVC14 (Visual C++ 2015) x86)
    Copyright (c) 1997-2017 The PHP Group
    Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologies

### Result Files

Benchmark results are provided in `results.csv`, `log.txt`. 

A randomly generated route table `routes.php`, the FoxyRoute compiled route table `compiled_routes.php`, and the fake requests used to profile the dispatcher `fake_requests.php` are provided as examples. These three files return a PHP array and are overwritten every time the benchmark is run.

## Performance Discussion

Both FoxyRoute and FastRoute exhibit linear performance. FoxyRoute is slightly closer to constant-time performance for larger route tables, but it is unclear if this margin is significant.

It is possible that FoxyRoute's performance with larger route tables is thanks to PHP 7's enhanced performance.

## Benchmark Characteristics
 
* Each test dispatches 20,000 requests against a route table of a specific size.
* Route table size is varied from 100 to 5000 in increments of 100.
* For each test, a fake set of resolvable requests is generated.
  * These fake requests are run against both FoxyRoute and FastRoute.
  * Both routers process the same randomized data.
* Routes are random depth with a max depth of 10; depths are equally distributed.
* Every route table has ~25% match blocks e.g. one in every four route URI blocks is a (regex) match block:

    /some/random/route/{id:\d+}

The `{id:\d+}` is a match block.

    $ php index.php