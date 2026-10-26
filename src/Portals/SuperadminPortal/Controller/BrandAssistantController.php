<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\BrandAssistantQa;
use App\Entity\Portal;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Fragen zu Fahrzeugmarken. Der Agent durchsucht zuerst die interne Wissensbasis, dann bei Bedarf das Internet (Perplexity).
 */
final class BrandAssistantController extends AbstractController
{
    private const SYSTEM_PROMPT = <<<PROMPT
        Du beantwortest Fragen zu Fahrzeugmarken für eine interne Tuning-Verwaltung.
        Reihenfolge: Zuerst "knowledge_search" (interne Dokumente), dann das Internet ("web_search")
        nur, wenn intern nichts Passendes steht. Aktuelle oder markenspezifische Fakten (Modelle, Baujahre,
        Motoren, Neuheiten) gehören ins Internet, wenn sie nicht intern belegt sind.
        Kennzeichne jede Quelle: "intern: <Dokumenttitel>" oder die URL. Widersprechen sich intern und Internet,
        nenne beide und bevorzuge die interne Quelle.
        Wenn du etwas nicht sicher weißt oder keine Quelle findest, sage das ausdrücklich.
        Antworte auf Deutsch, kurz und sachlich.
        PROMPT;

    private const MAX_QUESTION_LENGTH = 1000;
    private const HISTORY_LIMIT = 200;

    public function __construct(
        private readonly PortalRuntimeResolver $portalRuntimeResolver,
        #[Autowire(service: 'ai.agent.brand_research')] private readonly AgentInterface $agent,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/operations-api/brand-assistant', name: 'superadmin_portal_brand_assistant', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $payload = json_decode((string) $request->getContent(), true);
        $question = is_array($payload) ? trim((string) ($payload['question'] ?? '')) : '';
        if ('' === $question) {
            throw new BadRequestHttpException('Bitte eine Frage eingeben.');
        }
        if (mb_strlen($question) > self::MAX_QUESTION_LENGTH) {
            throw new BadRequestHttpException('Die Frage ist zu lang.');
        }

        $messages = new MessageBag(Message::forSystem(self::SYSTEM_PROMPT));

        // Kontext aus vorherigen Fragen/Antworten hinzufügen
        $history = is_array($payload['history'] ?? null) ? $payload['history'] : [];
        foreach ($history as $msg) {
            if (is_array($msg) && isset($msg['role'], $msg['text'])) {
                $role = $msg['role'];
                $text = trim((string) $msg['text']);
                if ('' !== $text) {
                    if ('user' === $role) {
                        $messages->add(Message::ofUser($text));
                    } elseif ('assistant' === $role) {
                        $messages->add(Message::ofAssistant($text));
                    }
                }
            }
        }

        // Aktuelle Frage hinzufügen
        $messages->add(Message::ofUser($question));

        // Standard-Ausgabelimit des Anthropic-Modells liegt bei 1000 Token und schneidet Antworten ab.
        $result = $this->agent->call($messages, ['max_tokens' => 2048]);
        $answer = (string) $result->getContent();

        // Frage und Antwort bleiben erhalten, damit sie später in das Wissensprojekt „Marken-Wissen” übernommen werden können.
        $this->entityManager->persist(new BrandAssistantQa($question, $answer, 'brand_research'));
        $this->entityManager->flush();

        return $this->json(['data' => ['answer' => $answer]]);
    }

    /** Die letzten gestellten Fragen mit Antworten, neueste zuerst. */
    #[Route('/operations-api/brand-assistant/history', name: 'superadmin_portal_brand_assistant_history', methods: ['GET'])]
    public function history(Request $request): JsonResponse
    {
        $this->assertAccess($request);

        $rows = $this->entityManager->getRepository(BrandAssistantQa::class)->findBy([], ['createdAt' => 'DESC', 'id' => 'DESC'], self::HISTORY_LIMIT);

        return $this->json(['data' => array_map(static fn (BrandAssistantQa $qa): array => [
            'id' => $qa->getId(),
            'question' => $qa->getQuestion(),
            'answer' => $qa->getAnswer(),
            'model' => $qa->getModel(),
            'createdAt' => $qa->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ], $rows)]);
    }

    private function assertAccess(Request $request): void
    {
        $systemType = (string) $request->attributes->get('_brand_system_type', '');
        if (SystemType::SUPERADMIN === $systemType) {
            return;
        }

        $portal = $this->portalRuntimeResolver->requirePortalSystem($request);
        if ($portal->getPortalType() !== Portal::TYPE_SUPERADMIN_PORTAL) {
            throw new NotFoundHttpException('Superadmin operations API not available for this portal.');
        }
    }
}
