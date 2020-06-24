<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

class Translations
{
    private array $data;

    public function set(string $lang, string $translation): void
    {
        $this->data[$lang] = $translation;
    }

    public function get(string $lang): ?string
    {
        if (! isset($this->data[$lang])) {
            return null;
        }
        return $this->data[$lang];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
