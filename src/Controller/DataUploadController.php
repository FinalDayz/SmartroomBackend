<?php

namespace App\Controller;

use App\Service\ReadingHelper;
use App\Service\RealTimeData;
use Exception;
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
     * @Route("/readings", name="readings", methods={"POST"})
     * @param Request $request
     * @param RealTimeData $realTimeData
     * @return Response
     * @throws Exception
     */
    public function readings(Request $request, RealTimeData $realTimeData): Response {
        $rawReading = json_decode($request->getContent(), true);

        $realTimeData->setReadingData($rawReading);

        $readingArr = $this->readingHelper->readingFromRaw($rawReading);
        $this->readingHelper->addReadings($readingArr);

        return $this->redirectToRoute("realtime_readings");
    }
}