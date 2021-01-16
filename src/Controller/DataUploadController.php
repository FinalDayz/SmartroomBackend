<?php

namespace App\Controller;

use App\Service\ActionHelper;
use App\Service\ReadingHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @param ReadingHelper $realTimeData
     * @param ActionHelper $actionHelper
     * @return Response
     */
    public function readings(Request $request, ReadingHelper $realTimeData, ActionHelper $actionHelper): Response {
        $realTimeData->updateLastConnection();

        $rawReading = json_decode($request->getContent(), true);

        $realTimeData->setReadingData($rawReading);

        $this->readingHelper->addReadings(
            $this->readingHelper->fromArray($rawReading)
        );

        if($realTimeData->isInsertedInDB()) {
            $actionHelper->handleAllAutomations();
        }

        return new JsonResponse(
            $realTimeData->getAllReadingData()
        );
    }
}