<?php

namespace App\Service;

use App\Entity\Reading;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Log\Logger;

class ReadingHelper
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param $readings
     * @throws Exception
     */
    public function addReadings($readings)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = $this->container->get('doctrine')->getManager();
        foreach ((array) $readings as $reading) {
            $objectManager->persist($reading);
        }
        $objectManager->flush();
    }

    /**
     * @param array $rawReading
     * @return Reading[]
     */
    public function parseReading(array $rawReading): array
    {
        $readings = [];

        foreach (Reading::VALID_TYPES as $availableType) {
            if (isset($rawReading[$availableType])) {
                $value = $rawReading[$availableType];
                if (is_numeric($value)) {
                    array_push(
                        $readings,
                        $this->newReading($availableType, $rawReading['temperature'])
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