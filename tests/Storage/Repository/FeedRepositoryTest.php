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
        $feed->setLink('http://some-feed.com/feed.atom');
        $feed->setLastModified(new \DateTime());

        $updateResult = $feedRepository->save($feed);
        $this->assertEquals(1, $updateResult->getUpsertedCount());
        $this->assertNotNull($updateResult->getUpsertedId());
        $feedFromDb = $feedRepository->findOne($updateResult->getUpsertedId());
        $this->assertEquals('http://some-feed.com/feed.atom', $feedFromDb->getLink());
        $this->assertEquals('//some-feed.com', $feedFromDb->getHost());

    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getDatabase()->dropCollection('feeds');
    }

    private function getRepository(): FeedRepository
    {
        return new FeedRepository($this->getDatabase());
    }

    private function getDatabase(): Database
    {
        $client = new Client('mongodb://mongo:27017');
        return $client->selectDatabase('feed-io-test');
    }
}
