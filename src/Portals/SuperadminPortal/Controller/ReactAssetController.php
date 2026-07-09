<?php

declare(strict_types=1);

namespace App\Portals\SuperadminPortal\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

final class ReactAssetController extends AbstractController
{
    #[Route('/_portal-react/superadmin-portal/{path}', name: 'superadmin_portal_react_asset', requirements: ['path' => '.+'], methods: ['GET'])]
    public function __invoke(string $path): BinaryFileResponse
    {
        if (str_contains($path, '..')) {
            throw $this->createNotFoundException();
        }

        $projectDir = (string) $this->getParameter('kernel.project_dir');
        $normalizedPath = ltrim($path, '/');
        $candidatePaths = [
            $projectDir . '/public/assets/portals/superadmin-portal/react/' . $normalizedPath,
            $projectDir . '/public/build/superadmin-portal/' . $normalizedPath,
        ];

        $assetPath = null;

        foreach ($candidatePaths as $candidatePath) {
            if (is_file($candidatePath)) {
                $assetPath = $candidatePath;
                break;
            }
        }

        if ($assetPath === null) {
            throw $this->createNotFoundException(sprintf('React asset "%s" wurde nicht gefunden.', $path));
        }

        $response = new BinaryFileResponse($assetPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}
