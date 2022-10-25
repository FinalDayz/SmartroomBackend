<?php

namespace App\Tests\unit;

use App\Entity\Reading;
use App\Repository\ReadingRepository;
use App\Service\DataManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DataManagerTest extends KernelTestCase
{
    public function testDataManagerNewValues()
    {
        self::bootKernel();
        $container = self::getContainer();

        // (3) run some service & test the result
        /** @var DataManager $dataManager */
        $dataManager = $container->get(DataManager::class);

        /** @var ReadingRepository $readingRepository */
        $readingRepository = $container->get(ReadingRepository::class);

        $dataManager->newValues([
            'testKey' => 55
        ]);

        $readings = $readingRepository->getLastReadingEachType();

        $this->assertCount(1, $readings);
        $this->assertEquals(55, $readings[0]->getValue());
    }

    public function testDataManagerNewValues2()
    {
        self::bootKernel();
        $container = self::getContainer();

        // (3) run some service & test the result
        /** @var DataManager $dataManager */
        $dataManager = $container->get(DataManager::class);

        /** @var ReadingRepository $readingRepository */
        $readingRepository = $container->get(ReadingRepository::class);

        $dataManager->newValues([
            'testKey' => 123
        ]);

        $dataManager->newValues([
            'testKey' => 125
        ]);

        $readings = $readingRepository->getLastReadingEachType();

        $this->assertCount(1, $readings);
        $this->assertEquals(125, $readings[0]->getValue());
    }

    public function testDataManagerThreshold()
    {
        self::bootKernel();
        $container = self::getContainer();

        // (3) run some service & test the result
        /** @var DataManager $dataManager */
        $dataManager = $container->get(DataManager::class);

        /** @var ReadingRepository $readingRepository */
        $readingRepository = $container->get(ReadingRepository::class);

        $dataManager->newValues([
            'testKey' => 123
        ]);

        $dataManager->newValues([
            'testKey' => 123.5
        ]);

        $readings = $readingRepository->getLastReadingEachType();

        $this->assertEquals(123, $readings[0]->getValue());
    }
}