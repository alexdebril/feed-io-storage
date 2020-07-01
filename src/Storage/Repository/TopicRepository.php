<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\Storage\Entity\Topic;
use MongoDB\BSON\ObjectIdInterface;
use MongoDB\Driver\Cursor;
use MongoDB\UpdateResult;

class TopicRepository extends AbstractRepository
{
    public function findOne(ObjectIdInterface $objectId): ? Topic
    {
        $topic = $this->getCollection()->findOne(
            ['_id' => $objectId],
            ['typeMap' => ['root' => Topic::class]]
        );

        if ($topic instanceof Topic) {
            return $topic;
        }

        return null;
    }

    /**
     * @return Cursor<Topic>
     */
    public function getTopics(): Cursor
    {
        return $this->getCollection()->find([],
            ['typeMap' => ['root' => Topic::class]]
        );
    }

    public function save(Topic $topic): UpdateResult
    {
        return $this->getCollection()->updateOne(
            ['name.default' => $topic->getName()],
            ['$set' => $topic],
            ['upsert' => true]
        );
    }

    protected function getIndexes(): array
    {
        return [

        ];
    }

    protected function getCollectionName(): string
    {
        return 'topics';
    }
}
