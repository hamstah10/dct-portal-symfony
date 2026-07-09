<?php

declare(strict_types=1);

namespace App\Websites\No1tuningfiles\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    private const BUILD_DIR = '/public/assets/portals/no1tuningfiles/react_website/no1tuningfiles/static/';

    public function __construct(private readonly string $projectDir)
    {
    }

    #[Route('/{path}', name: 'no1_website_spa', requirements: ['path' => '.*'], priority: -1)]
    public function index(): Response
    {
        return $this->render('website/no1tuningfiles/spa.html.twig', [
            'spa_js' => $this->locateAsset('js/main', '.js'),
            'spa_css' => $this->locateAsset('css/main', '.css'),
        ]);
    }

    private function locateAsset(string $prefix, string $suffix): string
    {
        $dir = $this->projectDir . self::BUILD_DIR . dirname($prefix) . '/';
        $base = basename($prefix);

        if (!is_dir($dir)) {
            return '';
        }

        $files = scandir($dir);
        if ($files === false) {
            return '';
        }

        foreach ($files as $file) {
            if (str_starts_with($file, $base) && str_ends_with($file, $suffix) && !str_ends_with($file, '.map') && !str_ends_with($file, '.txt')) {
                return $file;
            }
        }

        return '';
    }
}
