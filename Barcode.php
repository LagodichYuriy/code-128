<?php

class Barcode
{
	/**
	 * --------------------------------------------------------------------------------------
	 * Code 128 charset structure:
	 * --------------------------------------------------------------------------------------
	 * 108 symbols:
	 * - 103 data symbols;
	 * - 3 start symbols;
	 * - 2 stop symbols.
	 *
	 * Each symbol consists of three black bars and three white spaces.
	 * Each bar and space is 1 to 4 modules wide, and are fixed width.
	 * The sum of the widths of three black bars and three white bars
	 * is 11 modules.
	 *
	 * More info: https://en.wikipedia.org/wiki/Code_128#Bar_code_widths
	 */
	const CODE_128B =
	[
		212222, 222122, 222221, 121223, 121322, 131222, 122213, 122312, 132212, 221213, 221312, 231212, 112232, 122132, 122231, 113222, 123122, 123221, 223211, 221132, 221231,
		213212, 223112, 312131, 311222, 321122, 321221, 312212, 322112, 322211, 212123, 212321, 232121, 111323, 131123, 131321, 112313, 132113, 132311, 211313, 231113, 231311,
		112133, 112331, 132131, 113123, 113321, 133121, 313121, 211331, 231131, 213113, 213311, 213131, 311123, 311321, 331121, 312113, 312311, 332111, 314111, 221411, 431111,
		111224, 111422, 121124, 121421, 141122, 141221, 112214, 112412, 122114, 122411, 142112, 142211, 241211, 221114, 413111, 241112, 134111, 111242, 121142, 121241, 114212,
		124112, 124211, 411212, 421112, 421211, 212141, 214121, 412121, 111143, 111341, 131141, 114113, 114311, 411113, 411311, 113141, 114131, 311141, 411131, 211412, 211214,
		211232, 23311120
	];

	/**
	 * --------------------------------------------------------------------------------------
	 * Barcode structure:
	 * --------------------------------------------------------------------------------------
	 * BARCODE = [START B] . [ENCODED DATA] . [CHECKSUM] . [STOP]
	 *
	 * [START B]      = $codes[104]            (constant)
	 * [ENCODED DATA] = $codes[ASCII - 32]     (for each character in the string)
	 * [CHECKSUM]     = $codes[CHECKSUM % 103] (the CHECKSUM number structure is described below)
	 * [STOP]         = $codes[106]            (constant)
	 *
	 * --------------------------------------------------------------------------------------
	 * CHECKSUM number:
	 * --------------------------------------------------------------------------------------
	 * CHECKSUM = 104 + (1 * (ord($string[0]) - 32)) +
	 *                  (2 * (ord($string[1]) - 32)) +
	 *                  (3 * (ord($string[2]) - 32)) + ... + (n * (ord($string[n - 1]) - 32)
	 *
	 * `ord($string[2]) - 32` means the ASCII char can be transformed to 128b by subtructing
	 * 32 from the char's ASCII index.
	 *
	 * More info: https://en.wikipedia.org/wiki/Code_128
	 *
	 * @param string $string           ASCII string for encode
	 * @param int    $height           height in pixels
	 * @param int    $width image width autoscale modifier
	 *
	 * @return resource
	 */
	public static function make(string $string, int $height = 40, int $width = 1)
	{
		$string_length = strlen($string);


		$image_width = (11 + $string_length * 11 + 11 + 13) * $width; # [START B] + [ENCODED DATA] + [CHECKSUM] + [STOP]


		$image = imagecreatetruecolor($image_width, $height);

		$color_white = imagecolorallocate($image, 255, 255, 255);
		$color_black = imagecolorallocate($image,   0,   0,   0);

		imagefill($image, 0, 0, $color_white); # white background


		$barcode[] = self::CODE_128B[104]; # START CODE B


		$checksum = 104;

		for ($i = 0; $i < $string_length; $i++)
		{
			$char = ord($string[$i]) - 32; # position in array is ASCII - 32

			$checksum += $char * ($i + 1);

			$barcode[] = self::CODE_128B[$char];
		}

		$barcode[] = self::CODE_128B[$checksum % 103]; # checksum
		$barcode[] = self::CODE_128B[106];             # STOP

		$barcode = implode($barcode);
		$barcode = str_split($barcode);

		array_pop($barcode); # remove redundant zero from the end of array


		$barcode = array_map(fn ($value) => $value * $width, $barcode);


		$offset = 0;

		foreach ($barcode as $index => $image_width)
		{
			if (~$index & 1) # if number is odd
			{
				for ($i = 0; $i < $image_width; $i++)
				{
					imageline($image, $offset + $i, 0, $offset + $i, $height, $color_black);
				}
			}

			$offset += $image_width;
		}

		return $image;
	}
}
