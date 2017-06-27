<?php

namespace Kodus\ImageHash;

interface Loader
{
    /**
     * @param string $path
     * @param int    $width
     * @param int    $height
     *
     * @return int[][]
     */
    public function load(string $path, int $width, int $height): array;
}
