<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class RealTimeData
{
    private const CACHE_KEY_SENSOR_DATA = 'realtime.sensor';
    private const CACHE_KEY_ACTION_ARRAY = 'realtime.action';

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    public function __construct(LoggerInterface $logger)
    {
        $this->cache = new FilesystemAdapter();
    }

    /**
     * @param array $notFoundValue
     * @return array
     */
    public function getSensorData($notFoundValue = []): array {
        return $this->getData(self::CACHE_KEY_SENSOR_DATA, $notFoundValue);
    }

    /**
     * @param array $data
     */
    public function setSensorData(array $data) {
        $this->setData(self::CACHE_KEY_SENSOR_DATA, $data);
    }

    /**
     * @param array $notFoundValue
     * @return array
     */
    public function getActionData($notFoundValue = []): array {
        return $this->getData(self::CACHE_KEY_ACTION_ARRAY, $notFoundValue);
    }

    /**
     * @param array $data
     */
    public function setActionData(array $data) {
        $this->setData(self::CACHE_KEY_ACTION_ARRAY, $data);
    }


    /**
     * @param string $key
     * @param array $notFoundValue
     * @return array
     */
    private function getData(string $key, $notFoundValue) {
        try {
            if ($this->cache->hasItem($key)) {
                $this->logger->info("HAS ITEN");
                return $this->cache->getItem($key)->get();
            }
        } catch (InvalidArgumentException $ignore) {}

        return $notFoundValue;
    }

    /**
     * @param string $key
     * @param $data
     */
    private function setData(string $key, $data) {
        try {
            $dataItem = $this->cache->getItem($key);
            $dataItem->set($data);

            $this->cache->save($dataItem);
        } catch (InvalidArgumentException $ignore) {}
    }
}