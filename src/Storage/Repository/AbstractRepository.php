<?php declare(strict_types=1);


namespace FeedIo\Storage\Repository;

use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Collection;
use MongoDB\Database;

abstract class AbstractRepository
{
    protected Collection $collection;

    public function __construct(Database $database)
    {
        $this->collection = $database->selectCollection($this->getCollectionName());
    }

    public function findOne(ObjectIdInterface $objectId): \stdClass
    {
        return $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => $this->getObjectType()]
        );
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    abstract protected function getCollectionName(): string;

    abstract protected function getObjectType(): string;
}
