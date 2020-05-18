<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;

use FeedIo\Storage\Entity\Feed;
use FeedIo\Storage\Repository\FeedRepository;
use MongoDB\Client;
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
        $feed->setLink('http://some-feed.com');
        $feed->setLastModified(new \DateTime());

        $updateResult = $feedRepository->save($feed);
        $this->assertEquals(1, $updateResult->getUpsertedCount());
        $this->assertNotNull($updateResult->getUpsertedId());
        //$feedRepository->get($updateResult->getUpsertedId());
    }

    private function getRepository(): FeedRepository
    {
        $client = new Client('mongodb://mongo:27017');
        $database = $client->selectDatabase('feed-io-test');
        return new FeedRepository($database);
    }
}
