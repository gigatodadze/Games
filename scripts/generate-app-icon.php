<?php

declare(strict_types=1);

const SIZE = 1024;

if (! extension_loaded('gd')) {
    fwrite(STDERR, "The GD PHP extension is required.\n");
    exit(1);
}

$font = '/System/Library/Fonts/SFGeorgianRounded.ttf';

if (! is_file($font)) {
    fwrite(STDERR, "The SF Georgian Rounded font was not found.\n");
    exit(1);
}

$image = imagecreatetruecolor(SIZE, SIZE);
imageantialias($image, true);

for ($y = 0; $y < SIZE; $y++) {
    $ratio = $y / (SIZE - 1);
    $red = (int) round(42 + (115 - 42) * $ratio);
    $green = (int) round(32 + (70 - 32) * $ratio);
    $blue = (int) round(55 + (233 - 55) * $ratio);
    $color = imagecolorallocate($image, $red, $green, $blue);
    imageline($image, 0, $y, SIZE, $y, $color);
}

$coral = imagecolorallocate($image, 255, 111, 91);
$lime = imagecolorallocate($image, 206, 242, 91);
$violet = imagecolorallocate($image, 112, 69, 235);
$cream = imagecolorallocate($image, 250, 246, 238);
$line = imagecolorallocatealpha($image, 250, 246, 238, 76);
$shadow = imagecolorallocatealpha($image, 25, 18, 38, 85);

imagefilledellipse($image, 926, 94, 430, 430, $coral);
imagefilledellipse($image, 70, 974, 440, 440, $lime);
imagefilledellipse($image, 512, 520, 690, 690, $violet);

imagesetthickness($image, 12);
imageline($image, 272, 302, 746, 240, $line);
imageline($image, 746, 240, 820, 720, $line);
imageline($image, 820, 720, 232, 776, $line);

imagefilledellipse($image, 272, 302, 54, 54, $cream);
imagefilledellipse($image, 746, 240, 54, 54, $lime);
imagefilledellipse($image, 820, 720, 54, 54, $coral);
imagefilledellipse($image, 232, 776, 54, 54, $cream);

$letter = 'თ';
$fontSize = 470;
$box = imagettfbbox($fontSize, 0, $font, $letter);

if ($box === false) {
    fwrite(STDERR, "Unable to measure the icon letter.\n");
    exit(1);
}

$width = $box[2] - $box[0];
$height = $box[1] - $box[7];
$x = (int) round((SIZE - $width) / 2 - $box[0]);
$y = (int) round((SIZE - $height) / 2 - $box[7] - 8);

imagettftext($image, $fontSize, 0, $x + 12, $y + 16, $shadow, $font, $letter);
imagettftext($image, $fontSize, 0, $x, $y, $cream, $font, $letter);

$output = dirname(__DIR__).'/public/icon.png';

if (! imagepng($image, $output, 9)) {
    fwrite(STDERR, "Unable to write {$output}.\n");
    exit(1);
}

imagedestroy($image);

/**
 * Generate a full-screen NativePHP launch image for the game collection.
 */
function createSplash(string $font, string $output): void
{
    $width = 1290;
    $height = 2796;
    $splash = imagecreatetruecolor($width, $height);
    imageantialias($splash, true);

    $navy = imagecolorallocate($splash, 16, 26, 47);
    $mint = imagecolorallocate($splash, 186, 244, 209);
    $cream = imagecolorallocate($splash, 245, 240, 229);
    $pink = imagecolorallocate($splash, 245, 168, 196);
    $star = imagecolorallocatealpha($splash, 245, 240, 229, 65);

    imagefilledrectangle($splash, 0, 0, $width, $height, $navy);

    foreach ([[154, 390, 8], [1078, 540, 5], [220, 2120, 7], [1110, 2270, 6], [980, 350, 4]] as [$x, $y, $size]) {
        imagefilledellipse($splash, $x, $y, $size, $size, $star);
    }

    $cardX = 365;
    $cardY = 1030;
    $cardWidth = 560;
    $cardHeight = 560;
    $radius = 115;
    imagefilledrectangle($splash, $cardX + $radius, $cardY, $cardX + $cardWidth - $radius, $cardY + $cardHeight, $mint);
    imagefilledrectangle($splash, $cardX, $cardY + $radius, $cardX + $cardWidth, $cardY + $cardHeight - $radius, $mint);
    imagefilledellipse($splash, $cardX + $radius, $cardY + $radius, $radius * 2, $radius * 2, $mint);
    imagefilledellipse($splash, $cardX + $cardWidth - $radius, $cardY + $radius, $radius * 2, $radius * 2, $mint);
    imagefilledellipse($splash, $cardX + $radius, $cardY + $cardHeight - $radius, $radius * 2, $radius * 2, $mint);
    imagefilledellipse($splash, $cardX + $cardWidth - $radius, $cardY + $cardHeight - $radius, $radius * 2, $radius * 2, $mint);

    imagefilledellipse($splash, $cardX + 45, $cardY + 60, 74, 74, $pink);

    $letter = 'თ';
    $letterSize = 340;
    $letterBox = imagettfbbox($letterSize, 0, $font, $letter);
    $letterWidth = $letterBox[2] - $letterBox[0];
    $letterHeight = $letterBox[1] - $letterBox[7];
    $letterX = (int) round(($width - $letterWidth) / 2 - $letterBox[0]);
    $letterY = (int) round($cardY + ($cardHeight - $letterHeight) / 2 - $letterBox[7] - 15);
    imagettftext($splash, $letterSize, 0, $letterX, $letterY, $navy, $font, $letter);

    $title = 'თამაშები';
    $titleSize = 82;
    $titleBox = imagettfbbox($titleSize, 0, $font, $title);
    $titleWidth = $titleBox[2] - $titleBox[0];
    $titleX = (int) round(($width - $titleWidth) / 2 - $titleBox[0]);
    imagettftext($splash, $titleSize, 0, $titleX, $cardY + $cardHeight + 155, $cream, $font, $title);

    if (! imagepng($splash, $output, 9)) {
        fwrite(STDERR, "Unable to write {$output}.\n");
        exit(1);
    }

    imagedestroy($splash);
}

$splash = dirname(__DIR__).'/public/splash.png';
$splashDark = dirname(__DIR__).'/public/splash-dark.png';
createSplash($font, $splash);
createSplash($font, $splashDark);

fwrite(STDOUT, "Created {$output}, {$splash}, and {$splashDark}\n");
