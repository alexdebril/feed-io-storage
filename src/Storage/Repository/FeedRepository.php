<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\Storage\Entity\Feed;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\UpdateResult;

class FeedRepository extends AbstractRepository
{
    public function get(ObjectIdInterface $objectId): ? Feed
    {
        $object = $this->findOne($objectId);
        if ($object instanceof Feed) {
            return $object;
        }
        return null;
    }

    public function save(Feed $feed): UpdateResult
    {
        return $this->getCollection()->updateOne(
            ['url' => $feed->getLink()],
            ['$set' => ['url' => $feed->getLink()]],
            ['upsert' => true]
        );
    }

    protected function getCollectionName(): string
    {
        return 'feeds';
    }

    protected function getObjectType(): string
    {
        return Feed::class;
    }
}
