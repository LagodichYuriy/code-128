# Code 128 generator
Simple and standalone barcode generator for PHP. Supports 128B (Code Set B):

* ASCII characters 32 to 127 (0–9, A–Z, a–z)
* special characters
* FNC 1–4.

# PHP requirements:
* PHP 5.4+
* GD library

# Examples

```php
<?php

require_once 'barcode.php';

$image = barcode::image('ABCDEF123456');

imagejpeg($image, 'image.jpg');
```

![defaults](https://github.com/ThisNameWasFree/code-128/blob/master/images/image_1.jpg)
