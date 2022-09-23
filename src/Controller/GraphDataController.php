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
     * @Route("/datatype/{type}/{secondsInterval}/{limit}", name="datatype", methods={"GET"})
     * @param ReadingRepository $readingRepository
     * @param string $type
     * @param int $secondsInterval
     * @param int $limit
     * @return Response
     */
    public function readings(
        ReadingRepository $readingRepository,
        string            $type,
        int               $secondsInterval,
        int               $limit = 60
    ): Response
    {
        $result = $readingRepository->getMaxMinTimeInterval($type, $secondsInterval, $limit);

        return new JsonResponse(
            $result
        );
    }
}