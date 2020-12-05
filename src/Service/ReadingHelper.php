<?php

namespace App\Service;

use App\Entity\Reading;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Psr\Log\LoggerInterface;

class ReadingHelper
{
    /**
     * @var ObjectManager
     */
    private $entityManager;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @param $readings
     * @throws Exception
     */
    public function addReadings($readings)
    {
        foreach ((array) $readings as $reading) {
            $this->entityManager->persist($reading);
        }
        $this->entityManager->flush();
    }

    /**
     * @param array $rawReading
     * @return Reading[]
     */
    public function readingFromRaw(array $rawReading): array
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
                    $this->logger->notice("Invalid value while parsing reading (not numeric).".
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
    public function rawFromReadings(array $readings): array {
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
}