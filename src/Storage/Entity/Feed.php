<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

use FeedIo\Feed as BaseFeed;
use FeedIo\Reader\Result;
use FeedIo\Storage\Entity\Feed\Status;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\BSON\UTCDateTime;

class Feed extends BaseFeed implements Serializable, Unserializable
{
    protected ObjectId $id;

    protected Status $status;

    protected \DateTime $nextUpdate;

    public function __construct()
    {
        $this->id = new ObjectId();

        parent::__construct();
    }

    public function getId(): ObjectId
    {
        return $this->id;
    }

    public function setId(ObjectId $id): void
    {
        $this->id = $id;
    }

    public function setResult(Result $result): void
    {
        $this->nextUpdate = $result->getNextUpdate();
    }

    /**
     * @return array<mixed>
     */
    public function bsonSerialize(): array
    {
        $properties = get_object_vars($this);
        unset($properties['items']);
        unset($properties['elements']);

        foreach ($properties as $name => $property) {
            if ($property instanceof \DateTime) {
                $properties[$name] = new UTCDateTime($property);
            }
        }

        $properties['_id'] = $this->getId();

        return $properties;
    }

    /**
     * @param array<mixed> $data
     */
    public function bsonUnserialize(array $data): void
    {
        $this->setId($data['_id']);
        if ($data['lastModified'] instanceof UTCDateTime) {
            $this->setLastModified($data['lastModified']->toDateTime());
        }
        $this->setTitle($data['title']);
        $this->setLink($data['link']);
        $this->setUrl($data['url']);
        $this->setDescription($data['description']);
        $this->setPublicId($data['publicId']);
        $this->setLanguage($data['language']);

        if (is_array($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $this->addCategory($category);
            }
        }
    }
}
