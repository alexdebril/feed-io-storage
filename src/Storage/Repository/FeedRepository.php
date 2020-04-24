<?php declare(strict_types=1);

namespace FeedIo\Storage\Repository;

use FeedIo\FeedInterface;

class FeedRepository extends AbstractRepository
{
    const COLLECTION_NAME = 'feeds';

    public function getFeed(): FeedInterface
    {

    }

}