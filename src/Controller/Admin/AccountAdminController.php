<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin/account')]
#[IsGranted('ROLE_ADMIN')]
class AccountAdminController extends AbstractController
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/me', methods: ['GET'])]
    public function accountMe(SerializerInterface $serializer): Response
    {
        try {
            $user = $this->getUser();

            if (!$user) {
                return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            $dataUser = $serializer->normalize($user, 'json', ['groups' => ['user']]);

            return $this->json($dataUser, Response::HTTP_OK);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la récupération des données utilisateur : ', [$e->getMessage()]);
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/edit/{id}', methods: ['PATCH'])]
    public function accountEdit(int $id, Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
            $password = $data['password'] ?? null;
            $email = $data['email'] ?? null;

            $user = $this->getUser();

            if (!$user || $user->getId() !== $id) {
                return $this->json(['error' => 'User not found'], Response::HTTP_UNAUTHORIZED);
            }

            $userExist = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if (!$userExist) {
                return $this->json(['type' => 'ACCOUNT_ERROR_EDIT_ADMIN', 'message' => 'Aucun compte n\'existe avec cet email'], Response::HTTP_CONFLICT);
            }

            $form = $this->createForm(UserType::class, $user);

            $data = $request->request->all();
            $form->submit($data, false);

            if (!$form->isValid()) {
                $errors = $this->getErrorMessages($form);
                return $this->json($errors, Response::HTTP_BAD_REQUEST);
            }

            if ($password) {
               $newPassword = $this->passwordHasher->hashPassword($user, $password);
               $user->setPassword($newPassword);
            }

            $this->entityManager->flush();

            return $this->json(['message' => 'Données modifiées'], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur de la modification des données utilisateur : ', [$e->getMessage()]);
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
