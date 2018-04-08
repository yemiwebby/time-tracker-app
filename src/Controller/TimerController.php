<?php

namespace App\Controller;

use App\Entity\Timer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class TimerController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $projectRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $timerRepository;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->projectRepository = $entityManager->getRepository('App:Project');
        $this->timerRepository = $entityManager->getRepository('App:Timer');
    }

    /**
     * @Route("/projects/{id}/timers", name="timer")
     */
    public function createTimer(Request $request, int $id)
    {
        $content = json_decode($request->getContent(), true);

        $project = $this->projectRepository->find($id);

        $timer = new Timer();
        $timer->setName($content['name']);
        $timer->setUser($this->getUser());
        $timer->setProject($project);
        $timer->setStartedAt(new \DateTime());
        $timer->setCreated(new \DateTime());
        $timer->setUpdated(new \DateTime());
        $this->updateDatabase($timer);

        // Serialize object into Json format
        $jsonContent = $this->serializeObject($timer);

        return new Response($jsonContent, Response::HTTP_OK);

    }

    /**
     * @Route("/project/timers/active", name="active_timer")
     */
    public function runningTimer()
    {
        $timer = $this->timerRepository->findRunningTimer($this->getUser()->getId());

        $jsonContent = $this->serializeObject($timer);

        return new Response($jsonContent, Response::HTTP_OK);
    }

    /**
     * @Route("/projects/{id}/timers/stop", name="stop_running")
     */
    public function stopRunningTimer()
    {
        $timer = $this->timerRepository->findRunningTimer($this->getUser()->getId());

        if ($timer) {
            $timer->setStoppedAt(new \DateTime());
            $this->updateDatabase($timer);
        }

        // Serialize object into Json format
        $jsonContent = $this->serializeObject($timer);

        return new Response($jsonContent, Response::HTTP_OK);
    }

    public function serializeObject($object)
    {
        $encoders = new JsonEncoder();
        $normalizers = new ObjectNormalizer();

        $normalizers->setCircularReferenceHandler(function ($obj) {
            return $obj->getId();
        });
        $serializer = new Serializer(array($normalizers), array($encoders));

        $jsonContent = $serializer->serialize($object, 'json');

        return $jsonContent;
    }


    public function updateDatabase($object)
    {
        $this->entityManager->persist($object);
        $this->entityManager->flush();
    }
}
