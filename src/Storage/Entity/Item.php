<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

use FeedIo\Feed\Item as BaseItem;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\BSON\UTCDateTime;

class Item extends BaseItem implements Serializable, Unserializable
{
    protected ?ObjectId $id;

    protected ObjectId $feedId;

    public function getId(): ? ObjectId
    {
        return $this->id;
    }

    public function getFeedId(): ObjectId
    {
        return $this->feedId;
    }

    public function setFeedId(ObjectId $feedId): Item
    {
        $this->feedId = $feedId;

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function bsonSerialize(): array
    {
        $properties = get_object_vars($this);

        foreach ($properties as $name => $property) {
            if ($property instanceof \DateTime) {
                $properties[$name] = new UTCDateTime($property);
            }
        }

        return $properties;
    }

    /**
     * @param array<mixed> $data
     */
    public function bsonUnserialize(array $data): void
    {
        $this->id = $data['_id'];
        $this->setFeedId($data['feedId']);
        if ($data['lastModified'] instanceof UTCDateTime) {
            $this->setLastModified($data['lastModified']->toDateTime());
        }
        $this->setTitle($data['title']);
        $this->setLink($data['link']);
        $this->setDescription($data['description']);
        $this->setPublicId($data['publicId']);

        if (is_array($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $this->addCategory($category);
            }
        }
    }
}