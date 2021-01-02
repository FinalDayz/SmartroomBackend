<?php

namespace App\Controller;

use App\Service\ReadingHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacySupportController extends AbstractController
{
    /**
     * @Route("/status", name="legacy_status")
     */
    public function status(ReadingHelper $realTimeData): Response
    {
        return new JsonResponse($this->statusData($realTimeData));
    }

    private function statusData(ReadingHelper $realTimeData): array {
        return array_merge(
            [
                'color' =>[
                    'r' => 0,
                    'g' => 0,
                    'b' => 0,
                ],
                'buzzer' => [],
            ],
            $realTimeData->getReadingData()
        );
    }

    /**
     * @Route("/set/heater", name="legacy_set_heater")
     */
    public function set_heater(Request $request, ReadingHelper $realTimeData): Response
    {
        $body = json_decode($request->getContent(), true);
        $heaterOn = $body['heater'] == 'true';
        $manualArr = [
            "heater" => $heaterOn ? 1 : 0
        ];

        $realTimeData->setReadingData($manualArr);

        $realTimeData->addReadings(
            $realTimeData->fromArray($manualArr)
        );

        return new JsonResponse($this->statusData($realTimeData));
    }

    /**
     * @Route("/reading", name="legacy_reading")
     */
    public function reading(Request $request, ReadingHelper $realTimeData) {
        $realTimeData->updateLastConnection();

        $rawReading = json_decode($request->getContent(), true);

        $realTimeData->setReadingData($rawReading);

        $realTimeData->addReadings(
            $realTimeData->fromArray($rawReading)
        );

        return new JsonResponse(
            $realTimeData->getReadingData()
        );
    }

}