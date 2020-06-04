<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\Storage\Entity\Feed;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Driver\Cursor;
use MongoDB\UpdateResult;

class FeedRepository extends AbstractRepository
{
    public function findOne(ObjectIdInterface $objectId): ? Feed
    {
        $feed = $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => ['root' => Feed::class]]
        );

        if ($feed instanceof Feed) {
            return $feed;
        }

        return null;
    }

    public function getFeedsToUpdate(): Cursor
    {
        $filter = [
            'nextUpdate' => ['$gte' => new \DateTime()],
            'status' => ['$ne' => Feed\Status::REJECTED],
        ];

        return $this->getCollection()->find($filter);
    }

    public function save(Feed $feed): UpdateResult
    {
        return $this->getCollection()->updateOne(
            ['url' => $feed->getLink()],
            ['$set' => $feed],
            ['upsert' => true]
        );
    }

    protected function getCollectionName(): string
    {
        return 'feeds';
    }
}
