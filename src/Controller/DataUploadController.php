<?php

namespace App\Controller;

use App\Service\ReadingHelper;
use Exception;
use Psr\Cache\InvalidArgumentException;
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
     * @return Response
     * @throws Exception
     */
    public function readings(Request $request, ReadingHelper $realTimeData): Response {
        $rawReading = json_decode($request->getContent(), true);

        $realTimeData->setReadingData($rawReading);

        $this->readingHelper->addReadings(
            $this->readingHelper->fromArray($rawReading)
        );

        return new JsonResponse(
            $realTimeData->getReadingData()
        );
    }
}