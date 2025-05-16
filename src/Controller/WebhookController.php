<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\Vpn\FreeTrialNotEligibleException;
use App\Service\Telegram\TelegramBotHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebhookController extends AbstractController
{
    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function handleWebhook(Request $request, TelegramBotHandlerInterface $botHandler, LoggerInterface $logger): Response
    {
        $content = $request->getContent();

        $logger->info('WebhookController::handleWebhook - Webhook request received', [
            'content_length' => strlen($content),
            'user_agent' => $request->headers->get('User-Agent'),
            'ip' => $request->getClientIp(),
            'method' => $request->getMethod(),
            'headers' => $request->headers->all(),
        ]);

        if (empty($content)) {
            $logger->warning('WebhookController::handleWebhook - Webhook received empty content');

            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        $update = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $logger->warning('WebhookController::handleWebhook - Webhook received invalid JSON', [
                'json_error' => json_last_error_msg(),
                'content_preview' => substr($content, 0, 200),
            ]);

            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        try {
            $botHandler->handleUpdate($update);

            $logger->info('WebhookController::handleWebhook - Webhook processed successfully', [
                'update_id' => $update['update_id'] ?? null,
            ]);
        } catch (FreeTrialNotEligibleException $e) {
            $logger->warning('WebhookController::handleWebhook - Free trial not eligible', [
                'error' => $e->getMessage(),
                'update_id' => $update['update_id'] ?? null,
            ]);

            return new Response('', Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            $logger->error('WebhookController::handleWebhook - Webhook processing failed', [
                'error' => $e->getMessage(),
                'update_id' => $update['update_id'] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response('', Response::HTTP_OK);
    }
}
