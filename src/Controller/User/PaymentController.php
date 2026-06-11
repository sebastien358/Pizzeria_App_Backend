<?php

namespace App\Controller\User;

use App\Entity\Command;
use App\Services\MailerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PaymentController extends AbstractController
{
    private string $keyPrivate;
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;
    private MailerProvider $mailerProvider;

    public function __construct(string $keyPrivate, LoggerInterface $logger, EntityManagerInterface $entityManager, MailerProvider $mailerProvider) {
        $this->keyPrivate = $keyPrivate;
        $this->logger = $logger;
        $this->entityManager = $entityManager;
        $this->mailerProvider = $mailerProvider;
    }

    #[Route('/api/payment', methods: ['POST'])]
    public function payment(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $token = $data['token'] ?? null;

            $profileId = $data['profileId'] ?? null;
            $pendingId = $data['pendingId'] ?? null;

            if (!$token) {
                return $this->json(['error' => 'Token Stripe requis'], Response::HTTP_FORBIDDEN);
            }

            $user = $this->getUser();

            if (!$user) {
                return $this->json(['error' => 'Utilisateur non connecté'], Response::HTTP_UNAUTHORIZED);
            }

            $commandId = $profileId ?? $pendingId;
            if (!$commandId) {
                return $this->json(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
            }

            $command = $this->entityManager->getRepository(Command::class)->find($commandId);
            if (!$command) {
                return $this->json(['error' => 'Command introuvable'], Response::HTTP_NOT_FOUND);
            }

            $totalAmount = (int) $command->getTotal();

            // Montant en centimes pour Stripe

            $totalAmountCents = (int) ($totalAmount * 100);

            $stripe = new \Stripe\StripeClient($this->keyPrivate);
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $totalAmountCents,
                'currency' => 'eur',
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => ['token' => $token]
                ],
                'payment_method_types' => ['card'],
                'confirm' => true,
            ]);

            if ($paymentIntent->status === 'succeeded') {

                // Mettre à jour le statut de la commande

                if (isset($command)) {
                    $command->setStatus(Command::STATUS_PAID);
                }

                // Flush unique pour tout enregistrer

                $this->entityManager->flush();

                $this->sendConfirmationEmail($profileId, $command, $totalAmount, $user);

                return $this->json([
                    'type' => 'SUCCESS_PAYMENT',
                    'message' => 'Paiement accepté',
                ], Response::HTTP_CREATED);

            } else {
                return $this->json([
                    'type' => 'ERROR_PAYMENT',
                    'message' => 'Une erreur est survenue'
                ], Response::HTTP_CONFLICT);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            $this->logger->error('Erreur Stripe : ' . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $e) {
            $this->logger->error('Erreur serveur : ' . $e->getMessage());
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sendConfirmationEmail(int $profileId, Command $command, float $totalAmount, UserInterface $user): void
    {
        if ($profileId) {
            $params = [
                'firstname' => $command->getFirstName(),
                'lastname' => $command->getLastName(),
                'commandItems' => $command->getCommandItems(),
                'totalAmount' => $totalAmount
            ];
        } else {
            $params = [
                'firstname' => $command->getFirstName(),
                'lastname' => $command->getLastName(),
                'commandItems' => $command->getCommandItems(),
                'totalAmount' => $totalAmount
            ];
        }

        $body = $this->render('emails/payment.html.twig', $params)->getContent();
        $this->mailerProvider->sendEmail($user->getEmail(), 'Vous avez passé une commande', $body);
    }
}
