<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\BrandAssistantBatchJob;
use App\Entity\BrandAssistantBatchJobItem;
use App\Message\ProcessBrandAssistantBatchJobMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessBrandAssistantBatchJobHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'ai.agent.brand_research')] private readonly AgentInterface $agent,
    ) {
    }

    public function __invoke(ProcessBrandAssistantBatchJobMessage $message): void
    {
        $job = $this->entityManager->getRepository(BrandAssistantBatchJob::class)->find($message->getJobId());
        if (!$job instanceof BrandAssistantBatchJob) {
            return;
        }

        if (BrandAssistantBatchJob::STATUS_PROCESSING === $job->getStatus()) {
            return;
        }

        try {
            $job->setStatus(BrandAssistantBatchJob::STATUS_PROCESSING)
                ->setStartedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            foreach ($job->getItems() as $item) {
                if ($item instanceof BrandAssistantBatchJobItem && null === $item->getResult()) {
                    $this->processItem($job, $item);
                }
            }

            $job->setStatus(BrandAssistantBatchJob::STATUS_COMPLETED)
                ->setCompletedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $job->setStatus(BrandAssistantBatchJob::STATUS_FAILED)
                ->setErrorMessage($e->getMessage())
                ->setCompletedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }
    }

    private function processItem(BrandAssistantBatchJob $job, BrandAssistantBatchJobItem $item): void
    {
        try {
            $engine = $item->getVehicleEngine();
            $engineName = trim((string) $engine->getName());
            $prompt = $job->getPrompt() ?? '';

            $context = sprintf(
                "Fahrzeugmotor: %s\nHerstellerschlüsselnummer: %s\nGewicht: %s kg\n\nBitte antworte auf folgende Anfrage:\n\n%s",
                $engineName,
                $engine->getHsn() ?? 'N/A',
                $engine->getWeight() ?? 'N/A',
                $prompt
            );

            $messages = new MessageBag(
                Message::forSystem('Du bist ein Fahrzeug-Datenexperte. Extrahiere die angeforderten Informationen strukturiert.'),
                Message::ofUser($context)
            );

            // Standard-Ausgabelimit des Anthropic-Modells liegt bei 1000 Token und schneidet Analysen ab.
            $result = $this->agent->call($messages, ['max_tokens' => 4096]);
            $answer = (string) $result->getContent();

            $item->setResult($answer)
                ->setProcessedAt(new \DateTimeImmutable());

            $this->entityManager->persist($item);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            $item->setError($e->getMessage())
                ->setProcessedAt(new \DateTimeImmutable());
            $this->entityManager->persist($item);
            $this->entityManager->flush();
        }
    }
}
