<?php

namespace App\Controller\Admin;

use App\Repository\MarketingAdSizeRepository;
use App\Service\Ai\DalleImageStorage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\Base64Image;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\ImageResult;
use Symfony\AI\Platform\Bridge\OpenAi\DallE\UrlImage;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;

final class AiDalleController extends AbstractController
{
    private const DEFAULT_MODEL = 'dall-e-3';
    private const UPLOAD_DIR = 'public/uploads/ai/dalle';

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly HttpClientInterface $httpClient,
        private readonly DalleImageStorage $storage,
        private readonly MarketingAdSizeRepository $adSizeRepository,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    private function isModel2(string $model): bool
    {
        return 'dall-e-2' === $model;
    }

    #[AdminRoute(path: '/ai/dalle', name: 'ai_dalle', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST') && '1' === (string) $request->request->get('delete_run', '')) {
            return $this->deleteRun($request);
        }

        $model = (string) $request->request->get('model', self::DEFAULT_MODEL);
        $prompt = trim((string) $request->request->get('prompt', ''));
        $aspect = (string) $request->request->get('aspect', 'square');
        $size = $this->resolveSizeForModel($aspect, $model);
        $quality = (string) $request->request->get('quality', 'hd');
        $style = (string) $request->request->get('style', 'vivid');
        $format = (string) $request->request->get('format', 'url');
        $count = (int) $request->request->get('count', 1);
        if ($count < 1) {
            $count = 1;
        }
        if ($count > 10) {
            $count = 10;
        }

        $imageUrl = null;
        $imageData = null;
        $revisedPrompt = null;
        $error = null;
        $metadata = null;
        $imagePaths = [];

        $runs = $this->storage->listRuns();
        $activeRunId = (int) $request->query->get('run', 0);
        if ($activeRunId <= 0 && [] !== $runs) {
            $activeRunId = (int) $runs[0]['id'];
        }
        $activeRun = $activeRunId > 0 ? $this->storage->getRun($activeRunId) : null;

        if (!$request->isMethod('POST') && is_array($activeRun)) {
            $prompt = (string) ($activeRun['prompt'] ?? $prompt);
            $size = (string) ($activeRun['size'] ?? $size);
            $quality = (string) ($activeRun['quality'] ?? $quality);
            $style = (string) ($activeRun['style'] ?? $style);
            $format = (string) ($activeRun['format'] ?? $format);
            $aspect = (string) ($activeRun['aspectRatio'] ?? $aspect);
            $count = (int) ($activeRun['imageCount'] ?? $count);
            $imageUrl = $activeRun['imagePath'] ?? null;
            $imagePaths = $activeRun['imagePaths'] ?? [];
            $revisedPrompt = $activeRun['revisedPrompt'] ?? null;
            $metadata = $activeRun['metadata'] ?? null;
            $error = $activeRun['error'] ?? null;
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_dalle', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_dalle');
            }

            if ('' === $prompt) {
                $error = 'Bitte einen Prompt angeben.';
            } else {
                try {
                    $runId = $this->storage->createRun($prompt, $model, $size, $quality, $style, $format, $count, $aspect);
                    $imagePaths = [];
                    $imageUrls = [];

                    for ($i = 0; $i < $count; $i++) {
                        $options = [
                            'response_format' => $format,
                            'n' => 1,
                        ];

                        if (!$this->isModel2($model)) {
                            $options['quality'] = $quality;
                            $options['style'] = $style;
                        }

                        $options['size'] = $size;

                        $result = $this->platform->invoke($model, $prompt, $options)->getResult();

                        if (!$result instanceof ImageResult) {
                            throw new \RuntimeException('Unerwartetes Ergebnis vom DALL-E Modell.');
                        }

                        if (null === $revisedPrompt) {
                            $revisedPrompt = $result->revisedPrompt;
                        }

                        $images = $result->getContent();
                        $first = $images[0] ?? null;
                        if ($first instanceof UrlImage) {
                            $imageUrls[] = $first->url;
                        } elseif ($first instanceof Base64Image) {
                            $imageData = 'data:image/png;base64,'.$first->encodedImage;
                        }
                        $metadata = $result->getMetadata()->all();

                        $savedPath = $this->persistImage($first);
                        if (null === $savedPath) {
                            throw new \RuntimeException('Bild konnte nicht gespeichert werden.');
                        }

                        $imagePaths[] = $savedPath;
                    }

                    $imageUrl = $imagePaths[0] ?? null;
                    $this->storage->updateRun($runId, $imagePaths, $imageUrls, $revisedPrompt, $metadata);

                    return $this->redirectToRoute('admin_ai_dalle', ['run' => $runId]);
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                    if (isset($runId) && $runId > 0) {
                        $this->storage->updateRun($runId, $imagePaths ?? [], $imageUrls ?? [], $revisedPrompt, $metadata, $error);
                        return $this->redirectToRoute('admin_ai_dalle', ['run' => $runId]);
                    }
                }
            }
        }

        $adSizes = $this->adSizeRepository->findAll();
        $aspectOptions = $this->buildAspectOptions($adSizes);

        return $this->render('admin/ai_dalle/index.html.twig', [
            'model' => $model,
            'prompt' => $prompt,
            'size' => $size,
            'quality' => $quality,
            'style' => $style,
            'format' => $format,
            'count' => $count,
            'aspect' => $aspect,
            'imageUrl' => $imageUrl,
            'imageData' => $imageData,
            'imagePaths' => $imagePaths,
            'revisedPrompt' => $revisedPrompt,
            'metadata' => $metadata,
            'runs' => $runs,
            'activeRunId' => $activeRunId,
            'error' => $error,
            'isModel2' => $this->isModel2($model),
            'aspectOptions' => $aspectOptions,
        ]);
    }

    #[AdminRoute(path: '/ai/dalle/download/{id}', name: 'ai_dalle_download', options: ['methods' => ['GET']])]
    public function download(int $id): Response
    {
        $run = $this->storage->getRun($id);
        if (!is_array($run)) {
            $this->addFlash('danger', 'Bild nicht gefunden.');
            return $this->redirectToRoute('admin_ai_dalle');
        }

        $path = $run['imagePath'] ?? null;
        if (!is_string($path) || '' === trim($path)) {
            $this->addFlash('danger', 'Kein Bild vorhanden.');
            return $this->redirectToRoute('admin_ai_dalle', ['run' => $id]);
        }

        $absolute = $this->projectDir.'/public/'.ltrim($path, '/');
        if (!is_file($absolute)) {
            $this->addFlash('danger', 'Datei nicht gefunden.');
            return $this->redirectToRoute('admin_ai_dalle', ['run' => $id]);
        }

        $filename = basename($absolute);
        $response = new Response(file_get_contents($absolute) ?: '');
        $response->headers->set('Content-Type', $this->guessMimeType($absolute));
        $response->headers->set('Content-Disposition', (new ResponseHeaderBag())->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        ));

        return $response;
    }

    private function deleteRun(Request $request): Response
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_ai_dalle', $csrfToken)) {
            $this->addFlash('danger', 'Ungueltiges CSRF-Token.');

            return $this->redirectToRoute('admin_ai_dalle');
        }

        $id = (int) $request->request->get('runId', 0);
        if ($id <= 0) {
            return $this->redirectToRoute('admin_ai_dalle');
        }

        $run = $this->storage->getRun($id);
        if (is_array($run) && isset($run['imagePath']) && is_string($run['imagePath'])) {
            $absolute = $this->projectDir.'/public/'.ltrim($run['imagePath'], '/');
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        $this->storage->deleteRun($id);

        return $this->redirectToRoute('admin_ai_dalle');
    }

    private function persistImage(Base64Image|UrlImage|null $image): ?string
    {
        if (null === $image) {
            return null;
        }

        $directory = $this->projectDir.'/'.self::UPLOAD_DIR;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $extension = 'png';
        $content = null;
        $sourceUrl = null;

        if ($image instanceof UrlImage) {
            $sourceUrl = $image->url;
            $response = $this->httpClient->request('GET', $sourceUrl);
            $contentType = $response->getHeaders(false)['content-type'][0] ?? null;
            if (is_string($contentType)) {
                if (str_contains($contentType, 'jpeg')) {
                    $extension = 'jpg';
                } elseif (str_contains($contentType, 'png')) {
                    $extension = 'png';
                }
            }
            $content = $response->getContent();
        } elseif ($image instanceof Base64Image) {
            $content = base64_decode($image->encodedImage, true);
            if (false === $content) {
                return null;
            }
        }

        if (null === $content || '' === $content) {
            return null;
        }

        $filename = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extension);
        $path = $directory.'/'.$filename;
        if (false === file_put_contents($path, $content)) {
            return null;
        }

        return '/uploads/ai/dalle/'.$filename;
    }

    private function resolveSizeForModel(string $aspect, string $model): string
    {
        $aspect = strtolower(trim($aspect));

        // Check default options first
        $defaultSizes = [
            'square' => '1024x1024',
            'landscape' => '1792x1024',
            'portrait' => '1024x1792',
            'instagram post' => '1024x1024',
            'instagram story' => '1024x1792',
            'tiktok' => '1024x1792',
            'youtube thumbnail' => '1792x1024',
            'pinterest' => '1024x1536',
            'facebook' => '1792x952',
            'linkedin' => '1792x952',
        ];

        if (isset($defaultSizes[$aspect])) {
            $size = $defaultSizes[$aspect];
            if ($this->isModel2($model)) {
                [$width, $height] = explode('x', $size);
                return $this->scaleDownToModel2((int) $width, (int) $height);
            }
            return $size;
        }

        // Check database sizes
        $adSizes = $this->adSizeRepository->findAll();
        foreach ($adSizes as $adSize) {
            if (strtolower($adSize->getTitle() ?? '') === $aspect) {
                $width = $adSize->getWidth() ?? 1024;
                $height = $adSize->getHeight() ?? 1024;

                if ($this->isModel2($model)) {
                    return $this->scaleDownToModel2($width, $height);
                }

                return sprintf('%dx%d', $width, $height);
            }
        }

        return '1024x1024';
    }

    private function scaleDownToModel2(int $width, int $height): string
    {
        $ratio = $width / $height;

        if ($ratio > 1) {
            $width = 1024;
            $height = (int) ($width / $ratio);
        } else {
            $height = 1024;
            $width = (int) ($height * $ratio);
        }

        return sprintf('%dx%d', $width, $height);
    }

    /**
     * @param array<object> $adSizes
     *
     * @return array<string, array<int, array<string, string>>>
     */
    private function buildAspectOptions(array $adSizes): array
    {
        $defaultOptions = [
            'Basis' => [
                ['value' => 'Square', 'label' => 'Square (1:1) - 1024x1024'],
                ['value' => 'Landscape', 'label' => 'Landscape (16:9) - 1792x1024'],
                ['value' => 'Portrait', 'label' => 'Portrait (9:16) - 1024x1792'],
            ],
            'Social Media' => [
                ['value' => 'Instagram Post', 'label' => 'Instagram Post (1:1) - 1024x1024'],
                ['value' => 'Instagram Story', 'label' => 'Instagram Story (9:16) - 1024x1792'],
                ['value' => 'TikTok', 'label' => 'TikTok (9:16) - 1024x1792'],
                ['value' => 'YouTube Thumbnail', 'label' => 'YouTube Thumbnail (16:9) - 1792x1024'],
                ['value' => 'Pinterest', 'label' => 'Pinterest (2:3) - 1024x1536'],
                ['value' => 'Facebook', 'label' => 'Facebook (1.9:1) - 1792x952'],
                ['value' => 'LinkedIn', 'label' => 'LinkedIn (1.9:1) - 1792x952'],
            ],
        ];

        $options = $defaultOptions;

        foreach ($adSizes as $adSize) {
            $title = $adSize->getTitle() ?? '';
            if ('' === $title) {
                continue;
            }

            $category = $adSize->getCategory()?->getTitle() ?? 'Sonstiges';

            if (!isset($options[$category])) {
                $options[$category] = [];
            }

            $width = $adSize->getWidth() ?? 0;
            $height = $adSize->getHeight() ?? 0;
            $dimensions = $width > 0 && $height > 0 ? sprintf(' (%dx%d)', $width, $height) : '';

            $options[$category][] = [
                'value' => $title,
                'label' => $title.$dimensions,
            ];
        }

        return $options;
    }

    private function guessMimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
