<?php declare(strict_types=1);


namespace FeedIo\Storage\Entity;

use FeedIo\Feed as BaseFeed;
use FeedIo\Feed\ItemInterface;
use FeedIo\Reader\Result;
use FeedIo\Storage\Entity\Feed\Status;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Serializable;
use MongoDB\BSON\Unserializable;
use MongoDB\BSON\UTCDateTime;

class Feed extends BaseFeed implements Serializable, Unserializable
{
    protected ?ObjectId $id;

    protected Status $status;

    protected \DateTime $nextUpdate;

    protected ?ObjectId $topicId = null;

    protected ?string $slug;

    /**
     * @var array<mixed>
     */
    protected array $checks = [];

    public function __construct()
    {
        $this->nextUpdate = new \DateTime();
        $this->setStatus(new Status(Status::PENDING));

        parent::__construct();
    }

    public function getId(): ? ObjectId
    {
        return $this->id;
    }

    public function newItem(): ItemInterface
    {
        return new Item();
    }

    public function setNextUpdate(\DateTime $nextUpdate): Feed
    {
        $this->nextUpdate = $nextUpdate;

        return $this;
    }

    public function setResult(Result $result): Feed
    {
        $this->setNextUpdate($result->getNextUpdate());

        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    /**
     * @param array<mixed> $checks
     * @return $this
     */
    public function setChecks(array $checks): Feed
    {
        $this->checks = $checks;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function getTopicId(): ?ObjectId
    {
        return $this->topicId;
    }

    public function setTopicId(ObjectId $topicId): Feed
    {
        $this->topicId = $topicId;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug = null): Feed
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return array<mixed>
     */
    public function bsonSerialize(): array
    {
        $properties = get_object_vars($this);
        unset($properties['items']);

        foreach ($properties as $name => $property) {
            if ($property instanceof \DateTime) {
                $properties[$name] = new UTCDateTime($property);
            }
        }

        $properties['status'] = $this->getStatus()->getValue();

        return $properties;
    }

    /**
     * @param array<mixed> $data
     */
    public function bsonUnserialize(array $data): void
    {
        $this->id = $data['_id'];
        if (isset($data['topicId']) && $data['topicId'] instanceof ObjectId) {
            $this->setTopicId($data['topicId']);
        }
        if ($data['lastModified'] instanceof UTCDateTime) {
            $this->setLastModified($data['lastModified']->toDateTime());
        }
        if ($data['nextUpdate'] instanceof UTCDateTime) {
            $this->setNextUpdate($data['nextUpdate']->toDateTime());
        }
        $this->setTitle($data['title']);
        $this->setLink($data['link']);
        $this->setUrl($data['url']);
        $this->setSlug($data['slug']);
        $this->setChecks((array) $data['checks']);
        $this->setDescription($data['description']);
        $this->setPublicId($data['publicId']);
        $this->setLanguage($data['language']);
        $this->setStatus(new Status($data['status']));

        if (is_array($data['categories'])) {
            foreach ($data['categories'] as $category) {
                $this->addCategory($category);
            }
        }
    }
}
