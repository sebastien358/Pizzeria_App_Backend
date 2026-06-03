<?php

namespace App\Controller\Admin;

use App\Entity\Testimonial;
use App\Services\TestimonialService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin/testimonials')]
#[IsGranted("ROLE_ADMIN")]
class TestimonialAdminController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private TestimonialService $testimonialService;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, TestimonialService $testimonialService)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->testimonialService = $testimonialService;
    }

    #[Route('/list', methods: ['GET'])]
    public function index(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if(!$user){
                return $this->json(['message' => 'Utlisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $testimonials = $this->entityManager->getRepository(Testimonial::class)->findAll();
            $dataTestimonials = $this->testimonialService->getTestimonialData($request, $testimonials, $serializer);

            return $this->json($dataTestimonials, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des témoignages', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/current/{id}', methods: ['GET'])]
    public function current(int $id, Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if(!$user){
                return $this->json(['message' => 'Utlisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $testimonial = $this->entityManager->getRepository(Testimonial::class)->find($id);

            if (!$testimonial) {
                return $this->json(['message' => 'Témoignage introuvable'], Response::HTTP_BAD_REQUEST);
            }

            $dataTestimonial = $this->testimonialService->getTestimonialData($request, $testimonial, $serializer);
            return $this->json($dataTestimonial, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des témoignages', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/published/{id}', methods: ['PATCH'])]
    public function published(int $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if(!$user){
                return $this->json(['message' => 'Utlisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $testimonial = $this->entityManager->getRepository(Testimonial::class)->find($id);

            if (!$testimonial) {
                return $this->json(['message' => 'Témoignage introuvable'], Response::HTTP_BAD_REQUEST);
            }

            $testimonial->setIsPublished(!$testimonial->isPublished());

            $this->entityManager->persist($testimonial);
            $this->entityManager->flush();

            return $this->json([
                'published' => $testimonial->isPublished()
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de l\'affichage d\'un témoignage', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
