<?php declare(strict_types=1);


namespace FeedIo\Storage\Repository;

use MongoDB\Collection;
use MongoDB\Database;

abstract class AbstractRepository
{
    protected Collection $collection;

    public function __construct(Database $database)
    {
        $this->collection = $database->selectCollection($this->getCollectionName());
    }

    public function getCollection(): Collection
    {
        return $this->collection;
    }

    public function createIndex(): void
    {
        $this->getCollection()->createIndexes(
            $this->getIndexes()
        );
    }

    abstract protected function getCollectionName(): string;

    abstract protected function getIndexes(): array;
}
