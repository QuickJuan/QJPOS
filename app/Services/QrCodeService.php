<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeService
{
    public static function pngDataUri(string $text, int $size = 180, int $margin = 5): string
    {
        $qrCode = new QrCode($text);
        $qrCode->setSize($size);
        $qrCode->setMargin($margin);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
}

