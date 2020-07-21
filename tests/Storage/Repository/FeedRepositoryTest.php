<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;

use FeedIo\Storage\Entity\Feed;
use FeedIo\Storage\Entity\Item;
use FeedIo\Storage\Entity\Topic;
use FeedIo\Storage\Entity\Translations;
use FeedIo\Storage\Repository\FeedRepository;
use FeedIo\Storage\Repository\ItemRepository;
use FeedIo\Storage\Repository\TopicRepository;
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

    public function testGetItemsFromTopic()
    {
        $topicRepository = new TopicRepository($this->getDatabase());
        $itemRepository = new ItemRepository($this->getDatabase());
        $feedRepository = $this->getRepository();

        $newsTopic = new Topic();
        $newsTopic->setName(new Translations('news'));
        $newsTopic->setSlug('news');
        $newsTopicId = $topicRepository->save($newsTopic)->getUpsertedId();

        $techTopic = new Topic();
        $techTopic->setName(new Translations('tech'));
        $techTopic->setSlug('tech');
        $techTopicId = $topicRepository->save($techTopic)->getUpsertedId();

        $feeds = [
            (new Feed())->setSlug('news-en-1')->setUrl('http://news-en-1')->setLanguage('english')->setTopicId($newsTopicId),
            (new Feed())->setSlug('news-en-2')->setUrl('http://news-en-2')->setLanguage('english')->setTopicId($newsTopicId),
            (new Feed())->setSlug('news-en-3')->setUrl('http://news-en-3')->setLanguage('english')->setTopicId($newsTopicId),
            (new Feed())->setSlug('news-fr-1')->setUrl('http://news-fr-1')->setLanguage('french')->setTopicId($newsTopicId),
            (new Feed())->setSlug('news-fr-2')->setUrl('http://news-fr-2')->setLanguage('french')->setTopicId($newsTopicId),
            (new Feed())->setSlug('news-fr-3')->setUrl('http://news-fr-3')->setLanguage('french')->setTopicId($newsTopicId),

            (new Feed())->setSlug('tech-en-1')->setUrl('http://tech-en-1')->setLanguage('english')->setTopicId($techTopicId),
            (new Feed())->setSlug('tech-en-2')->setUrl('http://tech-en-2')->setLanguage('english')->setTopicId($techTopicId),
            (new Feed())->setSlug('tech-en-3')->setUrl('http://tech-en-3')->setLanguage('english')->setTopicId($techTopicId),
            (new Feed())->setSlug('tech-fr-1')->setUrl('http://tech-fr-1')->setLanguage('french')->setTopicId($techTopicId),
            (new Feed())->setSlug('tech-fr-2')->setUrl('http://tech-fr-2')->setLanguage('french')->setTopicId($techTopicId),
            (new Feed())->setSlug('tech-fr-3')->setUrl('http://tech-fr-3')->setLanguage('french')->setTopicId($techTopicId),
        ];

        $nItems = 3;
        foreach ($feeds as $feed) {
            /** @var $feed Feed */
            $id = $feedRepository->save($feed)->getUpsertedId();

            for ($i = 0; $i < $nItems; $i++) {
                $item = new Item;
                $item->setFeedId($id);
                $item->setLanguage($feed->getLanguage());
                $item->setPublicId(uniqid());
                $itemRepository->save($item);
            }
        }

        $items = $feedRepository->getItemsFromTopic($newsTopicId, 'english');
        foreach ($items as $item) {
            $this->assertEquals('english', $item['language']);
            $this->assertEquals($newsTopicId, $item['topicId']);
            $this->assertNotEmpty($item['item']['publicId']);
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
