<?php

namespace App\Controller;

use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\DateTime;

class ProjectController extends Controller
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
     * ProjectController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->projectRepository = $entityManager->getRepository('App:Project');
    }

    /**
     * @Route("/projects", name="project")
     */
    public function index()
    {
        $projects = $this->projectRepository->findByUser($this->getUser()->getId());

        $jsonContent = $this->serializeObject($projects);

        return new Response($jsonContent, Response::HTTP_OK);

    }


    /**
     * @param Request $request
     * @return Response
     * @Route("/projects/create", name="create_project")
     */
    public function saveProjects(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        if($content['name']) {

            $project = new Project();
            $project->setUser($this->getUser());
            $project->setName($content['name']);
            $project->setTimers([]);
            $project->setCreatedAt(new \DateTime());
            $project->setUpdatedAt(new \DateTime());
            $this->updateDatabase($project);

            // Serialize object into Json format
            $jsonContent = $this->serializeObject($project);

            return new Response($jsonContent, Response::HTTP_OK);
        }

        return new Response('Error', Response::HTTP_NOT_FOUND);

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
