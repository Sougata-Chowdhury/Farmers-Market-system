<?php

namespace App\Support;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeImage
{
    /**
     * Render a PNG QR code as a data URI, ready to embed in a PDF.
     */
    public static function dataUri(string $data, int $scale = 5): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'eccLevel' => EccLevel::M,
            'scale' => $scale,
            'margin' => 2,
        ]);

        return (new QRCode($options))->render($data);
    }
}
