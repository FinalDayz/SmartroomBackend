<?php

namespace App\Controller;

use App\Service\RealTimeData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/realtime", name="realtime_")
 */
class RealTimeDataController extends AbstractController
{
    /**
     * @Route("/sensors", name="sensors")
     * @param RealTimeData $realTimeData
     * @return JsonResponse
     */
    public function standardInfo(RealTimeData $realTimeData): JsonResponse {

        return new JsonResponse(
            $realTimeData->getSensorData()
        );
    }

    /**
     * @Route("/actions", name="actions")
     * @param RealTimeData $realTimeData
     * @return JsonResponse
     */
    public function getAction(RealTimeData $realTimeData): JsonResponse {

        return new JsonResponse(
            $realTimeData->getActionData()
        );
    }
}