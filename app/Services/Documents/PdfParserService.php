<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Contracts\Services\Documents\PdfParserServiceContract;
use Smalot\PdfParser\Document;

class PdfParserService implements PdfParserServiceContract
{
    public function __construct(protected PdfReaderService $parser)
    {
    }

    public function getPdfTextFromFile(string $filename): string
    {
        /** @var Document $pdf */
        $pdf = $this->parser->parseFile($filename);

        $menuData = [];

        foreach ($pdf->getPages() as $page) {
            $dataChunks = $page->getDataTm();

            foreach ($dataChunks as $index => $chunk) {
                if ($index < 18) {
                    continue;
                }
                if (! isset($item)) {
                    $item = [];
                }

                $text = trim(match (count($chunk)) {
                    2 => $chunk[1],
                });

                if (empty($text)) {
                    continue;
                }

                $item[] = $text;

                preg_match('/\d{1,4}\.\d{2}/', $text, $matches);

                if (empty($matches)) {
                    continue;
                }

                $menuData[$item[0]] = $item;
                unset($item);
            }
        }

        return json_encode($menuData);
    }

    public function getPdfTextFromRawContent(string $contents): string
    {
        $pdf = $this->parser->parseContent($contents);

        return $this->readPdfText($pdf);
    }

    protected function readPdfText(Document $pdf): string
    {
        return $pdf->getText();
    }
}
