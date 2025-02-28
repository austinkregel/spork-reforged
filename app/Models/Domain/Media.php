<?php

namespace App\Models\Domain;

class Media
{
    public function __construct(
        public string $path,
        public bool $isDirectory,
        public array $subtitles = [],
        public array $audioChannels = [],
        protected array $audioLanguages = [],
        protected array $textLanguages = [],
    ) {
        $this->audioLanguages = array_map(fn ($track) => $track['Language'] ?? $track['Language_String'] ?? dd($track, 'no Language key found'), $this->audioChannels);
        $this->textLanguages = array_map(fn ($track) => $track['Language'] ?? $track['Language_String'] ?? dd($track, 'no Language key found'), $this->subtitles);
    }
}
