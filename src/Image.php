<?php
namespace DVillodres\ImageGenerator;

class Image
{
    private const CENTER = 'center';
    private const MIDDLE = 'middle';

    private $image;
    private ?int $width;
    private ?int $height;

    public static function create(ImageConfig $config) : void
    {
        $rectangleHeight = !$config->hasImage() ? $config->height() : 220 + ($config->lines() * ($config->fontSize() - 10));
        $rectangleHeightStart = ($config->height() / 2) - ($rectangleHeight / 2);
        $rectangleHeightEnd = ($config->height() / 2) + ($rectangleHeight / 2);

        $bg = self::createCanvas($config->width(), $config->height())
            ->drawRectangle(0, 0, $config->width(), $config->height(), '1E1E1E44')
            ->drawRectangle(0, $rectangleHeightStart, $config->width(), $rectangleHeightEnd, $config->color() . '11');

        $text = self::processText($config);

        if ($config->hasImage()) {
            $img = self::fromCurl($config->imgURL())
                ->rescalePrportionAndCrop($config->width(), $config->height())
                ->addLayer($bg);
        } else {
            $img = $bg;
        }

        $img
            ->writeText($text, $config->fontPath(), $config->fontSize(), $config->textColor())
            ->writeText(
                $config->subText(),
                $config->fontPath(),
                30,
                $config->hasImage() ? $config->color() : $config->textColor(),
                $config->height() - 100
            );

        $img->toJPEG($config->outputPath(), 100);
    }

    private function width(): int
    {
        return $this->width;
    }

    private function height(): int
    {
        return $this->height;
    }

    private function image()
    {
        return $this->image;
    }

    private static function isCorrectImage(mixed $image): bool
    {
        return \is_resource($image) || ($image instanceof \GdImage);
    }

    private static function createCanvas(int $width, int $height): self
    {
        return (new self())->resetCanvas($width, $height);
    }

    private function resetCanvas(int $width, int $height): self
    {
        if (($this->image = \imagecreatetruecolor($width, $height)) === false) {
            $this->setDefaultValues();
            return $this;
        }

        \imagealphablending($this->image, false);
        \imagesavealpha($this->image, true);
        \imagefill($this->image, 0, 0, \imagecolorallocatealpha($this->image, 0, 0, 0, 127));

        $this->width = $width;
        $this->height = $height;
        return $this;
    }

    private function data(string $data): self
    {
        if (($this->image = \imagecreatefromstring($data)) === false) {
            return $this->setDefaultValues();
        }

        $this->width = \imagesx($this->image);
        $this->height = \imagesy($this->image);

        if (!\imageistruecolor($this->image)) {
            \imagepalettetotruecolor($this->image);
        }

        \imagealphablending($this->image, false);
        \imagesavealpha($this->image, true);

        return $this;
    }

    private static function fromCurl(string $url): self
    {
        $image = (new self);
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_URL, $url);
        \curl_setopt_array(
            $curl,
            [
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0',
                CURLOPT_REFERER => \strtolower('https://google.com'),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 5,
            ]
        );

        $imageCurl = \curl_exec($curl);

        \curl_close($curl);

        if ($imageCurl === false) {
            return $image->setDefaultValues();
        }

