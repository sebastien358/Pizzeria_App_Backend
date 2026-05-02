<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/contact')]
class ContactController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager) {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    #[Route('/new', methods: ['POST'])]
    public function addContact(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            if (
                empty($data['firstname']) ||
                empty($data['lastname']) ||
                empty($data['email']) ||
                empty($data['message']))
            {
                return $this->json(['error' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
            }

            $contact = new Contact();

            $form = $this->createForm(ContactType::class, $contact);
            $form->submit($data);

            if (!$form->isValid()) {
                $errors = $this->getErrorMessages($form);
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return $this->json(['message' => 'Le message a bien été envoyé'], Response::HTTP_CREATED);
        } catch(\Throwable $e) {
            $this->logger->error('Impossible d\'envoyer le message', ['error' => $e->getMessage()]);
            return $this->json(['error' => 'Impossible d\'envoyer le message'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
