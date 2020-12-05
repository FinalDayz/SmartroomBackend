<?php

namespace App\Service;

use App\Entity\Reading;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\CacheItem;

class RealTimeData
{
    private const CACHE_KEY_READINGS = 'realtime.readings';

    /**
     * @var FilesystemAdapter
     */
    private $cache;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ReadingHelper
     */
    private $readingHelper;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $entityManager,
        ReadingHelper $readingHelper
    )
    {
        $this->entityManager = $entityManager;
        $this->cache = new FilesystemAdapter();
        $this->readingHelper = $readingHelper;
    }

    /**
     * @param array $notFoundValue
     * @return array
     * @throws InvalidArgumentException
     */
    public function getReadingData($notFoundValue = []): array
    {
        $this->cache->delete(self::CACHE_KEY_READINGS);
        return $this->cache->get(self::CACHE_KEY_READINGS, function (CacheItem $item) {
            $item->expiresAfter(3600);
            return
                $this->readingHelper->rawFromReadings(
                    $this->entityManager->getRepository(Reading::class)
                        ->getLastReadingEachType()
                );
        });
    }

    /**
     * @param array $data
     */
    public function setReadingData(array $data)
    {
        $this->setData(self::CACHE_KEY_READINGS, $data);
    }

    /**
     * @param string $key
     * @param array $notFoundValue
     * @return array
     */
    private function getData(string $key, $notFoundValue = null)
    {
        try {
            if ($this->cache->hasItem($key)) {
                return $this->cache->getItem($key)->get();
            }
        } catch (InvalidArgumentException $ignore) {
        }

        return $notFoundValue;
    }

    /**
     * @param string $key
     * @param $data
     */
    private function setData(string $key, $data)
    {
        try {
            $dataItem = $this->cache->getItem($key);
            $dataItem->set($data);

            $this->cache->save($dataItem);
        } catch (InvalidArgumentException $ignore) {
        }
    }
}