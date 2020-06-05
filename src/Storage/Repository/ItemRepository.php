<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\Storage\Entity\Item;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Driver\Cursor;
use MongoDB\InsertOneResult;

class ItemRepository extends AbstractRepository
{
    public function findOne(ObjectIdInterface $objectId): ? Item
    {
        $item = $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => ['root' => Item::class]]
        );

        if ($item instanceof Item) {
            return $item;
        }

        return null;
    }

    public function getItemsFromFeed(ObjectIdInterface $feedId, int $start = 0, int $limit = 10): Cursor
    {
        return $this->getCollection()->find(
            ['feedId' => $feedId],
            [
                'typeMap' => ['root' => Item::class],
                'skip' => $start,
                'limit' => $limit,
                'sort' => ['lastModified' => -1]
            ]
        );
    }

    public function save(Item $item): InsertOneResult
    {
        if (is_null($item->getPublicId())) {
            throw new \UnexpectedValueException("publicId cannot be null");
        }
        return $this->getCollection()->insertOne($item);
    }

    protected function getIndexes(): array
    {
        return [
            ['key' => ['lastModified' => -1, 'feedId' => 1]],
            ['key' => ['publicId' => 1], 'unique' => true]
        ];
    }


    protected function getCollectionName(): string
    {
        return 'items';
    }
}
