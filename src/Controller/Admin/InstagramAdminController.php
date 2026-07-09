<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tuningfux\InstagramBundle\Store\InstagramAccountStoreInterface;

#[Route('/admin/instagram', name: 'admin_instagram_index', methods: ['GET'])]
class InstagramAdminController extends AbstractController
{
    public function __construct(
        private readonly InstagramAccountStoreInterface $store,
    ) {
    }

    public function __invoke(): Response
    {
        return $this->render('admin/instagram/index.html.twig', [
            'accounts' => $this->store->findAll(),
        ]);
    }
}
