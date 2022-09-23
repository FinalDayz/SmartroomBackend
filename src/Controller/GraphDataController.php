<?php

namespace App\Controller;

use App\Repository\ReadingRepository;
use App\Service\ActionHelper;
use App\Service\ReadingHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/graphdata", name="graphdata_")
 */
class GraphDataController extends AbstractController
{

    /**
     * @Route("/datatype/{type}/{secondsInterval}", name="datatype", methods={"GET"})
     * @param string $type
     * @param int $secondsInterval
     * @param ReadingRepository $readingRepository
     * @return Response
     */
    public function readings(
        string            $type,
        int               $secondsInterval,
        ReadingRepository $readingRepository
    ): Response
    {
        $result = $readingRepository->getMaxMinTimeInterval($type, $secondsInterval);

        return new JsonResponse(
            $result
        );
    }
}