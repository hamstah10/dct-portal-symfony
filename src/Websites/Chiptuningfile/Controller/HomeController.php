<?php

namespace App\Websites\Chiptuningfile\Controller;

use App\Service\SpaAssetLocator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(private readonly SpaAssetLocator $assetLocator)
    {
    }

    #[Route('/{path}', name: 'cpt_website_spa', requirements: ['path' => '.*'], priority: -1)]
    public function index(): Response
    {
        return $this->render('spa.html.twig', [
            'spa_js' => $this->assetLocator->locate('index-', '.js'),
            'spa_css' => $this->assetLocator->locate('index-', '.css'),
        ]);
    }
}
