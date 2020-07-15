<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;

class Topic implements Serializable, Unserializable
{
    protected ?ObjectId $id;

    protected string $slug;

    protected ?string $image = null;

    protected Translations $name;

    public function getId(): ?ObjectId
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): Topic
    {
        $this->slug = $slug;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): Topic
    {
        $this->image = $image;
        return $this;
    }

    public function getName(): Translations
    {
        return $this->name;
    }

    public function setName(Translations $name): Topic
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function bsonSerialize(): array
    {
        return [
            'slug' => $this->getSlug(),
            'image' => $this->getImage(),
            'name' => $this->name->toArray(),
        ];
    }

    /**
     * @param array<mixed> $data
     */
    public function bsonUnserialize(array $data): void
    {
        $this->id = $data['_id'];
        $this->setSlug($data['slug']);
        $name = new Translations($data['name']->default);
        foreach ($data['name']->translations as $lang => $translation) {
            $name->set($lang, $translation);
        }
        $this->setName($name);
        $this->setImage($data['image']);
    }
}
