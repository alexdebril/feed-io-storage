<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

trait Translatable
{
    protected Translations $translations;

    public function setName(string $name): void
    {
        $this->translations = new Translations($name);
    }

    public function setTranslation(string $lang, string $translation): void
    {
        $this->translations->set($lang, $translation);
    }

    public function getTranslation(string $lang): string
    {
        return $this->translations->get($lang);
    }
}
