<?php

namespace Kodus\ImageHash;

use Imagick;

class ImagickLoader implements Loader
{
    public function load(string $path, int $width, int $height): array
    {
        $image = new Imagick($path);

        // NOTE: FILTER_BOX produces the most similar result to GD's imagecopyresampled()

        $image->resizeImage($width, $height, Imagick::FILTER_BOX, 1);

        $bitmap = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = $image->getImagePixelColor($x, $y)->getColor();

                $bitmap[$y][$x] = intval($color['r'] * 0.299 + $color['g'] * 0.587 + $color['b'] * 0.114);
            }
        }

        $image->destroy();

        return $bitmap;
    }
}
