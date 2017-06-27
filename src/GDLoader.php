<?php

namespace Kodus\ImageHash;

use RuntimeException;

class GDLoader implements Loader
{
    public function load(string $path, int $width, int $height): array
    {
        $func_map = [
            IMAGETYPE_GIF  => 'imagecreatefromgif',
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG  => 'imagecreatefrompng',
            IMAGETYPE_BMP  => 'imagecreatefrombmp',
            IMAGETYPE_WBMP => 'imagecreatefromwbmp',
            IMAGETYPE_XBM  => 'imagecreatefromxbm',
        ];

        $type = exif_imagetype($path);

        if (! array_key_exists($type, $func_map)) {
            throw new RuntimeException("unsupported image type: {$type}");
        }

        $func = $func_map[$type];

        if (! function_exists($func)) {
            throw new RuntimeException("undefined function: {$func}");
        }

        $source = $func($path);

        $image = imagecreatetruecolor($width, $height);

        imagecopyresampled(
            $image, $source,
            0, 0, 0, 0,
            $width, $height, imagesx($source), imagesy($source)
        );

        imagedestroy($source);

        $bitmap = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));

                $bitmap[$y][$x] = intval($color['red'] * 0.299 + $color['green'] * 0.587 + $color['blue'] * 0.114);
            }
        }

        imagedestroy($image);

        return $bitmap;
    }
}
