<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class AbstractCacheManager
{
    /**
     * @var FilesystemAdapter
     */
    protected $cache;

    /**
     * @param string $key
     * @param array $notFoundValue
     * @return array|null|bool|string
     */
    protected function getData(string $key, $notFoundValue = null)
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
     * @param mixed $data
     */
    protected function setData(string $key, $data)
    {
        try {
            $dataItem = $this->cache->getItem($key);
            $dataItem->set($data);
            $this->cache->save($dataItem);
        } catch (InvalidArgumentException $ignore) {
        }
    }
}