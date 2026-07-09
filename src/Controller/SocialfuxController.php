<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/socialfux')]
final class SocialfuxController extends AbstractController
{
    #[Route('', name: 'socialfux_app', methods: ['GET'])]
    #[Route('/{reactRoute}', name: 'socialfux_app_route', requirements: ['reactRoute' => '.+'], methods: ['GET'])]
    public function app(): Response
    {
        $indexFile = $this->getParameter('kernel.project_dir') . '/public/assets/portals/socialfux/react/index.html';
        if (!file_exists($indexFile)) {
            throw $this->createNotFoundException('SocialFux app not built yet.');
        }

        return new Response(
            file_get_contents($indexFile),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
