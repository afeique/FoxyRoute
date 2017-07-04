# FoxyRoute

[View benchmark results](https://goo.gl/8XfYLj). Benchmark run using PHP 7.1. **The benchmark needs to be rerun using PHP 5.6 for further analysis.**

    $ php --version
    PHP 7.1.1 (cli) (built: Jan 18 2017 18:51:14) ( ZTS MSVC14 (Visual C++ 2015) x86)
    Copyright (c) 1997-2017 The PHP Group
    Zend Engine v3.1.0, Copyright (c) 1998-2017 Zend Technologies

Both FoxyRoute and FastRoute exhibit linear performance. FoxyRoute is slightly closer to constant-time performance for larger route tables, but it is unclear if this margin is significant.

It is possible that FoxyRoute's performance with larger route tables is thanks to PHP 7's enhanced performance.

* Benchmark runs 50 side-by-side tests of both FoxyRoute and FastRoute. 
* Each test dispatches 20,000 requests to a randomly generated route-table of different size. 
* Route tables start at 100 routes and are incremented by 100 routes per test up to 5000 routes.
* Routes are random depth with a max depth of 10; depths are equally distributed across routes.
* Every route table has ~25% match blocks e.g. one in every four route URI blocks is a (regex) match block:

    /some/random/route/{id:\d+}

The `{id:\d+}` is a match block.

    $ php index.php