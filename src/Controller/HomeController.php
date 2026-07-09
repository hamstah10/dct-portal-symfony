<?php
/*
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('website/home.html.twig', [
            'homeCssVersion' => $this->getHomeCssVersion(),
        ]);
    }

    private function getHomeCssVersion(): string
    {
        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $path = $projectDir . '/public/styles/home.css';

        if (!is_file($path)) {
            return (string) time();
        }

        $hash = hash_file('sha1', $path);
        if (false === $hash) {
            $mtime = @filemtime($path);

            return false === $mtime ? (string) time() : (string) $mtime;
        }

        return substr($hash, 0, 12);
    }
}
*/


declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Catch-all route that serves the React SPA.
     * Place this controller last in routing priority so API routes take precedence.
     */
    #[Route('/{path}', name: 'spa', requirements: ['path' => '.*'], priority: -1)]
    public function index(): Response
    {
        return $this->render('spa.html.twig');
    }
}
