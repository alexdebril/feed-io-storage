<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;

class Topic implements Serializable, Unserializable
{
    use Translateable;

    protected ?ObjectId $id;

    protected string $slug;

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

    public function bsonSerialize(): array
    {
        return [
            'slug' => $this->getSlug(),
            'translations' => $this->translations->toArray(),
        ];
    }

    public function bsonUnserialize(array $data)
    {
        $this->setSlug($data['slug']);

    }


}
