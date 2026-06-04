<?php

namespace App\Controller\Admin;

use App\Entity\Testimonial;
use App\Services\FileUploader;
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
    private FileUploader $fileUploader;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, TestimonialService $testimonialService, FileUploader $fileUploader)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->testimonialService = $testimonialService;
        $this->fileUploader = $fileUploader;
    }

    #[Route('/list', methods: ['GET'])]
    public function index(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utlisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $currentPage = (int) $request->query->get('currentPage');
            $limit = (int) $request->query->get('limit');

            if (!number_format($currentPage) || !number_format($limit)) {
                return $this->json(['error' => 'Donneés manquantes'], Response::HTTP_BAD_REQUEST);
            }

            $testimonials = $this->entityManager->getRepository(Testimonial::class)->findAllPaginatedAdmin($currentPage, $limit);
            $totalTestimonials = $this->entityManager->getRepository(Testimonial::class)->findAllCount();

            $dataTestimonials = $this->testimonialService->getTestimonialData($request, $testimonials, $serializer);

            return $this->json([
                'testimonials' => $dataTestimonials,
                'totalTestimonials' => (int) $totalTestimonials,
                'pages' => ceil($totalTestimonials / $limit)
            ], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des témoignages', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/lazy-load', methods: ['GET'])]
    public function load(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'Utlisateur introuvable'], Response::HTTP_UNAUTHORIZED);
            }

            $search = (string) $request->query->get('search');

            if (!is_string($search)) {
                return $this->json(['error' => 'Donneé attendu manquante'], Response::HTTP_BAD_REQUEST);
            }

            $testimonials = $this->entityManager->getRepository(Testimonial::class)->findAllAdminLazyLoad($search);
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

            $testimonial->setIsRead(true);

            $this->entityManager->persist($testimonial);
            $this->entityManager->flush();

            return $this->json($dataTestimonial, Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Erreur de la récupération des témoignages', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/current/{id}/delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
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

            $images = $testimonial->getPictures();
            $this->fileUploader->removeTestimonialImage($images);

            $this->entityManager->remove($testimonial);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le témoignage a été supprimé'], Response::HTTP_OK);
        } catch(\Throwable $e) {
            $this->logger->error('Le témoignage a été supprimé', [$e->getMessage()]);
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
