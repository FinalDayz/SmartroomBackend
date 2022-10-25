<?php


namespace App\Service;


use App\Entity\Reading;
use App\Repository\ReadingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class DataManager extends AbstractCacheManager
{
    private const CACHE_LAST_INSERT = 'cache.last_insert_values';

    private $insertedInDB = false;

    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ReadingHelper constructor.
     * @param ContainerBagInterface $params
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @throws InvalidArgumentException
     */
    public function __construct(
        ContainerBagInterface $params,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->cache = new FilesystemAdapter("", 0, $params->get('kernel.cache_dir'));

        $this->init();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function init() {
        if(!$this->cache->hasItem(self::CACHE_LAST_INSERT)) {
            /** @var ReadingRepository $repository */
            $repository = $this->entityManager->getRepository(Reading::class);
            $dbReadingsArr = $this->toArray(
                $repository->getLastReadingEachType()
            );

            $this->setData(self::CACHE_LAST_INSERT, $dbReadingsArr);
        }
    }

    /**
     * key-value array of values. [reading type] => [reading value]
     * Persist items to database if they changed enough
     *
     * @param array $newValues
     */
    function newValues(array $newValues) {
        $lastInsertValues = $this->getData(self::CACHE_LAST_INSERT, []);
        $dbChanges = false;

        foreach($newValues as $readingType => $readingValue) {
            $threshold = Reading::getDBThresholdForValue($readingType);

            if(!isset($lastInsertValues[$readingType]) ||
                abs($readingValue - $lastInsertValues[$readingType]) >= $threshold) {
                // Value changed enough to persist it
                $lastInsertValues[$readingType] = $readingValue;
                $dbChanges = true;
                $reading = (new Reading())
                    ->setValue($readingValue)
                    ->setType($readingType);
                $this->entityManager->persist($reading);
            }
        }

        if($dbChanges) {
            $this->setData(self::CACHE_LAST_INSERT, $lastInsertValues);
            $this->entityManager->flush();
            $this->insertedInDB = true;
        }
    }

    /**
     * @param Reading[] $readings
     * @return array
     */
    public function toArray(array $readings): array {
        $rawReadings = [];

        foreach($readings as $reading) {
            $rawReadings[$reading->getType()] = $reading->getValue();
        }

        return $rawReadings;
    }
}