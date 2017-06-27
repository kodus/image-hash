<?php

namespace Kodus\ImageHash;

use Imagick;
use RuntimeException;

/**
 * This class implements the "aHash", "dHash" and "pHash" image hashing algorithms.
 *
 * @see http://jax-work-archive.blogspot.dk/2013/05/php-ahash-phash-dhash.html
 */
class ImageHasher
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @param Loader|null $loader optional Loader instance (defaults to auto-detection of GD or Imagick)
     */
    public function __construct(Loader $loader = null)
    {
        $this->loader = $loader ?: $this->createLoader();
    }

    /**
     * @param string $path
     *
     * @return string hash
     */
    public function aHash($path)
    {
        $bitmap = $this->loader->load($path, 8, 8);

        $gray_sum = 0;
        $grays = [];

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $gray = $bitmap[$y][$x];
                $grays[] = $gray;
                $gray_sum += $gray;
            }
        }

        $average = $gray_sum / 64;

        foreach ($grays as $i => $gray) {
            $grays[$i] = ($gray >= $average) ? '1' : '0';
        }

        return join('', $grays);
    }

    /**
     * @param string $path
     *
     * @return string hash
     */
    public function pHash($path)
    {
        $bitmap = $this->loader->load($path, 32, 32);

        $dctConst = self::getDctTable();
        $dct_sum = 0;
        $bits = [];

        for ($dctY = 0; $dctY < 8; $dctY++) {
            for ($dctX = 0; $dctX < 8; $dctX++) {

                $sum = 1;

                for ($y = 0; $y < 32; $y++) {
                    for ($x = 0; $x < 32; $x++) {
                        $sum += $dctConst[$dctY][$y] * $dctConst[$dctX][$x] * $bitmap[$y][$x];
                    }
                }

                $sum *= .25;

                if ($dctY == 0 || $dctX == 0) {
                    $sum *= 1 / sqrt(2);
                }

                $bits[] = $sum;
                $dct_sum += $sum;
            }
        }

        $average = $dct_sum / 64;

        foreach ($bits as $i => $dct) {
            $bits[$i] = ($dct >= $average) ? '1' : '0';
        }

        return join('', $bits);
    }

    /**
     * @param string $path
     *
     * @return string hash
     */
    public function dHash($path)
    {
        $bitmap = $this->loader->load($path, 9, 8);

        $bits = [];

        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits[] = ($bitmap[$y][$x] < $bitmap[$y][$x + 1]) ? '1' : '0';
            }
        }

        return join('', $bits);
    }

    /**
     * @param string $hash_a
     * @param string $hash_b
     *
     * @return int
     */
    public function getDistance($hash_a, $hash_b)
    {
        $aL = strlen($hash_a);
        $bL = strlen($hash_b);

        if ($aL !== $bL) {
            return false;
        }

        $distance = 0;

        for ($i = 0; $i < $aL; $i++) {
            if ($hash_a{$i} !== $hash_b{$i}) {
                $distance++;
            }
        }

        return $distance;
    }

    private function createLoader(): Loader
    {
        if (extension_loaded("gd")) {
            return new GDLoader();
        } else {
            if (class_exists(Imagick::class)) {
                return new ImagickLoader();
            }
        }

        throw new RuntimeException("neither GD or Imagick extension is available");
    }

    /**
     * @return float[][]
     */
    private static function getDctTable()
    {
        static $table;

        if (! $table) {
            $table = [];

            for ($dct_p = 0; $dct_p < 8; $dct_p++) {
                for ($p = 0; $p < 32; $p++) {
                    $table[$dct_p][$p] = cos(((2 * $p + 1) / 64) * $dct_p * pi());
                }
            }
        }

        return $table;
    }
}
