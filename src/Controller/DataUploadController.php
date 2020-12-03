<?php

namespace App\Controller;

use App\Service\ReadingHelper;
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
     * @var ReadingHelper
     */
    private $readingHelper;

    public function __construct(ReadingHelper $readingHelper)
    {
        $this->readingHelper = $readingHelper;
    }

    /**
     * @Route("/sensorData", name="sensor_data", methods={"POST"})
     * @param Request $request
     * @param RealTimeData $realTimeData
     * @return Response
     */
    public function uploadSensorData(Request $request, RealTimeData $realTimeData): Response {
        $rawReading = json_decode($request->getContent(), true);

        $realTimeData->setSensorData($rawReading);

        $readingArr = $this->readingHelper->parseReading($rawReading);
        $this->readingHelper->addReadings($readingArr);

        return $this->redirectToRoute("realtime_actions");
    }
}