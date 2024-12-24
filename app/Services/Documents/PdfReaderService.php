<?php

declare(strict_types=1);

namespace App\Services\Documents;

use Smalot\PdfParser\Document;
use Smalot\PdfParser\Parser;

class PdfReaderService extends Parser
{
    public function parseContent(string $content): Document
    {
        [$xref, $data] = $this->rawDataParser->parseData($content);
        if (isset($xref['trailer']['encrypt'])) {
            $this->stripEncryption($xref);
        }

        if (empty($data)) {
            throw new \Exception('Object list not found. Possible secured file.');
        }

        // Create destination object.
        $document = new Document();
        $this->objects = [];

        foreach ($data as $id => $structure) {
            $this->parseObject($id, $structure, $document);
            unset($data[$id]);
        }

        $document->setTrailer($this->parseTrailer($xref['trailer'], $document));
        $document->setObjects($this->objects);

        return $document;
    }

    protected function stripEncryption(array &$xref): void
    {
        if (is_string($xref['trailer']['size'])) {
            $xref['trailer']['encrypt'] = null;
        }
    }
}