        return $image->data($imageCurl);
    }

    private function setDefaultValues(): self
    {
        [$this->image, $this->width, $this->height] = null;
        return $this;
    }

    private function centerX(int $width = 0): float
    {
        return \round($this->width() / 2 - $width / 2);
    }

    private function centerY(int $height = 0): float
    {
        return \round($this->height() / 2 - $height / 2);
    }

    private function rescaleProportion(int $maxWidth, int $maxHeight): self
    {
        if ($maxHeight > $maxWidth) {
            $newHeight = $maxHeight;
            $factor = $this->height() > $this->width() ? $maxWidth / $this->width() : $maxHeight / $this->height();
            $newWidth = $this->width() * $factor;
        } elseif ($maxWidth > $maxHeight) {
            $newWidth = $maxWidth;
            $factor = $this->height() > $this->width() ? $maxWidth / $this->width() : $maxHeight / $this->height();
            $newHeight = $this->height() * $factor;
        } else {
            if ($this->width() > $this->height()) {
                $newHeight = $maxHeight;
                $factor = $maxHeight / $this->height();
                $newWidth = $this->width() * $factor;
            } else {
                $newWidth = $maxWidth;
                $factor = $maxWidth / $this->width();
                $newHeight = $this->height() * $factor;
            }
        }

        return $this->resize($newWidth, $newHeight);
    }

    private function resize(int $width, int $height): self
    {
        if (!self::isCorrectImage($this->image)) {
            return $this;
        }

        $image = self::createCanvas($width, $height)->image();

        if (\imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width(), $this->height()) !== false) {
            $this->image = $image;
            $this->width = $width;
            $this->height = $height;
        }
        return $this;
    }

    private function rescalePrportionAndCrop(int $width, int $height): self
    {
        if ($this->rescaleProportion($width, $height)) {
            $this->crop($width, $height);
        }

        return $this;
    }


    private function crop(?int $width, ?int $height): self
    {
        if (!self::isCorrectImage($this->image)) {
            return $this;
        }

        $width = $this->width() < $width ? $this->width() : $width;
        $height = $this->height() < $height ? $this->height() : $height;
        $posX = $this->centerX($width);
        $posY = $this->centerY($height);
        $posX = $posX < 0 ? 0 : $posX;
        $posX = $posX + $width > $this->width() ? $this->width() - $width : $posX;
        $posY = $posY < 0 ? 0 : $posY;
        $posY =$posY + $height > $this->height() ? $this->height() - $height : $posY;

        if (
            ($image = \imagecreatetruecolor($width, $height)) !== false &&
            \imagealphablending($image, false) !== false &&
            \imagesavealpha($image, true) !== false &&
            ($transparent = $this->getColor('#000000FF')) !== false &&
            \imagefill($image, 0, 0, $transparent) !== false &&
            \imagecopyresampled($image, $this->image, 0, 0, $posX, $posY, $width, $height, $width, $height) !== false
        ) {
            $this->image = $image;
            $this->width = $width;
            $this->height = $height;
        }

        return $this;
    }

    private static function formatColor(string $stringColor): string
    {
        $stringColor = \trim(\str_replace('#', '', $stringColor));
        switch (\mb_strlen($stringColor)) {
            case 3 :
                $r = \substr($stringColor, 0, 1);
                $g = \substr($stringColor, 1, 1);
                $b = \substr($stringColor, 2, 1);
                return $r . $r . $g . $g . $b . $b . '00';
            case 6 :
                return $stringColor . '00';
            case 8 :
                return $stringColor;
            default:
                return '00000000';
        }
    }

    private function getColor(string $color) : mixed
    {
        $color = static::formatColor($color);
        $red = \hexdec(\substr($color, 0, 2));
        $green = \hexdec(\substr($color, 2, 2));
        $blue = \hexdec(\substr($color, 4, 2));
        $alpha = \floor(\hexdec(\substr($color, 6, 2)) / 2);

        $colorId = \imagecolorexactalpha($this->image, $red, $green, $blue, $alpha);
        if ($colorId === -1) {
            $colorId = \imagecolorallocatealpha($this->image, $red, $green, $blue, $alpha);
        }

        return $colorId;
    }

    private function addLayer(Image $image): self
    {
        if (!self::isCorrectImage($this->image) || !static::isCorrectImage($image->image())) {
            return $this;
        }

        $posX = $this->centerX($image->width);
        $posY = $this->centerY($image->height);

        \imagesavealpha($this->image, false);
        \imagealphablending($this->image, true);
        \imagecopy($this->image, $image->image(), $posX, $posY, 0, 0, $image->width, $image->height);
        \imagealphablending($this->image, false);
        \imagesavealpha($this->image, true);

        return $this;
    }

    private function writeText(string $string, string $fontPath, float $fontSize, string $color = 'ffffff', $posY = 0, $anchorX = self::CENTER, $anchorY = self::MIDDLE, float $rotation = 0, float $letterSpacing = 0): self
    {
        $this->writeTextAndGetBoundingBox($string, $fontPath, $fontSize, $color, $posY);
        return $this;
    }

    private function writeTextAndGetBoundingBox(
        string $string,
        string $fontPath,
        float $fontSize,
        string $color = 'ffffff',
        $yPosition = 0
    ): void {
        if (!self::isCorrectImage($this->image)) {
            return;
        }

        $posX = $this->centerX();
        $posY = $yPosition > 0 ? $yPosition : $this->centerY();

        \imagesavealpha($this->image, false);
        \imagealphablending($this->image, true);

        $color = $this->getColor($color);

        if ($color === false) {
            return;
        }

        if (
            ($newImg = \imagecreatetruecolor(1, 1)) === false ||
            ($posText = $this->imagettftextWithSpacing($newImg, $fontSize, 0, 0, $color, $fontPath, $string)) === false
        ) {
            return;
        }
        \imagedestroy($newImg);

        $xMin = 0;
        $xMax = 0;
        $yMin = 0;
        $yMax = 0;
        for ($i = 0; $i < 8; $i += 2) {
            if ($posText[$i] < $xMin) {
                $xMin = $posText[$i];
            }
            if ($posText[$i] > $xMax) {
                $xMax = $posText[$i];
            }
            if ($posText[$i + 1] < $yMin) {
                $yMin = $posText[$i + 1];
            }
            if ($posText[$i + 1] > $yMax) {
                $yMax = $posText[$i + 1];
            }
        }

        $sizeWidth = $xMax - $xMin;
        $sizeHeight = $yMax - $yMin;

        $posX = $posX - ($sizeWidth / 2) - $xMin;
        $posY = $posY - ($sizeHeight / 2) - $yMin;

        $posText = $this->imagettftextWithSpacing($this->image, $fontSize, $posX, $posY, $color, $fontPath, $string);

        if ($posText === false) {
            return;
        }

        \imagealphablending($this->image, false);
        \imagesavealpha($this->image, true);

    }

    private function imagettftextWithSpacing($image, float $size, float $x, float $y, int $color, string $font, string $text): bool|array
    {
        return \imagettftext($image, $size, 0, \round($x), \round($y), $color, $font, $text);
    }

    private function drawRectangle(int $left, int $top, int $right, int $bottom, string $color): self
    {
        if (!self::isCorrectImage($this->image)) {
            return $this;
        }

        $color = $this->getColor($color);

        if (($bottom - $top) <= 1.5) {
            \imageline($this->image, $left, $top, $right, $top, $color);
        } elseif (($right - $left) <= 1.5) {
            \imageline($this->image, $left, $top, $left, $bottom, $color);
        } else {
            \imagefilledrectangle($this->image, $left, $top, $right, $bottom, $color);
        }
        return $this;
    }

    private function toJPEG(string $path, int $quality = -1): void
    {
        if (!self::isCorrectImage($this->image)) {
            return;
        }
        \imagejpeg($this->image, $path, $quality);
    }

    private static function processText(ImageConfig $config) : string
    {
        $text = $config->text();
        $charLimit = $config->maxCharactersPerLine();
        $textArray = explode(' ', $text);
        $textResultArray = [];
        $currentLine = '';

        foreach ($textArray as $word) {
            $wordLength = strlen($word);

            if ($currentLine !== '' && strlen($currentLine) + $wordLength > $charLimit) {
                $textResultArray[] = rtrim($currentLine);
                $currentLine = $word . ' ';
            } else {
                $currentLine .= $word . ' ';
            }
        }

        $textResultArray[] = rtrim($currentLine);

        foreach ($textResultArray as $key => $line) {
            $lineLenght = strlen($line);
            if ($lineLenght < $charLimit) {
                $spaces = $charLimit - $lineLenght;
                if ($spaces % 2 !== 0) {
                    $spaces++;
                }
                $spacesString = str_repeat(' ', $spaces);
                $textResultArray[$key] = $spacesString . $line . $spacesString;
            }
        }

        return implode(PHP_EOL, $textResultArray);
    }
}
