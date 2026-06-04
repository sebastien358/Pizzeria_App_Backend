<?php

namespace App\Controller;

use App\Entity\Product;
use App\Services\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/product', methods: ['GET'])]
final class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProductService $productService;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, ProductService $productService)
    {
        $this->entityManager = $entityManager;
        $this->productService = $productService;
        $this->logger = $logger;
    }

    #[Route('/list', methods: ['GET'])]
    public function list(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $offset = (int) $request->query->get('offset');
            $limit = (int) $request->query->get('limit');

            if ($offset < 0 || $limit < 0) {
                return $this->json(['message' => 'Paramètres de pagination manquants'], Response::HTTP_BAD_REQUEST);
            }

            $products = $this->entityManager->getRepository(Product::class)->findAllLoadProducts($offset, $limit);

            $dataProducts = $this->productService->getProductData($request, $products, $serializer);

            return $this->json($dataProducts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la récupération de la liste des produits', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/search', methods: ['GET'])]
    public function search(Request $request, SerializerInterface $serializer): JsonResponse
    {
        try {
            $search = $request->query->get('search');

            if (!$search || !is_string($search)) {
                return $this->json(['error' => 'Paramètre search obligatoire'], Response::HTTP_BAD_REQUEST);
            }

            $search = trim((string) $search);

            $products = $this->entityManager->getRepository(Product::class)->findAllSearch($search);

            $dataProducts = $this->productService->getProductData($request, $products, $serializer);

            return $this->json($dataProducts, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la récupération de la liste des produits filtré : (search)', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
