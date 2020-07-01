<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;

use FeedIo\Storage\Entity\Feed;
use FeedIo\Storage\Repository\FeedRepository;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

class FeedRepositoryTest extends TestCase
{
    public function testInitRepository()
    {
        $feedRepository = $this->getRepository();

        $this->assertInstanceOf('\\MongoDB\\Collection', $feedRepository->getCollection());
        $collection = $feedRepository->getCollection();
        $this->assertEquals('feeds', $collection->getCollectionName());
    }

    public function testSave()
    {
        $feedRepository = $this->getRepository();
        $feed = new Feed();
        $feed->setUrl('http://some-feed.com/feed.atom');
        $feed->setLink('http://some-feed.com');
        $feed->setLastModified(new \DateTime());
        $feed->setChecks(['uniqId' => true, 'normal_flow' => false]);
        $updateResult = $feedRepository->save($feed);
        $this->assertEquals(1, $updateResult->getUpsertedCount());
        $this->assertNotNull($updateResult->getUpsertedId());
        $feedFromDb = $feedRepository->findOne($updateResult->getUpsertedId());
        $this->assertEquals('http://some-feed.com/feed.atom', $feedFromDb->getUrl());
        $this->assertEquals('//some-feed.com', $feedFromDb->getHost());
        $this->assertEquals(['uniqId' => true, 'normal_flow' => false], $feed->getChecks());
    }

    public function testGetNexUpdate()
    {
        $feedRepository = $this->getRepository();
        $toUpdate = new Feed();
        $toUpdate->setUrl('http://to-update.com/feed.atom');
        $toUpdate->setStatus(new Feed\Status(Feed\Status::ACCEPTED));
        $toUpdate->setLastModified(new \DateTime());
        $toUpdate->setNextUpdate(new \DateTime('-1hour'));

        $feedRepository->save($toUpdate);

        $rejected = new Feed();
        $rejected->setUrl('http://rejected.com/feed.atom');
        $rejected->setStatus(new Feed\Status(Feed\Status::REJECTED));
        $rejected->setLastModified(new \DateTime());
        $rejected->setNextUpdate(new \DateTime('-1hour'));

        $feedRepository->save($rejected);

        $notYet = new Feed();
        $notYet->setUrl('http://not-yet.com/feed.atom');
        $notYet->setStatus(new Feed\Status(Feed\Status::ACCEPTED));
        $notYet->setLastModified(new \DateTime());
        $notYet->setNextUpdate(new \DateTime('+1hour'));

        $feedRepository->save($notYet);

        $cursor = $feedRepository->getFeedsToUpdate();
        $result = $cursor->toArray();
        $this->assertCount(1, $result);
        /** @var Feed $feed */
        foreach ($result as $feed) {
            $this->assertInstanceOf(Feed::class, $feed);
            $this->assertEquals('http://to-update.com/feed.atom', $feed->getUrl());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getDatabase()->dropCollection('feeds');
    }

    private function getRepository(): FeedRepository
    {
        $repository = new FeedRepository($this->getDatabase());
        $repository->createIndex();

        return $repository;
    }

    private function getDatabase(): Database
    {
        $client = new Client('mongodb://mongo:27017');
        return $client->selectDatabase('feed-io-test');
    }
}
