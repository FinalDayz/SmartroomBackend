<?php

namespace App\Controller;

use App\Service\ReadingHelper;
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
     * @param ReadingHelper $realTimeData
     * @return JsonResponse
     */
    public function readingData(ReadingHelper $realTimeData): JsonResponse {

        return new JsonResponse(
            $realTimeData->getReadingData()
        );
    }
}