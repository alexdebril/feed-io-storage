<?php declare(strict_types=1);


namespace FeedIo\Storage\Tests\Repository;

use FeedIo\Storage\Entity\Feed;
use FeedIo\Storage\Entity\Topic;
use FeedIo\Storage\Entity\Translations;
use FeedIo\Storage\Repository\FeedRepository;
use FeedIo\Storage\Repository\TopicRepository;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

class TopicRepositoryTest extends TestCase
{
    public function testInitRepository()
    {
        $topicRepository = $this->getRepository();

        $this->assertInstanceOf('\\MongoDB\\Collection', $topicRepository->getCollection());
        $collection = $topicRepository->getCollection();
        $this->assertEquals('topics', $collection->getCollectionName());
    }

    public function testSave()
    {
        $topicRepository = $this->getRepository();
        $topic = new Topic();
        $name = new Translations('news');
        $name->set('en', 'News')->set('fr', 'actualités');
        $topic->setSlug('slug-string')->setName($name);
        $result = $topicRepository->save($topic);
        $this->assertEquals(1, $result->getUpsertedCount());
        $newTopic = $topicRepository->findOne($result->getUpsertedId());
        $this->assertEquals('slug-string', $newTopic->getSlug());
        $this->assertInstanceOf('\\FeedIo\\Storage\\Entity\\Translations', $newTopic->getName());
        $this->assertEquals('News', $newTopic->getName()->get('en'));

        $topic->setImage('https://an-image');
        $updateResult = $topicRepository->save($topic);
        $this->assertEquals(1, $updateResult->getModifiedCount());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->getDatabase()->dropCollection('topics');
    }

    private function getRepository(): TopicRepository
    {
        return new TopicRepository($this->getDatabase());
    }

    private function getDatabase(): Database
    {
        $client = new Client('mongodb://mongo:27017');
        return $client->selectDatabase('feed-io-test');
    }
}
