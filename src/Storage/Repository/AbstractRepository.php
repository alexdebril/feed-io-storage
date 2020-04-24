<?php declare(strict_types=1);


namespace FeedIo\Storage\Repository;


use \UnexpectedValueException;
use MongoDB\Collection;
use MongoDB\Database;

abstract class AbstractRepository
{
    const COLLECTION_NAME = '';

    protected Collection $collection;

    public function __construct(Database $database)
    {
        $this->collection = $database->selectCollection($this->getCollectionName());
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    protected function getCollectionName(): string
    {
        if (empty(static::COLLECTION_NAME)) {
            throw new UnexpectedValueException("");
        }
        return static::COLLECTION_NAME;
    }

}