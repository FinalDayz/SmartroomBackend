<?php

namespace App\Service;

use App\Entity\Reading;
use App\Repository\ReadingRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class ReadingHelper extends AbstractCacheManager
{
    // Values are the most up to date
    private const CACHE_KEY_READINGS = 'cache.realtime_readings';
    // Values are always in sync with the DB
    private const CACHE_KEY_LAST_INSERT = 'cache.last_insert';
    private const CACHE_KEY_TIME_LAST_CONNECTION = 'cache.time_last_connection';
    private const CACHE_KEY_HAS_RECENT_CONNECTION = 'cache.time_last_connection_value';
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
     * @return bool
     */
    public function isInsertedInDB(): bool
    {
        return $this->insertedInDB;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function init() {
        if(!$this->cache->hasItem(self::CACHE_KEY_READINGS)) {
            /** @var ReadingRepository $repository */
            $repository = $this->entityManager->getRepository(Reading::class);
            $dbReadingsArr = $this->toArray(
                $repository->getLastReadingEachType()
            );


            $this->setData(self::CACHE_KEY_READINGS, $dbReadingsArr);
            $this->setData(self::CACHE_KEY_LAST_INSERT, $dbReadingsArr);

            $latestReading = $repository->lastInsertTime();
            $this->setData(self::CACHE_KEY_TIME_LAST_CONNECTION,
                $latestReading != null ? $latestReading->getTime() : null
            );
            $this->setData(self::CACHE_KEY_HAS_RECENT_CONNECTION, $this->connectionIsDown());
        }
    }

    /**
     * return true if `updateLastConnection` has not been called at least for 60 seconds
     *
     * @return bool
     * @throws Exception
     */
    public function connectionIsDown() {
        /** @var DateTimeImmutable|null $lastConnectionTime */
        $lastConnectionTime = $this->getData(self::CACHE_KEY_TIME_LAST_CONNECTION);
        if($lastConnectionTime != null) {
            $diffInSeconds = $lastConnectionTime->getTimestamp() - (new DateTimeImmutable())->getTimestamp();
            return $diffInSeconds > 60;
        }

        return true;
    }

    /**
     * @return bool|null
     */
    public function getLastConnectionValue() {
        return $this->getData(self::CACHE_KEY_HAS_RECENT_CONNECTION);
    }

    /**
     * @param bool $isUp
     */
    public function connectionChanged(bool $isUp) {
        $connectionReading = new Reading();
        $connectionReading->setType('connection');
        $connectionReading->setValue($isUp ? 1 : 0);
        $this->entityManager->persist($connectionReading);
        $this->entityManager->flush();
        $this->insertedInDB = true;
    }

    public function updateLastConnection() {
        if(!$this->getLastConnectionValue()) {
            $this->setData(self::CACHE_KEY_HAS_RECENT_CONNECTION, true);
            $this->connectionChanged(true);
        }
        $this->setData(self::CACHE_KEY_TIME_LAST_CONNECTION,
            new DateTimeImmutable()
        );
    }

    /**
     * @param Reading[] $readings
     * @param bool $silent
     */
    public function addReadings($readings, $silent=false)
    {
        $lastDBValues = $this->getData(self::CACHE_KEY_LAST_INSERT);
        $dbChanges = false;
        foreach ((array) $readings as $reading) {
            $readingType = $reading->getType();
            $lastDBValue = $lastDBValues[$readingType];

            if($lastDBValue == null ||
                abs($lastDBValue - $reading->getValue()) >= Reading::DB_UPLOAD_THRESHOLD[$readingType]
            ) {
                $lastDBValues[$readingType] = $reading->getValue();
                $dbChanges = true;
                $this->entityManager->persist($reading);
            }
        }
        if($dbChanges) {
            $this->setData(self::CACHE_KEY_LAST_INSERT, $lastDBValues);
            $this->entityManager->flush();
            $this->insertedInDB = true;
        }
    }


    /**
     * @param array $rawReading
     * @return Reading[]
     */
    public function fromArray(array $rawReading): array
    {
        $readings = [];

        foreach (Reading::VALID_TYPES as $availableType) {
            if (isset($rawReading[$availableType])) {
                $value = $rawReading[$availableType];
                if (is_numeric($value)) {
                    array_push(
                        $readings,
                        $this->newReading($availableType, $rawReading[$availableType])
                    );
                } else {
                    $this->logger->warning("Invalid value while parsing reading (not numeric).".
                        "Value: '" . $value . "', reading type: '" . $availableType . "'");
                }
            }
        }

        return $readings;
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

    /**
     * @param string $type
     * @param float $value
     * @return Reading
     */
    public function newReading(string $type, float $value): Reading
    {
        $reading = new Reading();
        $reading->setType($type);
        $reading->setValue($value);

        return $reading;
    }

    /**
     * @param Reading[]|null $readings
     * @param string $type
     * @return Reading|mixed|null
     */
    private function findType(?array $readings, $type)
    {
        if(!is_countable($readings))
            return null;

        foreach ($readings as $reading) {
            if($reading->getType() == $type) {
                return $reading;
            }
        }

        return null;
    }

    /**
     * @return array
     */
    public function getAllReadingData(): array
    {
        $allData = $this->getData(self::CACHE_KEY_READINGS);
        $lastConnection = $this->getData(self::CACHE_KEY_TIME_LAST_CONNECTION);
        if($lastConnection instanceof DateTimeImmutable) {
            $allData['lastConnection'] = $lastConnection->format("d-m-Y H:i:s");
        }
        return $allData;
    }

    /**
     * @param array $data
     */
    public function setReadingData(array $data)
    {
        $oldReadingArr = $this->getAllReadingData();
        $curReading = $this->fromArray($data);
        $newReading = array_merge(
            $this->fromArray($oldReadingArr),
            $curReading
        );

        $this->setData(self::CACHE_KEY_READINGS, $this->toArray($newReading));
    }

}