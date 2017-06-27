<?php

use Kodus\ImageHash\GDLoader;
use Kodus\ImageHash\ImageHasher;
use Kodus\ImageHash\ImagickLoader;
use Kodus\ImageHash\Loader;

require dirname(__DIR__) . '/vendor/autoload.php';

function test_loader(Loader $loader)
{
    $bitmap = $loader->load(__DIR__ . '/img/lena-high.jpg', 5, 5);

    $expected = [
        [128, 124, 149, 137, 106],
        [115, 127, 155, 164, 116],
        [116, 90, 130, 107, 151],
        [107, 86, 117, 108, 167],
        [106, 73, 125, 156, 116],
    ];

    for ($y = 0; $y < 5; $y++) {
        for ($x = 0; $x < 5; $x++) {
            // NOTE: because of resize algorithm differences, we have to
            // tolerate a difference of +/- 1 in the loaded grayscale bitmaps.

            ok(abs($bitmap[$y][$x] - $expected[$y][$x]) <= 1);
        }
    }
}

function test_hashes(Loader $loader = null)
{
    $hasher = new ImageHasher($loader);

    $high = __DIR__ . "/img/lena-high.jpg";
    $low = __DIR__ . "/img/lena-low.jpg";
    $small = __DIR__ . "/img/lena-small.jpg";
    $control = __DIR__ . "/img/hummingbird.jpg";

    $algos = ["aHash", "dHash", "pHash"];

    foreach ($algos as $algo) {
        $hash_high = $hasher->$algo($high);
        $hash_low = $hasher->$algo($low);
        $hash_small = $hasher->$algo($small);
        $hash_control = $hasher->$algo($control);

        // distance between low/high/small versions of the Lena image should be low:

        ok($hasher->getDistance($hash_high, $hash_low) <= 1);
        ok($hasher->getDistance($hash_low, $hash_small) <= 1);
        ok($hasher->getDistance($hash_small, $hash_high) <= 1);

        // distance between Lena and the control image should be high:

        ok($hasher->getDistance($hash_control, $hash_low) > 20);
        ok($hasher->getDistance($hash_control, $hash_high) > 20);
        ok($hasher->getDistance($hash_control, $hash_small) > 20);
    }
}

function run_tests(Loader $loader)
{
    test_loader($loader);
    test_hashes($loader);
}

test(
    "GD support",
    function () {
        run_tests(new GDLoader());
    }
);

test(
    "Imagick support",
    function () {
        run_tests(new ImagickLoader());
    }
);

test(
    "auto-detection support",
    function () {
        test_hashes();
    }
);

exit(run());
