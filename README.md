kodus/image-hash
================

This library implements the aHash, dHash and pHash image-hashing algorithms as described
in [this article](http://www.hackerfactor.com/blog/?/archives/529-Kind-of-Like-That.html).

It's based on [this implementation](http://jax-work-archive.blogspot.dk/2013/05/php-ahash-phash-dhash.html)
but with a loader-abstraction for GD and Imagick support, added tests, and a benchmark.

## Usage

To compare two images:

```php
use Kodus\ImageHash\ImageHasher;

$hasher = new ImageHasher();

$a_hash = $hasher->pHash("path/to/image-a.jpg");
$b_hash = $hasher->pHash("path/to/image-b.jpg");

if ($hasher->getDistance($a_hash, $b_hash) <= 2) {
    echo "same!";
} else {
    echo "different.";
}
```

Substitute calls to `pHash()` for `aHash()` or `dHash()` to use another algorithm, but note
that `getDistance()` only makes sense for two hashes computed using the same algorithm.

Hashes are returned as a binary-mask string - use [base_convert](http://php.net/base_convert)
if you need a decimal or hex value.

## Hacking

To run the tests:

    php test/test.php

To run the benchmark:

    php test/benchmark.php

If you hack on this library, be sure to run the benchmark before/after making changes.
