<?php

namespace App\Portals\No1tuningfiles\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Entity\Kunde;
use App\Entity\Portal;
use App\Portals\No1tuningfiles\Form\FileUploadFormType;
use App\Portals\No1tuningfiles\Repository\PortalUploadJobRepository;
use App\Portals\No1tuningfiles\Security\No1tuningfilesPortalCustomerUser;
use App\Portals\No1tuningfiles\Service\FileUploadPortalService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FileUploadController extends AbstractController
{
    #[Route('/uploads', name: 'no1_portal_uploads', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        FileUploadPortalService $fileUploadPortalService,
        PortalUploadJobRepository $jobRepository,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response {
        $portalConfig = $portalRuntimeResolver->requirePortalModule(Portal::MODULE_UPLOADS, $request);
        $customer = $this->getCurrentCustomer();
        $form = $this->createForm(FileUploadFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile|null $upload */
            $upload = $form->get('upload')->getData();
            $notes = $form->get('notes')->getData();
            if ($upload instanceof UploadedFile) {
                $fileUploadPortalService->createUploadJob($customer, $upload, is_string($notes) ? $notes : null);
                $this->addFlash('success', 'Datei wurde hochgeladen.');

                return $this->redirectToRoute('no1_portal_uploads');
            }

            $this->addFlash('danger', 'Bitte waehle eine Datei aus.');
        }

        return $this->render('portal/no1tuningfiles/upload/index.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'form' => $form->createView(),
            'jobs' => $jobRepository->findRecentForCustomer($customer, 20),
        ]);
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof No1tuningfilesPortalCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }
}
