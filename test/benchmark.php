<?php

use Kodus\ImageHash\GDLoader;
use Kodus\ImageHash\ImageHasher;
use Kodus\ImageHash\ImagickLoader;
use mindplay\benchpress\Benchmark;

require dirname(__DIR__) . '/vendor/autoload.php';

$loaders = [
    new GDLoader(),
    new ImagickLoader(),
];

$benchmark = new Benchmark(1000, 20);

$algos = ["aHash", "dHash", "pHash"];

$src = __DIR__ . "/img/lena-high.jpg";

foreach ($loaders as $loader) {
    $hasher = new ImageHasher($loader);

    foreach ($algos as $algo) {
        $benchmark->add(
            $algo . " with " . get_class($loader),
            function () use ($hasher, $algo, $src) {
                $hash = $hasher->$algo($src);
            }
        );
    }
}

$benchmark->run();
