<?php

declare(strict_types=1);

namespace App\Services\Documents;

use App\Contracts\Services\Documents\PdfParserServiceContract;
use mikehaertl\pdftk\Pdf;

class MenuCardParser
{
    protected PdfParserServiceContract $parserService;

    public function __construct(PdfParserServiceContract $parserService)
    {
        $this->parserService = $parserService;
    }

    public function getAllIdentifiers(string $filename): array
    {
        $pdfContents = $this->parseAndHandleEncryptedPdf($filename);

        dd($pdfContents);
        $replacements = [
            '  ' => ' ',
            "\n" => ' ',
            "\t" => ' ',
            ' - ' => '-',
            '- ' => '-',
            ' -' => '-',
        ];

        foreach ($replacements as $search => $replace) {
            $pdfContents = str_replace($search, $replace, $pdfContents);
        }

        $lines = array_values(array_filter(explode("\n", $pdfContents)));
        $matches = collect([]);

        foreach ($lines as $line) {
            if (preg_match_all('/(1[\s]?A[\s]?4[\s]?.+?[^A-Z\d])|([A-Z-]{1,3}-[A-Z-0-9]{1,3}.+?[^A-Z\d])/m', $line, $allMatches)) {
                $matches = collect($allMatches)
                    ->flatten()
                    ->map(fn ($str) => preg_replace('/[^[:print:]]/', '', trim(str_replace(' ', '', $str), " \t\n\r\0\x0B:)(")))
                    ->filter()
                    // The MRA email gets caught in this regex, so let's filter out anything starting with MRA
                    ->filter(fn ($str) => ! str_starts_with($str, 'MRA') && ! str_starts_with($str, 'AGE') && ! str_starts_with($str, 'LLC'))
                    ->merge($matches)
                    ->unique();
            }
        }

        return $matches->toArray();
    }

    public function getRecallsFromPdfFile(string $filename): array
    {
        $pdfContents = $this->parseAndHandleEncryptedPdf($filename);
        $lines = array_values(array_filter(explode("\n", $pdfContents)));

        return $this->parsePdf($lines);
    }

    public function getPackageIdsFromPdfFile(string $filename): array
    {
        $pdfContents = $this->parseAndHandleEncryptedPdf($filename);
        $lines = array_values(array_filter(explode("\n", $pdfContents)));

        return $this->parsePackagesFromPdf($lines);
    }

    protected function parseAndHandleEncryptedPdf(string $filename, int $recursionCounter = 0): string
    {
        try {
            $pdfText = $this->parserService->getPdfTextFromFile($filename);

            if (empty($pdfText)) {
                throw new \Exception('Secured pdf file are currently not supported.');
            }

            return $pdfText;
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Secured pdf file are currently not supported.' && $recursionCounter < 3) {
                // We can actually use pdftk to decrypt the file and then re-run the extraction.
                $pdf = new Pdf();
                $pdf->addFile($filename, null, '')->saveAs($filename);

                return $this->parserService->getPdfTextFromFile($filename);
            }
            throw $e;
        }
    }

    protected function parsePackagesFromPdf(array $lines): array
    {
        $packages = [];
        foreach ($lines as $line) {
            if ($package = $this->isPackageBlock($line)) {
                $packages[] = $package;
            }
        }

        return $packages;
    }

    protected function parsePdf(array $lines): array
    {
        $recalls = [];

        $currentStore = null;
        $currentPackage = null;

        foreach ($lines as $line) {
            $isStoreBlock = $this->isStoreBlock($line);
            $isPackageBlock = $this->isPackageBlock($line);

            if (! empty($isStoreBlock)) {
                // New store has been discovered, set package info and reset
                $currentStore = $isStoreBlock;
                $currentPackage = null;

                continue;
            }

            if (! empty($isPackageBlock)) {
                // New package discovered
                $currentPackage = $isPackageBlock;

                continue;
            }

            if (empty($currentPackage)) {
                continue;
            }

            if (empty($recalls[$currentStore])) {
                $recalls[$currentStore] = [];
            }

            if (empty($recalls[$currentStore][$currentPackage])) {
                $recalls[$currentStore][$currentPackage] = '';
            }

            $recalls[$currentStore][$currentPackage] .= ' '.str_replace(array_keys($recalls), '', $this->trim($line));
        }

        /**
         * Depending on how a package recall block is structured there may be another store name trailing at the end
         * of the recall info. Here we will just filter out store names from the recall info based on the store names
         * that are in our recalls array.
         */
        $stores = array_keys($recalls);
        foreach ($recalls as $store => $packages) {
            foreach ($packages as $package => $recallInfo) {
                $recalls[$store][$package] = $this->trim(str_replace($stores, '', $recallInfo));
            }
        }

        return $recalls;
    }

    protected function isStoreBlock(string $line): ?string
    {
        $storeNameRegex = '/This recall affects.*from ([a-zA-Z0-9\-_ &]*).*/i';
        preg_match_all($storeNameRegex, $line, $matches);
        if (! empty($matches[1])) {
            return $this->trim($matches[1][0]);
        }

        return null;
    }

    protected function isPackageBlock(string $line): ?string
    {
        $packageRegex = '/[Package]? ?#? ?(1A[A-Z0-9]*)/i';
        preg_match_all($packageRegex, $line, $matches);
        if (! empty($matches[1])) {
            return $this->trim($matches[1][0]);
        }

        return null;
    }

    protected function trim(string $value): string
    {
        return trim($value, "\t\r\n ");
    }
}
