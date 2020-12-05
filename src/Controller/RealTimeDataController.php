<?php

namespace App\Controller;

use App\Service\RealTimeData;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/realtime", name="realtime_")
 */
class RealTimeDataController extends AbstractController
{
    /**
     * @Route("/readings", name="readings")
     * @param RealTimeData $realTimeData
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function readingData(RealTimeData $realTimeData): JsonResponse {

        return new JsonResponse(
            $realTimeData->getReadingData()
        );
    }

    /**
     * @Route("/actions", name="actions")
     * @param RealTimeData $realTimeData
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    public function getAction(RealTimeData $realTimeData): JsonResponse {

        return new JsonResponse(
            $realTimeData->getReadingData()
        );
    }
}