<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\Storage\Entity\Feed;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\BSON\UTCDateTime;
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

    /**
     * @param array<string> $statuses
     * @return Cursor<Feed>
     */
    public function getFeedsToUpdate(array $statuses = [Feed\Status::ACCEPTED, Feed\Status::APPROVED]): Cursor
    {
        return $this->getCollection()->find(
            [
                'nextUpdate' => ['$lte' => new UTCDateTime()],
                'status' => ['$in' => $statuses],
            ],
            ['typeMap' => ['root' => Feed::class]]
        );
    }

    /**
     * @param string $status
     * @return Cursor<Feed>
     */
    public function getFeedsByStatus(string $status): Cursor
    {
        return $this->getCollection()->find(
            ['status' => $status],
            ['typeMap' => ['root' => Feed::class]]
        );
    }

    /**
     * @param ObjectIdInterface $topicId
     * @param string $language
     * @param int $start
     * @param int $limit
     * @return \Traversable<array>
     */
    public function getItemsFromTopic(ObjectIdInterface $topicId, string $language, int $start = 0, int $limit = 10): \Traversable
    {
        return $this->getCollection()->aggregate([
            ['$match' => [
                'language' => $language,
                'topicId' => $topicId,
            ]],
            ['$lookup' => [
                'as' => 'item',
                'from' => 'items',
                'localField' => '_id',
                'foreignField' => 'feedId',
            ]],
            ['$unwind' => '$item'],
            ['$project' => [
                '_id' => 1,
                'language' => 1,
                'title' => 1,
                'url' => 1,
                'slug' => 1,
                'topicId' => 1,
                'item._id' => 1,
                'item.description' => 1,
                'item.title' => 1,
                'item.url' => 1,
                'item.publicId' => 1,
                'item.thumbnail' => 1,
                'item.lastModified' => 1,
            ]],
            ['$sort' => ['item.lastModified' => -1]],
            ['$skip' => $start],
            ['$limit' => $limit],
        ]);
    }

    public function save(Feed $feed): UpdateResult
    {
        if (is_null($feed->getUrl())) {
            throw new \UnexpectedValueException("feed URL cannot be null");
        }
        return $this->getCollection()->updateOne(
            ['url' => $feed->getUrl()],
            ['$set' => $feed],
            ['upsert' => true]
        );
    }

    protected function getIndexes(): array
    {
        return [
            ['key' => ['nextUpdate' => 1, 'status' => 1]],
            ['key' => ['language' => 1, 'topicId' => 1]],
            ['key' => ['url' => 1], 'unique' => true]
        ];
    }

    protected function getCollectionName(): string
    {
        return 'feeds';
    }
}
