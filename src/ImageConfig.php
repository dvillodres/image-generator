<?php

namespace DVillodres\ImageGenerator;

class ImageConfig
{
    private const FONT_PATH = __DIR__ . '/../fonts/Roboto-Medium.ttf';

    private int $width;
    private int $height;
    private string $textColor;
    private string $color;
    private ?string $imgURL;
    private string $text;
    private string $subText;
    private string $fontPath;
    private string $outputPath;
    private int $fontSize;
    private int $maxCharactersPerLine;

    private function __construct(
        int $width,
        int $height,
        string $textColor,
        string $color,
        string $text,
        string $subText,
        string $fontPath,
        string $outputPath,
        int $fontSize,
        int $maxCharactersPerLine,
        ?string $imgURL
    ) {
        $this->width = $width;
        $this->height = $height;
        $this->textColor = $textColor;
        $this->color = $color;
        $this->imgURL = $imgURL;
        $this->text = $text;
        $this->subText = $subText;
        $this->fontPath = $fontPath;
        $this->outputPath = $outputPath;
        $this->fontSize = $fontSize;
        $this->maxCharactersPerLine = $maxCharactersPerLine;
    }

    public static function square(
        string $outputPath,
        ?string $text = null,
        ?string $subText = null,
        ?string $fontPath = null,
        ?string $color = null,
        ?string $textColor = null,
        ?int $fontSize = null,
        ?int $maxCharactersPerLine = null,
        ?string $imgURL = null
    ): self {
        return new self(
            1080,
            1080,
            $textColor ?? '#FFF',
            $color ?? '#3e3e3e',
            $text,
            $subText,
            $fontPath ?? self::FONT_PATH,
            $outputPath,
            $fontSize ?? 40,
            $maxCharactersPerLine ?? 35,
            $imgURL
        );
    }

    public static function igStory(
        string $outputPath,
        ?string $text = null,
        ?string $subText = null,
        ?string $fontPath = null,
        ?string $color = null,
        ?string $textColor = null,
        ?int $fontSize = null,
        ?int $maxCharactersPerLine = null,
        ?string $imgURL = null
    ): self {
        return new self(
            1080,
            1920,
            $textColor ?? '#FFF',
            $color ?? '#3e3e3e',
            $text,
            $subText,
            $fontPath ?? self::FONT_PATH,
            $outputPath,
            $fontSize ?? 40,
            $maxCharactersPerLine ?? 35,
            $imgURL
        );
    }

    public static function postCover(
        string $outputPath,
        ?string $text = null,
        ?string $subText = null,
        ?string $fontPath = null,
        ?string $color = null,
        ?string $textColor = null,
        ?int $fontSize = null,
        ?int $maxCharactersPerLine = null,
        ?string $imgURL = null
    ): self {
        return new self(
            1920,
            1080,
            $textColor ?? '#FFF',
            $color ?? '#3e3e3e',
            $text,
            $subText,
            $fontPath ?? self::FONT_PATH,
            $outputPath,
            $fontSize ?? 65,
            $maxCharactersPerLine ?? 35,
            $imgURL
        );
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function textColor(): string
    {
        return $this->textColor;
    }

    public function color(): string
    {
        return $this->color;
    }

    public function imgURL(): ?string
    {
        return $this->imgURL;
    }

    public function hasImage(): bool
    {
        return !is_null($this->imgURL);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function lines(): int
    {
        $wordsNumber = strlen($this->text);
        return ceil($wordsNumber / $this->maxCharactersPerLine);
    }

    public function subText(): string
    {
        return $this->subText;
    }

    public function fontPath(): string
    {
        return $this->fontPath;
    }

    public function outputPath(): string
    {
        return $this->outputPath;
    }

    public function fontSize(): int
    {
        return $this->fontSize;
    }

    public function maxCharactersPerLine(): int
    {
        return $this->maxCharactersPerLine;
    }
}
