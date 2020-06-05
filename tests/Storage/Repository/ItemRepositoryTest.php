<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;

use FeedIo\Storage\Entity\Item;
use FeedIo\Storage\Repository\ItemRepository;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

class ItemRepositoryTest extends TestCase
{
    public function testInitRepository()
    {
        $feedRepository = $this->getRepository();

        $this->assertInstanceOf('\\MongoDB\\Collection', $feedRepository->getCollection());
        $collection = $feedRepository->getCollection();
        $this->assertEquals('items', $collection->getCollectionName());
    }

    public function testSave()
    {
        $itemRepository = $this->getRepository();
        $item = new Item();
        $item->setPublicId('http://some-feed.com/feed.atom');
        $item->setLastModified(new \DateTime());
        $item->setFeedId(new ObjectId());

        $insertResult = $itemRepository->save($item);
        $this->assertEquals(1, $insertResult->getInsertedCount());
        $this->assertNotNull($insertResult->getInsertedId());
        $itemFromDb = $itemRepository->findOne($insertResult->getInsertedId());
        $this->assertEquals('http://some-feed.com/feed.atom', $itemFromDb->getPublicId());
    }

    public function testGetItems()
    {
        $itemRepository = $this->getRepository();
        $feedId = new ObjectId();

        $item = new Item();
        $item->setPublicId('1');
        $item->setLastModified(new \DateTime('-7 days'));
        $item->setFeedId($feedId);

        $itemRepository->save($item);

        $item = new Item();
        $item->setPublicId('3');
        $item->setLastModified(new \DateTime('-10 days'));
        $item->setFeedId($feedId);

        $itemRepository->save($item);

        $item = new Item();
        $item->setPublicId('2');
        $item->setLastModified(new \DateTime('-8 days'));
        $item->setFeedId($feedId);

        $itemRepository->save($item);


        $item = new Item();
        $item->setPublicId('excluded');
        $item->setLastModified(new \DateTime('-8 days'));
        $item->setFeedId(new ObjectId());

        $itemRepository->save($item);

        $items = $itemRepository->getItemsFromFeed($feedId)->toArray();
        $this->assertCount(3, $items);
        $ids = [];
        /** @var Item $item */
        foreach ($items as $item) {
            $this->assertNotEquals('excluded', $item->getPublicId());
            $ids[] = $item->getPublicId();
        }
        $this->assertEquals(['1', '2', '3'], $ids);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getDatabase()->dropCollection('items');
    }

    private function getRepository(): ItemRepository
    {
        $repository = new ItemRepository($this->getDatabase());
        $repository->createIndex();

        return $repository;
    }

    private function getDatabase(): Database
    {
        $client = new Client('mongodb://mongo:27017');
        return $client->selectDatabase('feed-io-test');
    }
}
