<?php

namespace App\Controller\Admin;

use App\Entity\ToolNewsItem;
use App\Repository\ToolNewsItemRepository;
use App\Repository\ToolProviderRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

final class ToolNewsControlController extends AbstractController
{
    private const MAX_LOG_LENGTH = 8000;

    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
        private readonly ToolProviderRepository $toolProviderRepository,
        private readonly ToolNewsItemRepository $toolNewsItemRepository,
    ) {
    }

    #[AdminRoute(path: '/ecu-intelligence/steuerung', name: 'tool_news_control', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $result = null;

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('admin_tool_news_control', $csrfToken)) {
                $this->addFlash('danger', 'Ungueltiges Formular-Token.');

                return $this->redirectToRoute('admin_tool_news_control');
            }

            $action = (string) $request->request->get('action', '');
            $result = match ($action) {
                'run' => $this->runScraper($request),
                'import' => $this->runImport($request),
                'analyze' => $this->runAnalyze($request),
                default => ['success' => false, 'output' => 'Unbekannte Aktion.'],
            };

            $this->addFlash(
                $result['success'] ? 'success' : 'danger',
                $result['success'] ? 'Befehl wurde erfolgreich ausgefuehrt.' : 'Befehl ist fehlgeschlagen.'
            );
        }

        return $this->render('admin/tool_news_control/index.html.twig', [
            'providers' => $this->toolProviderRepository->findBy([], ['name' => 'ASC']),
            'analyzableCount' => count($this->toolNewsItemRepository->findBy([
                'status' => [ToolNewsItem::STATUS_NEW, ToolNewsItem::STATUS_IMPORTED],
            ])),
            'result' => $result,
        ]);
    }

    /**
     * @return array{success: bool, output: string, command: string}
     */
    private function runScraper(Request $request): array
    {
        $provider = trim((string) $request->request->get('provider', ''));
        $year = trim((string) $request->request->get('year', ''));

        if ('' === $provider) {
            return ['success' => false, 'output' => 'Bitte einen Anbieter auswaehlen.', 'command' => ''];
        }

        $command = [self::phpBinary(), 'bin/console', 'app:tool-news:run', $provider];
        if ('' !== $year) {
            $command[] = '--year=' . $year;
        }

        return $this->runConsoleCommand($command);
    }

    /**
     * @return array{success: bool, output: string, command: string}
     */
    private function runImport(Request $request): array
    {
        $spider = trim((string) $request->request->get('import_spider', ''));
        if ('' === $spider) {
            return ['success' => false, 'output' => 'Bitte einen Anbieter fuer den Import auswaehlen.', 'command' => ''];
        }

        $file = sprintf('%s/scrapy-remote-scraper/output/tool_news/%s_latest.json', $this->projectDir, $spider);
        if (!is_file($file)) {
            return [
                'success' => false,
                'output' => sprintf('Datei nicht gefunden: %s (zuerst "Scraper starten" fuer diesen Anbieter ausfuehren).', $file),
                'command' => '',
            ];
        }

        return $this->runConsoleCommand([self::phpBinary(), 'bin/console', 'app:tool-news:import', $file]);
    }

    /**
     * @return array{success: bool, output: string, command: string}
     */
    private function runAnalyze(Request $request): array
    {
        $command = [self::phpBinary(), 'bin/console', 'app:tool-news:analyze', '--only-new'];

        $provider = trim((string) $request->request->get('analyze_provider', ''));
        if ('' !== $provider) {
            $command[] = '--provider=' . $provider;
        }

        return $this->runConsoleCommand($command);
    }

    /**
     * Resolves the PHP binary to use for spawning console commands.
     *
     * Plain "php" would resolve via $PATH, which on this server may point
     * to an outdated system PHP (8.1) instead of the phpenv-managed 8.4
     * that this application actually requires. PhpExecutableFinder instead
     * resolves from the currently running PHP's own binary directory.
     */
    private static function phpBinary(): string
    {
        return (new PhpExecutableFinder())->find() ?: 'php';
    }

    /**
     * @param list<string> $command
     *
     * @return array{success: bool, output: string, command: string}
     */
    private function runConsoleCommand(array $command): array
    {
        $process = new Process($command, $this->projectDir);
        $process->setTimeout(600);

        try {
            $process->run();
        } catch (\Throwable $exception) {
            return [
                'success' => false,
                'output' => $exception->getMessage(),
                'command' => implode(' ', $command),
            ];
        }

        $output = trim($process->getOutput() . "\n" . $process->getErrorOutput());
        if (mb_strlen($output) > self::MAX_LOG_LENGTH) {
            $output = '…' . mb_substr($output, -self::MAX_LOG_LENGTH);
        }

        return [
            'success' => $process->isSuccessful(),
            'output' => $output,
            'command' => implode(' ', $command),
        ];
    }
}
