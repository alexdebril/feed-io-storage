<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

class Translations
{
    protected string $default;

    /**
     * @var array<string>
     */
    protected array $data;

    /**
     * Translations constructor.
     * @param string $default
     * @param array<string> $data
     */
    public function __construct(string $default, array $data = [])
    {
        $this->default = $default;
        $this->data = $data;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function set(string $lang, string $translation): Translations
    {
        $this->data[$lang] = $translation;
        return $this;
    }

    public function get(string $lang): ?string
    {
        if (! isset($this->data[$lang])) {
            return null;
        }
        return $this->data[$lang];
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'default' => $this->default,
            'translations' => $this->data,
        ];
    }
}
