<?php

namespace App\Service;

use App\Entity\Automation;
use App\Entity\Reading;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ActionHelper extends AbstractCacheManager
{
    private const CACHE_KEY_AUTOMATION_STARTED_ACTIVATING_AT = 'cache.time_automation_activated';

    /**
     * @var ReadingHelper
     */
    private $readingHelper;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var null
     */
    private $lastReadings;
    /**
     * @var HttpClientInterface
     */
    private $httpClient;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var array
     */
    private $automationActivatedAt;

    /**
     * ReadingHelper constructor.
     * @param ContainerBagInterface $params
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param ReadingHelper $readingHelper
     * @param HttpClientInterface $httpClient
     */
    public function __construct(
        ContainerBagInterface $params,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ReadingHelper $readingHelper,
        HttpClientInterface $httpClient
    ) {
        $this->entityManager = $entityManager;
        $this->readingHelper = $readingHelper;
        $this->logger = $logger;
        $this->lastReadings = null;
        $this->httpClient = $httpClient;

        $this->cache = new FilesystemAdapter("", 0, $params->get('kernel.cache_dir'));

        $this->init();
    }

    private function init() {
        $this->automationActivatedAt = $this->getData(self::CACHE_KEY_AUTOMATION_STARTED_ACTIVATING_AT, []);
    }

    public function fetchReadings() {
        if($this->lastReadings == null) {
            $this->lastReadings = $this->readingHelper->getAllReadingData();
        }
    }

    public function handleAllAutomations() {
        $repository = $this->entityManager->getRepository(Automation::class);
        $automations = $repository->findAll();
        foreach ($automations as $automation) {
            $this->handleAutomation($automation);
        }
    }

    public function handleAutomation(Automation $automation) {
        $this->fetchReadings();

        $isActivated = isset($this->automationActivatedAt[$automation->getId()]);

        $shouldSkipExecution = $isActivated && !$automation->getRepeatActivation();

        if($automation->getEnabled() &&
            !$shouldSkipExecution &&
            $this->ifArrIsTrue(
                json_decode($automation->getIfJson(), true)
            )
        ) {

            $this->automationActivatedAt[$automation->getId()] = new DateTimeImmutable();
            $this->updateAutomationActivatedAt();

            $this->executeActions(
                json_decode($automation->getActionsJson(), true)
            );
        } else if($isActivated) {
            unset($this->automationActivatedAt[$automation->getId()]);
            $this->updateAutomationActivatedAt();
        }
    }

    private function updateAutomationActivatedAt() {
        $this->setData(self::CACHE_KEY_AUTOMATION_STARTED_ACTIVATING_AT, $this->automationActivatedAt);
    }

    private function executeActions($actions) {
        foreach($actions as $action) {
            $type = $action['type'];
            $data = $action['data'];
            switch ($type) {
                case "heater":
                    $this->heaterAction($data);
                    break;
                case "notification":
                    $this->sendNotification($data);
                    break;
            }
        }
    }

    private function heaterAction($data) {
        $heaterReading = new Reading();
        $heaterReading->setType("heater");
        $heaterReading->setValue($data['isOn']);
        $this->readingHelper->addReadings([$heaterReading]);
        $this->readingHelper->setReadingData(
            $this->readingHelper->toArray([$heaterReading])
        );
    }

    private function sendNotification($data) {
        $url = 'https://maker.ifttt.com/trigger/notification/with/key/' . $_SERVER['IFTTT_NOTIFICATION_KEY'];

        $requestBody = [
            'value1' => $this->applyStrKeywords($data['title']),
            'value2' => $this->applyStrKeywords($data['content']),
        ];

        try {
            $this->httpClient->request(
                "POST",
                $url,
                [
                    'json' => $requestBody
                ]
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->warning("TransportExceptionInterface while sendNotification(), " . $e->getMessage());
        }

    }

    private function applyStrKeywords($str) {
        foreach (Reading::VALID_TYPES as $type) {
            if(isset($this->lastReadings[$type]))
                $str = str_replace("{".$type."}", $this->lastReadings[$type], $str);
        }
        return $str;
    }

    private function ifArrIsTrue($ifsObj): bool {
        if(count($ifsObj) === 0) {
            return true;
        }
        foreach($ifsObj as $if) {
            $input = $if['input'];
            $condition = $if['condition'];
            $value = $if['value'];

            $hasAnd = isset($if['and']) && is_countable($if['and']) && is_array($if['and']);

            $inputValue = $this->lastReadings[$input];

             if($this->conditionIsTrue($inputValue, $condition, $value)) {

                // If is true at this point
                // Check if the AND value(s) are also true (if there are any)
                if($hasAnd) {
                    return $this->ifArrIsTrue($if['and']);
                } else {
                    return true;
                }
            }
        }

        return false;
    }

    //">=", "<=", "==", "!=", "<", ">"
    private function conditionIsTrue($inputValue, $condition, $checkValue): bool {
        $inputValue = floatval($inputValue);
        $checkValue = floatval($checkValue);
        switch ($condition) {
            case '>=':
                return $inputValue >= $checkValue;
            case '<=':
                return $inputValue <= $checkValue;
            case '==':
                return $inputValue == $checkValue;
            case '!=':
                return $inputValue != $checkValue;
            case '<':
                return $inputValue < $checkValue;
            case '>':
                return $inputValue > $checkValue;
        }

        $this->logger->warning("Invalid condition used $condition");

        return false;
    }

}