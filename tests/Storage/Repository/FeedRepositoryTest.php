<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;


use FeedIo\Storage\Repository\FeedRepository;
use MongoDB\Client;
use PHPUnit\Framework\TestCase;

class FeedRepositoryTest extends TestCase
{

    public function testInitRepository()
    {
        $client = new Client('mongodb://localhost:27017');
        $database = $client->selectDatabase('feed-io-test');
        $feedRepository = new FeedRepository($database);

        $this->assertInstanceOf('\\MongoDB\\Collection', $feedRepository->getCollection());
        $collection = $feedRepository->getCollection();
        $this->assertEquals('feeds', $collection->getCollectionName());
    }
}