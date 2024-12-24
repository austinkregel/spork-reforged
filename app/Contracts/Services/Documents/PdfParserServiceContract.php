<?php

declare(strict_types=1);

namespace App\Contracts\Services\Documents;

interface PdfParserServiceContract
{
    public function getPdfTextFromFile(string $filename): string;

    public function getPdfTextFromRawContent(string $contents): string;
}
