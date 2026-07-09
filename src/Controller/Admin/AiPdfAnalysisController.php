<?php

namespace App\Controller\Admin;

use App\Service\Ai\PdfAnalysisStorage;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Platform\Message\Content\Document;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AiPdfAnalysisController extends AbstractController
{
    private const UPLOAD_DIR = 'public/uploads/ai/pdf-analysis';
    private const DEFAULT_MODEL = 'gpt-5';

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly PdfAnalysisStorage $storage,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
    }

    #[AdminRoute(path: '/ai/pdf-analysis', name: 'ai_pdf_analysis', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST') && '1' === (string) $request->request->get('delete_run', '')) {
            return $this->deleteRun($request);
        }

        $model = (string) $request->request->get('model', self::DEFAULT_MODEL);
        $prompt = trim((string) $request->request->get('prompt', ''));
        $uploadedFile = $request->files->get('pdf');
        $pdfFile = null;
        $response = null;
        $error = null;

        $runs = $this->storage->listRuns();
        $activeRunId = (int) $request->query->get('run', 0);
        if ($activeRunId <= 0 && [] !== $runs) {
            $activeRunId = (int) $runs[0]['id'];
        }
        $activeRun = $activeRunId > 0 ? $this->storage->getRun($activeRunId) : null;

        if (!$request->isMethod('POST') && is_array($activeRun)) {
            $prompt = (string) ($activeRun['prompt'] ?? $prompt);
            $response = $activeRun['response'] ?? null;
            $error = $activeRun['error'] ?? null;
            $pdfFile = $activeRun['filePath'] ?? null;
        }

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_token', '');
            if (!$this->isCsrfTokenValid('admin_ai_pdf_analysis', $csrfToken)) {
                $this->addFlash('danger', 'Ungültiges CSRF-Token.');

                return $this->redirectToRoute('admin_ai_pdf_analysis');
            }

            if (null === $uploadedFile || '' === $prompt) {
                $error = 'Bitte eine PDF und einen Prompt angeben.';
            } else {
                try {
                    $savedPath = $this->saveUploadedFile($uploadedFile);
                    if (null === $savedPath) {
                        throw new \RuntimeException('Datei konnte nicht gespeichert werden.');
                    }

                    $runId = $this->storage->createRun($savedPath, $model, $prompt);

                    $absolutePath = $this->projectDir.'/public/'.ltrim($savedPath, '/');

                    try {
                        $document = Document::fromFile($absolutePath);
                        $messages = new MessageBag(
                            Message::ofUser($prompt, $document)
                        );
                    } catch (\Throwable $e) {
                        throw new \RuntimeException('PDF konnte nicht geladen werden: '.$e->getMessage());
                    }

                    $result = $this->platform->invoke($model, $messages);
                    $response = $result->asText();

                    $this->storage->updateRun($runId, $response);

                    return $this->redirectToRoute('admin_ai_pdf_analysis', ['run' => $runId]);
                } catch (\Throwable $exception) {
                    $error = $exception->getMessage();
                    if (isset($runId) && $runId > 0) {
                        $this->storage->updateRun($runId, null, $error);

                        return $this->redirectToRoute('admin_ai_pdf_analysis', ['run' => $runId]);
                    }
                }
            }
        }

        return $this->render('admin/ai_pdf_analysis/index.html.twig', [
            'model' => $model,
            'prompt' => $prompt,
            'pdfFile' => $pdfFile,
            'response' => $response,
            'runs' => $runs,
            'activeRunId' => $activeRunId,
            'error' => $error,
        ]);
    }

    private function deleteRun(Request $request): Response
    {
        $csrfToken = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('admin_ai_pdf_analysis', $csrfToken)) {
            $this->addFlash('danger', 'Ungültiges CSRF-Token.');

            return $this->redirectToRoute('admin_ai_pdf_analysis');
        }

        $id = (int) $request->request->get('runId', 0);
        if ($id <= 0) {
            return $this->redirectToRoute('admin_ai_pdf_analysis');
        }

        $run = $this->storage->getRun($id);
        if (is_array($run) && isset($run['filePath']) && is_string($run['filePath'])) {
            $absolute = $this->projectDir.'/public/'.ltrim($run['filePath'], '/');
            if (is_file($absolute)) {
                @unlink($absolute);
            }
        }

        $this->storage->deleteRun($id);

        return $this->redirectToRoute('admin_ai_pdf_analysis');
    }

    private function saveUploadedFile($file): ?string
    {
        if (null === $file) {
            return null;
        }

        $directory = $this->projectDir.'/'.self::UPLOAD_DIR;
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            return null;
        }

        $originalName = $file->getClientOriginalName();
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = sprintf('%s-%s.%s', date('YmdHis'), bin2hex(random_bytes(4)), $extension);
        $path = $directory.'/'.$filename;

        try {
            $file->move($directory, $filename);

            return '/uploads/ai/pdf-analysis/'.$filename;
        } catch (\Throwable) {
            return null;
        }
    }
}
