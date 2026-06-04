<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Testimonial;
use App\Form\TestimonialType;
use App\Services\FileUploader;
use App\Services\TestimonialService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/testimonial')]
final class TestimonialController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private FileUploader $fileUploader;
    private TestimonialService $testimonialService;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, FileUploader $fileUploader, TestimonialService $testimonialService)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->testimonialService = $testimonialService;
    }

    #[Route('/home', methods: ['GET'])]
    public function index(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $testimonials = $this->entityManager->getRepository(Testimonial::class)->findAllTestimonialsHomePage();
            if(empty($testimonials)) {
                return $this->json([], Response::HTTP_OK);
            }

            $dataTestimonials = $this->testimonialService->getTestimonialData($request, $testimonials , $serializer);

            return $this->json($dataTestimonials, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la récupération de la liste des témoignage de la page home', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $currentPage = (int) $request->query->get('currentPage');
            $limit = (int) $request->query->get('limit');

            if ($currentPage < 1 && $limit < 1) {
                return $this->json(['message' => "Les données ne sont pas correctes"], Response::HTTP_BAD_REQUEST);
            }

            $testimonials = $this->entityManager->getRepository(Testimonial::class)->findAllPaginated($currentPage, $limit);
            $dataTestimonials = $this->testimonialService->getTestimonialData($request, $testimonials, $serializer);

            $countTestimonials = $this->entityManager->getRepository(Testimonial::class)->findAllCount();
            $average = $this->entityManager->getRepository(Testimonial::class)->getAverageRating();

            return $this->json([
                'testimonials' => $dataTestimonials,
                'countTestimonials' => (int) $countTestimonials,
                'pages' => ceil($countTestimonials / $limit),
                'averageRating' => round($average, 1), // <- ajoute ça
            ], Response::HTTP_OK);
        } catch(\Throwable $exception) {
            $this->logger->error('Erreur de la récupération des témoignages : ', [$exception->getMessage()]);
            return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        try {
            $testimonial = new Testimonial();

            $form = $this->createForm(TestimonialType::class, $testimonial);

            $data = $request->request->all();
            $form->submit($data, false);

            if (!$form->isValid()) {
                $errors = $this->getErrorMessages($form);
                return $this->json(['error' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $images = $request->files->get('images') ?? [];

            foreach ($images as $image) {
                if ($image->getSize() > 5 * 1024 * 1024) {
                    throw new \Exception('La taille de l\'image est trop grande'. $image->getClientOriginalName());
                }

                $picture = new Picture();

                $filename = $this->fileUploader->upload($image);

                $picture->setFilename($filename);
                $picture->setTestimonial($testimonial);

                $this->entityManager->persist($picture);
            }

            $this->entityManager->persist($testimonial);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le témoignage a été envoyé'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de l\'ajout d\'un témoignage : ', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getErrorMessages(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $child) {
            if ($child->isSubmitted() && !$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }
        return $errors;
    }
}
