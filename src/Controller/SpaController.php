<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SpaAssetLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SpaController extends AbstractController
{
    public function __construct(private readonly SpaAssetLocator $assetLocator)
    {
    }

    #[Route('/{path}', name: 'spa_catchall', requirements: ['path' => '.*'], priority: -1)]
    public function index(): Response
    {
        return $this->render('spa.html.twig', [
            'spa_js' => $this->assetLocator->locate('index-', '.js'),
            'spa_css' => $this->assetLocator->locate('index-', '.css'),
        ]);
    }
}
