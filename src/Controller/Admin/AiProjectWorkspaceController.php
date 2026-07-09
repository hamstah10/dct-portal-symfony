<?php

namespace App\Controller\Admin;

use App\Entity\AiProject;
use App\Entity\AiProjectResource;
use App\Entity\MediaAsset;
use App\Repository\AiProjectResourceRepository;
use App\Repository\MediaAssetRepository;
use App\Service\Ai\ChatSessionStorage;
use App\Service\Ai\DalleImageStorage;
use App\Service\Ai\Project\AiProjectAgentRunStorage;
use App\Service\Ai\Project\AiProjectContextBuilder;
use App\Service\Ai\Project\AiProjectKnowledgeManager;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\Base64Image;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\ImageResult;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\UrlImage;
use Symfony\AI\Platform\Message\AssistantMessage;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\MessageInterface;
use Symfony\AI\Platform\Message\SystemMessage;
use Symfony\AI\Platform\Message\UserMessage;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AiProjectWorkspaceController extends AbstractController
{
    private const DEFAULT_IMAGE_MODEL = 'dall-e-3';
    private const IMAGE_UPLOAD_DIR = 'public/uploads/ai/dalle';

    public function __construct(
        private readonly MediaAssetRepository $mediaAssetRepository,
        private readonly AiProjectResourceRepository $resourceRepository,
        private readonly AiProjectKnowledgeManager $knowledgeManager,
        private readonly AiProjectContextBuilder $contextBuilder,
        private readonly ChatSessionStorage $chatStorage,
        private readonly AiProjectAgentRunStorage $agentRunStorage,
        private readonly DalleImageStorage $dalleImageStorage,
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
        #[Autowire(service: 'ai.agent.default')] private readonly AgentInterface $agent,
        private readonly PlatformInterface $platform,
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/ai/project/{id}/workspace', name: 'ai_project_workspace', options: ['requirements' => ['id' => '\d+'], 'methods' => ['GET', 'POST']])]
    public function index(Request $request, AiProject $project): Response
    {
        if ($request->isMethod('POST')) {
            return $this->handlePost($request, $project);
        }

        $resources = $this->resourceRepository->findByProject($project);
        $knowledgeStats = $this->buildKnowledgeStats($resources);

        $chatSessions = $this->chatStorage->listProjectSessions((int) $project->getId());
        $activeChatId = $this->resolveActiveChatId($request, $project, $chatSessions);
        $messages = $activeChatId > 0
            ? $this->buildMessageList($this->chatStorage->loadMessageBag($activeChatId))
            : [];

        $agentRuns = $this->agentRunStorage->listRuns((int) $project->getId());
        $imageRuns = $this->dalleImageStorage->listProjectRuns((int) $project->getId());

        return $this->render('admin/ai_project/workspace.html.twig', [
            'project' => $project,
            'resources' => $resources,
            'knowledgeStats' => $knowledgeStats,
            'attachableMediaAssets' => $this->mediaAssetRepository->findLatestForProjectPicker(),
            'chatSessions' => $chatSessions,
            'activeChatId' => $activeChatId,
            'messages' => $messages,
            'agentRuns' => $agentRuns,
            'imageRuns' => $imageRuns,
            'workspaceCsrfTokenId' => $this->buildCsrfTokenId($project),
        ]);
    }

    private function handlePost(Request $request, AiProject $project): Response
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid($this->buildCsrfTokenId($project), $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToWorkspace($project);
        }

        $action = trim((string) $request->request->get('action', ''));

        try {
            $response = match ($action) {
                'attach_media' => $this->handleAttachMedia($request, $project),
                'upload_resource' => $this->handleUploadResource($request, $project),
                'reindex_resource' => $this->handleReindexResource($request, $project),
                'delete_resource' => $this->handleDeleteResource($request, $project),
                'new_chat' => $this->handleNewChat($project),
                'delete_chat' => $this->handleDeleteChat($request, $project),
                'clear_chat' => $this->handleClearChat($request, $project),
                'send_chat' => $this->handleSendChat($request, $project),
                'run_agent' => $this->handleRunAgent($request, $project),
                'delete_agent_run' => $this->handleDeleteAgentRun($request, $project),
                'generate_image' => $this->handleGenerateImage($request, $project),
                'delete_image' => $this->handleDeleteImage($request, $project),
                default => $this->redirectToWorkspace($project),
            };

            $this->publishWorkspaceNotification($project, $action);

            return $response;
        } catch (\Throwable $exception) {
            $this->addFlash('danger', $exception->getMessage());
            $this->publishWorkspaceNotification($project, $action, $exception->getMessage(), true);

            return $this->redirectToWorkspace($project);
        }
    }

    private function handleAttachMedia(Request $request, AiProject $project): Response
    {
        $mediaAssetId = (int) $request->request->get('mediaAssetId', 0);
        $mediaAsset = $this->mediaAssetRepository->find($mediaAssetId);
        if (!$mediaAsset instanceof MediaAsset) {
            throw new \RuntimeException('Bitte eine gueltige Datei aus dem Media Manager auswaehlen.');
        }

        $this->knowledgeManager->attachExistingMediaAsset($project, $mediaAsset);
        $this->addFlash('success', 'Datei dem Projekt hinzugefuegt.');

        return $this->redirectToWorkspace($project, fragment: 'knowledge');
    }

    private function handleUploadResource(Request $request, AiProject $project): Response
    {
        $uploadedFile = $request->files->get('uploadFile');
        if (!$uploadedFile instanceof UploadedFile) {
            throw new \RuntimeException('Bitte eine Datei zum Upload auswaehlen.');
        }

        $this->knowledgeManager->uploadAndAttach($project, $uploadedFile);
        $this->addFlash('success', 'Datei hochgeladen, dem Projekt zugeordnet und indexiert.');

        return $this->redirectToWorkspace($project, fragment: 'knowledge');
    }

    private function handleReindexResource(Request $request, AiProject $project): Response
    {
        $resource = $this->resolveProjectResource($project, (int) $request->request->get('resourceId', 0));
        $this->knowledgeManager->reindexResource($resource);
        $this->addFlash('success', 'Ressource neu verarbeitet.');

        return $this->redirectToWorkspace($project, fragment: 'knowledge');
    }

    private function handleDeleteResource(Request $request, AiProject $project): Response
    {
        $resource = $this->resolveProjectResource($project, (int) $request->request->get('resourceId', 0));
        $this->knowledgeManager->removeResource($resource);
        $this->addFlash('success', 'Ressource aus dem Projekt entfernt.');

        return $this->redirectToWorkspace($project, fragment: 'knowledge');
    }

    private function handleNewChat(AiProject $project): Response
    {
        $chatId = $this->chatStorage->createProjectSession((int) $project->getId());

        return $this->redirectToWorkspace($project, ['chat' => $chatId], 'chat');
    }

    private function handleDeleteChat(Request $request, AiProject $project): Response
    {
        $chatId = (int) $request->request->get('chatId', 0);
        if (null === $this->chatStorage->getProjectSession((int) $project->getId(), $chatId)) {
            throw new \RuntimeException('Chat nicht gefunden.');
        }

        $this->chatStorage->deleteProjectSession((int) $project->getId(), $chatId);
        $this->addFlash('success', 'Chat entfernt.');

        return $this->redirectToWorkspace($project, fragment: 'chat');
    }

    private function handleClearChat(Request $request, AiProject $project): Response
    {
        $chatId = (int) $request->request->get('chatId', 0);
        if (null === $this->chatStorage->getProjectSession((int) $project->getId(), $chatId)) {
            throw new \RuntimeException('Chat nicht gefunden.');
        }

        $this->chatStorage->resetSession($chatId);
        $this->addFlash('success', 'Chat-Verlauf geleert.');

        return $this->redirectToWorkspace($project, ['chat' => $chatId], 'chat');
    }

    private function handleSendChat(Request $request, AiProject $project): Response
    {
        $userMessage = trim((string) $request->request->get('message', ''));
        if ('' === $userMessage) {
            throw new \RuntimeException('Bitte eine Nachricht eingeben.');
        }

        $chatId = (int) $request->request->get('chatId', 0);
        if ($chatId > 0 && null === $this->chatStorage->getProjectSession((int) $project->getId(), $chatId)) {
            throw new \RuntimeException('Chat nicht gefunden.');
        }

        if ($chatId <= 0) {
            $chatId = $this->chatStorage->createProjectSession((int) $project->getId());
        }

        $history = $this->chatStorage->loadMessageBag($chatId);
        $conversation = new MessageBag();

        $systemPrompt = $this->buildProjectSystemPrompt($project, $userMessage);
        if ('' !== $systemPrompt) {
            $conversation->add(Message::forSystem($systemPrompt));
        }

        foreach ($history as $message) {
            if ($message instanceof MessageInterface) {
                $conversation->add($message);
            }
        }

        $conversation->add(Message::ofUser($userMessage));

        $result = $this->agent->call($conversation);
        $response = method_exists($result, 'getContent') ? (string) $result->getContent() : (string) $result;

        $history->add(Message::ofUser($userMessage));
        $history->add(new AssistantMessage($response));

        $this->chatStorage->saveMessageBag($chatId, $history);
        $this->chatStorage->updateTitleIfEmpty($chatId, $userMessage);

        return $this->redirectToWorkspace($project, ['chat' => $chatId], 'chat');
    }

    private function handleRunAgent(Request $request, AiProject $project): Response
    {
        $prompt = trim((string) $request->request->get('prompt', ''));
        if ('' === $prompt) {
            throw new \RuntimeException('Bitte eine Agent-Aufgabe eingeben.');
        }

        $systemPrompt = trim((string) $request->request->get('systemPrompt', ''));
        if ('' === $systemPrompt) {
            $systemPrompt = trim((string) ($project->getSystemPrompt() ?? ''));
        }

        $runId = $this->agentRunStorage->createRun((int) $project->getId(), $systemPrompt, $prompt);

        try {
            $messages = new MessageBag();
            $combinedSystemPrompt = $this->buildProjectSystemPrompt($project, $prompt, $systemPrompt);
            if ('' !== $combinedSystemPrompt) {
                $messages->add(Message::forSystem($combinedSystemPrompt));
            }
            $messages->add(Message::ofUser($prompt));

            $result = $this->agent->call($messages);
            $response = method_exists($result, 'getContent') ? (string) $result->getContent() : (string) $result;
            $this->agentRunStorage->updateRun($runId, $response, $result->getMetadata()->all());

            $this->addFlash('success', 'Agent-Lauf abgeschlossen.');
        } catch (\Throwable $exception) {
            $this->agentRunStorage->updateRun($runId, null, null, $exception->getMessage());
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToWorkspace($project, ['agentRun' => $runId], 'agent');
    }

    private function handleDeleteAgentRun(Request $request, AiProject $project): Response
    {
        $runId = (int) $request->request->get('agentRunId', 0);
        $run = $this->agentRunStorage->getRun($runId);
        if (!is_array($run) || (int) ($run['projectId'] ?? 0) !== (int) $project->getId()) {
            throw new \RuntimeException('Agent-Lauf nicht gefunden.');
        }

        $this->agentRunStorage->deleteRun((int) $project->getId(), $runId);
        $this->addFlash('success', 'Agent-Lauf geloescht.');

        return $this->redirectToWorkspace($project, fragment: 'agent');
    }

    private function handleGenerateImage(Request $request, AiProject $project): Response
    {
        $prompt = trim((string) $request->request->get('imagePrompt', ''));
        if ('' === $prompt) {
            throw new \RuntimeException('Bitte einen Bild-Prompt eingeben.');
        }

        $aspect = strtolower(trim((string) $request->request->get('aspect', 'square')));
        $size = $this->resolveImageSize($aspect);
        $quality = strtolower(trim((string) $request->request->get('quality', 'hd')));
        $style = strtolower(trim((string) $request->request->get('style', 'vivid')));
        $format = strtolower(trim((string) $request->request->get('format', 'url')));
        $count = max(1, min(4, (int) $request->request->get('count', 1)));

        $runId = $this->dalleImageStorage->createProjectRun(
            (int) $project->getId(),
            $prompt,
            self::DEFAULT_IMAGE_MODEL,
            $size,
            $quality,
            $style,
            $format,
            $count,
            $aspect
        );

        $imagePaths = [];
        $imageUrls = [];
        $revisedPrompt = null;
        $metadata = null;

        try {
            $effectivePrompt = $this->buildImagePrompt($project, $prompt);

            for ($i = 0; $i < $count; ++$i) {
                $result = $this->platform->invoke(self::DEFAULT_IMAGE_MODEL, $effectivePrompt, [
                    'response_format' => $format,
                    'quality' => $quality,
                    'size' => $size,
                    'style' => $style,
                ])->getResult();

                if (!$result instanceof ImageResult) {
                    throw new \RuntimeException('Unerwartetes Ergebnis vom DALL-E Modell.');
                }

                if (null === $revisedPrompt) {
                    $revisedPrompt = $result->revisedPrompt;
                }

                $first = $result->getContent()[0] ?? null;
                if ($first instanceof UrlImage) {
                    $imageUrls[] = $first->url;
                }

                $savedPath = $this->persistImage($first);
                if (null === $savedPath) {
                    throw new \RuntimeException('Bild konnte nicht gespeichert werden.');
                }

                $imagePaths[] = $savedPath;
                $metadata = $result->getMetadata()->all();
            }

            $this->dalleImageStorage->updateRun($runId, $imagePaths, $imageUrls, $revisedPrompt, $metadata);
            $this->addFlash('success', 'Projektbilder erstellt.');
        } catch (\Throwable $exception) {
            $this->dalleImageStorage->updateRun($runId, $imagePaths, $imageUrls, $revisedPrompt, $metadata, $exception->getMessage());
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToWorkspace($project, fragment: 'images');
    }

    private function handleDeleteImage(Request $request, AiProject $project): Response
    {
        $runId = (int) $request->request->get('imageRunId', 0);
        $run = $this->dalleImageStorage->getProjectRun((int) $project->getId(), $runId);
        if (!is_array($run)) {
            throw new \RuntimeException('Bildlauf nicht gefunden.');
        }

        $paths = $run['imagePaths'] ?? [];
        if ([] === $paths && isset($run['imagePath']) && is_string($run['imagePath']) && '' !== trim($run['imagePath'])) {
            $paths = [$run['imagePath']];
        }

        foreach ($paths as $path) {
            if (!is_string($path) || '' === trim($path)) {
                continue;
            }

            $absolute = $this->projectDir.'/public/'.ltrim($path, '/');
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        $this->dalleImageStorage->deleteProjectRun((int) $project->getId(), $runId);
        $this->addFlash('success', 'Bildlauf geloescht.');

        return $this->redirectToWorkspace($project, fragment: 'images');
    }

    private function resolveProjectResource(AiProject $project, int $resourceId): AiProjectResource
    {
        if ($resourceId <= 0) {
            throw new \RuntimeException('Projekt-Ressource nicht gefunden.');
        }

        $resource = $this->resourceRepository->find($resourceId);
        if (!$resource instanceof AiProjectResource || $resource->getProject()?->getId() !== $project->getId()) {
            throw new \RuntimeException('Projekt-Ressource nicht gefunden.');
        }

        return $resource;
    }

    /**
     * @param list<array{id:int}> $chatSessions
     */
    private function resolveActiveChatId(Request $request, AiProject $project, array $chatSessions): int
    {
        $chatId = (int) $request->query->get('chat', 0);
        if ($chatId > 0 && null !== $this->chatStorage->getProjectSession((int) $project->getId(), $chatId)) {
            return $chatId;
        }

        $first = $chatSessions[0]['id'] ?? 0;

        return \is_int($first) ? $first : 0;
    }

    /**
     * @param list<AiProjectResource> $resources
     *
     * @return array{total:int,indexed:int,pending:int,unsupported:int,error:int,percent:int}
     */
    private function buildKnowledgeStats(array $resources): array
    {
        $stats = [
            'total' => \count($resources),
            'indexed' => 0,
            'pending' => 0,
            'unsupported' => 0,
            'error' => 0,
            'percent' => 0,
        ];

        foreach ($resources as $resource) {
            match ($resource->getIndexingStatus()) {
                AiProjectResource::STATUS_INDEXED => ++$stats['indexed'],
                AiProjectResource::STATUS_UNSUPPORTED => ++$stats['unsupported'],
                AiProjectResource::STATUS_ERROR => ++$stats['error'],
                default => ++$stats['pending'],
            };
        }

        if ($stats['total'] > 0) {
            $stats['percent'] = (int) round(($stats['indexed'] / $stats['total']) * 100);
        }

        return $stats;
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function buildMessageList(MessageBag $bag): array
    {
        $list = [];

        foreach ($bag as $message) {
            if (!$message instanceof MessageInterface) {
                continue;
            }

            $content = $this->extractMessageContent($message);
            if (null === $content || '' === trim($content)) {
                continue;
            }

            $list[] = [
                'role' => $message->getRole()->value,
                'content' => trim($content),
            ];
        }

        return $list;
    }

    private function extractMessageContent(MessageInterface $message): ?string
    {
        if ($message instanceof UserMessage) {
            return $message->asText();
        }

        if ($message instanceof AssistantMessage) {
            return $message->getContent();
        }

        if ($message instanceof SystemMessage) {
            return (string) $message->getContent();
        }

        return null;
    }

    private function buildProjectSystemPrompt(AiProject $project, string $query, ?string $basePrompt = null): string
    {
        $parts = [
            'Du arbeitest innerhalb eines thematischen Projekt-Workspaces. Antworte praezise auf Deutsch und nutze Projektwissen nur, wenn es zur Frage passt.',
        ];

        $basePrompt = trim((string) ($basePrompt ?? $project->getSystemPrompt() ?? ''));
        if ('' !== $basePrompt) {
            $parts[] = $basePrompt;
        }

        $context = $this->contextBuilder->buildContext((int) $project->getId(), $query, 5);
        if ('' !== $context) {
            $parts[] = "Projektwissen:\n".$context;
        }

        return implode("\n\n", $parts);
    }

    private function buildImagePrompt(AiProject $project, string $prompt): string
    {
        $parts = [sprintf('Projekt: %s', $project->getName())];

        $description = trim((string) ($project->getDescription() ?? ''));
        if ('' !== $description) {
            $parts[] = $description;
        }

        $parts[] = 'Bildauftrag: '.$prompt;

        return implode("\n\n", $parts);
    }

    private function persistImage(Base64Image|UrlImage|null $image): ?string
    {
        if (null === $image) {
            return null;
        }

        $directory = $this->projectDir.'/'.self::IMAGE_UPLOAD_DIR;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $extension = 'png';
        $content = null;

        if ($image instanceof UrlImage) {
            $response = $this->httpClient->request('GET', $image->url);
            $contentType = $response->getHeaders(false)['content-type'][0] ?? null;
            if (is_string($contentType) && str_contains($contentType, 'jpeg')) {
                $extension = 'jpg';
            }
            $content = $response->getContent();
        }

        if ($image instanceof Base64Image) {
            $content = base64_decode($image->encodedImage, true);
            if (false === $content) {
                return null;
            }
        }

        if (!is_string($content) || '' === $content) {
            return null;
        }

        $filename = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extension);
        $path = $directory.'/'.$filename;
        if (false === file_put_contents($path, $content)) {
            return null;
        }

        return '/uploads/ai/dalle/'.$filename;
    }

    private function resolveImageSize(string $aspect): string
    {
        return match ($aspect) {
            '16:9', '16x9', 'wide', 'landscape' => '1792x1024',
            default => '1024x1024',
        };
    }

    private function buildCsrfTokenId(AiProject $project): string
    {
        return sprintf('admin_ai_project_workspace_%d', (int) $project->getId());
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function redirectToWorkspace(AiProject $project, array $parameters = [], ?string $fragment = null): Response
    {
        $url = $this->generateUrl('admin_ai_project_workspace', array_merge(['id' => $project->getId()], $parameters));
        if (null !== $fragment && '' !== trim($fragment)) {
            $url .= '#'.trim($fragment);
        }

        return $this->redirect($url);
    }

    private function publishWorkspaceNotification(AiProject $project, string $action, ?string $body = null, bool $isError = false): void
    {
        $summary = match ($action) {
            'attach_media' => 'AI-Projektdatei uebernommen',
            'upload_resource' => 'AI-Projektdatei hochgeladen',
            'reindex_resource' => 'AI-Projektdatei neu indexiert',
            'delete_resource' => 'AI-Projektdatei entfernt',
            'new_chat' => 'AI-Projekt-Chat erstellt',
            'delete_chat' => 'AI-Projekt-Chat geloescht',
            'clear_chat' => 'AI-Projekt-Chat geleert',
            'send_chat' => 'AI-Projekt-Chat beantwortet',
            'run_agent' => 'AI-Agent-Lauf abgeschlossen',
            'delete_agent_run' => 'AI-Agent-Lauf geloescht',
            'generate_image' => 'AI-Projektbild erstellt',
            'delete_image' => 'AI-Projektbild geloescht',
            default => '',
        };

        if ('' === $summary) {
            return;
        }

        if ($isError) {
            $summary = 'AI-Projekt-Aktion fehlgeschlagen';
        }

        $topics = array_merge(
            $this->portalMercureTopics->adminNotifications(),
            [$this->portalMercureTopics->aiProject((int) $project->getId())],
        );

        $this->portalRealtimePublisher->publish($topics, $summary, [
            'body' => $body ?: sprintf('Projekt %s', $project->getName()),
            'tag' => sprintf('ai-project-%d-%s', (int) $project->getId(), $action ?: 'action'),
        ]);
    }
}
