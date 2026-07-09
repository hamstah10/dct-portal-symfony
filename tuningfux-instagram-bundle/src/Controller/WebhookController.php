<?php

declare(strict_types=1);

namespace Tuningfux\InstagramBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebhookController extends AbstractController
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function verify(Request $request): Response
    {
        $mode = $request->query->get('hub_mode');
        $token = $request->query->get('hub_verify_token');
        $challenge = $request->query->get('hub_challenge');
        $expectedToken = $this->parameterBag->get('tuningfux_instagram.webhook_verify_token');

        if ($mode === 'subscribe' && $token === $expectedToken) {
            return new Response((string) $challenge, 200);
        }

        return new Response('Verification failed', 403);
    }

    public function receive(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        $this->logger->info('Instagram webhook received', [
            'payload' => $payload,
        ]);

        return new Response('EVENT_RECEIVED', 200);
    }
}
