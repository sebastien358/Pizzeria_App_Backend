<?php

namespace App\Controller\User;

use App\Entity\Command;
use App\Services\MailerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PaymentController extends AbstractController
{
    public function __construct(
        private string $keyPrivate,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private MailerProvider $mailerProvider
    ) {}

    #[Route('/api/payment', methods: ['POST'])]
    public function payment(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            $token = $data['token'] ?? null;
            $commandId = $data['pendingId'] ?? $data['commandId'] ?? null;

            if (!$token || !$commandId) {
                return $this->json(['error' => 'Token et commandId requis'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->getUser();
            $command = $this->entityManager->find(Command::class, (int)$commandId);

            if (!$command) {
                return $this->json(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
            }

            if ($command->getUser()?->getId() !== $user->getId()) {
                return $this->json(['error' => 'Accès interdit'], Response::HTTP_FORBIDDEN);
            }

            if ($command->getStatus() !== Command::STATUS_PENDING) {
                return $this->json([
                    'type' => 'ALREADY_PROCESSED',
                    'message' => 'Commande déjà traitée'
                ], Response::HTTP_CONFLICT);
            }

            $totalAmountCents = (int) round($command->getTotal() * 100);

            if ($totalAmountCents <= 0) {
                return $this->json(['error' => 'Montant invalide'], Response::HTTP_BAD_REQUEST);
            }

            $stripe = new StripeClient($this->keyPrivate);
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $totalAmountCents,
                'currency' => 'eur',
                'payment_method_data' => [
                    'type' => 'card',
                    'card' => ['token' => $token]
                ],
                'payment_method_types' => ['card'],
                'confirm' => true,
                'return_url' => $this->getParameter('frontend_url') . '/finish',
                'metadata' => ['command_id' => $command->getId()]
            ], [
                'idempotency_key' => 'pi_cmd_' . $command->getId() . '_' . $command->getUpdatedAt()->getTimestamp()
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $command->setStatus(Command::STATUS_PAID);
                $this->entityManager->flush();

                $this->sendConfirmationEmail($command, $command->getTotal(), $user);

                return $this->json([
                    'type' => 'SUCCESS_PAYMENT',
                    'message' => 'Paiement accepté',
                ], Response::HTTP_CREATED);
            }

            if ($paymentIntent->status === 'requires_action') {
                return $this->json([
                    'type' => 'REQUIRES_ACTION',
                    'clientSecret' => $paymentIntent->client_secret
                ], Response::HTTP_OK);
            }

            return $this->json([
                'type' => 'ERROR_PAYMENT',
                'message' => 'Paiement refusé'
            ], Response::HTTP_CONFLICT);

        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['error' => 'Paiement refusé par la banque'], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $this->logger->error('Serveur: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['error' => 'Erreur serveur'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function sendConfirmationEmail(Command $command, float $totalAmount, $user): void
    {
        $params = [
            'firstname' => $command->getFirstName(),
            'lastname' => $command->getLastName(),
            'commandItems' => $command->getCommandItems(),
            'totalAmount' => $totalAmount
        ];

        $body = $this->renderView('emails/payment.html.twig', $params);
        $this->mailerProvider->sendEmail($user->getEmail(), 'Vous avez passé une commande', $body);
    }
}
