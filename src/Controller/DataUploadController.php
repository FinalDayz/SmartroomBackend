<?php

namespace App\Controller;

use App\Service\RealTimeData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/upload", name="upload_")
 */
class DataUploadController extends AbstractController
{

    /**
     * @Route("/sensorData", name="sensor_data", methods={"POST"})
     * @param Request $request
     * @param RealTimeData $realTimeData
     * @return Response
     */
    public function uploadSensorData(Request $request, RealTimeData $realTimeData): Response {
        $data = json_decode($request->getContent(), true);

        $realTimeData->setSensorData($data);

        return $this->redirectToRoute("realtime_actions");
    }
}