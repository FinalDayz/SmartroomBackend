<?php

namespace App\Controller;

use App\Entity\Automation;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/automation", name="automation_")
 */
class AutomationController extends AbstractController
{

    /**
     * @var ObjectManager
     */
    private $em;
    /**
     * @var ObjectRepository
     */
    private $repository;

    public function init()
    {
        $this->repository = $this->getDoctrine()->getRepository(Automation::class);

        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @return Response
     */
    public function addAutomation(Request $request) {
        $this->init();
        $automationData = json_decode($request->getContent(), true);

        $automation = $this->jsonToAutomation($automationData);

        $this->em->persist($automation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/modify/{id}", name="modify")
     * @param Request $request
     * @return Response
     */
    public function modifyAutomation(int $id, Request $request) {
        $this->init();
        $automation = $this->repository->find($id);
        $automationData = json_decode($request->getContent(), true);

        $automation = $this->jsonToAutomation($automationData, $automation);

        $this->em->persist($automation);
        $this->em->flush();

        return new Response();
    }

    /**
     * @Route("/all", name="all")
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function getAutomations(SerializerInterface $serializer) {
        $this->init();
        $automations = $this->repository->findAll();
        $automations = $serializer->serialize($automations, 'json');

        return new Response($automations, 200, ['content-type' => 'text/html']);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(int $id) {
        $this->init();
        $automation = $this->repository->find($id);
        $this->em->remove($automation);
        $this->em->flush();

        return new Response();
    }


    private function jsonToAutomation($jsonData, $automation = null) {
        if($automation === null) {
            $automation = new Automation();
        }
        $automation->setName($jsonData['name']);
        $automation->setEnabled($jsonData['enabled']);
        $automation->setIfJson(json_encode($jsonData['ifs']));
        $automation->setActionJson(json_encode($jsonData['action']));

        return $automation;
    }
}