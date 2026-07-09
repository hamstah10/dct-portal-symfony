<?php

namespace App\Portals\Chiptuningfile\Controller;

use App\Brand\Common\Context\BrandContextResolver;
use App\Brand\Common\Portal\PortalRuntimeResolver;
use App\Controller\Api\ApiControllerTrait;
use App\Entity\ApiToken;
use App\Entity\Kunde;
use App\Entity\Portal;
use App\Repository\ApiTokenRepository;
use App\Service\Api\CustomerApiPayloadService;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Portals\Chiptuningfile\Security\PortalCustomerUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private readonly CustomerApiPayloadService $customerApiPayloadService,
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
    }

    #[Route('/profil', name: 'cpt_portal_profile', methods: ['GET'])]
    public function edit(
        Request $request,
        BrandContextResolver $brandContextResolver,
        PortalRuntimeResolver $portalRuntimeResolver,
    ): Response
    {
        $portalConfig = $portalRuntimeResolver->requirePortalModule(Portal::MODULE_PROFILE, $request);
        $customer = $this->getCurrentCustomer();

        return $this->render('portal/chiptuningfile/profile/edit.html.twig', [
            'brandContext' => $brandContextResolver->resolve(),
            'portalConfig' => $portalConfig,
            'reactBootstrap' => [
                'session' => [
                    'customerId' => $customer->getId(),
                    'credits' => (int) $customer->getCredits(),
                ],
                'portalDesign' => $portalConfig->getResolvedDesign(),
                'profile' => $this->buildProfileResponse($customer),
            ],
        ]);
    }

    #[Route('/profil/api', name: 'cpt_portal_profile_api', methods: ['GET', 'PATCH'])]
    public function api(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $customer = $this->getCurrentCustomer();

        if ($request->isMethod('GET')) {
            return $this->json($this->buildProfileResponse($customer));
        }

        try {
            $payload = $this->readJsonPayload($request);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $this->customerApiPayloadService->applyPayload($customer, $this->mapProfilePayloadToCustomerPayload($payload), true);

        try {
            $entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            return $this->json(['message' => 'E-Mail existiert bereits.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json($this->buildProfileResponse($customer));
    }

    private function getCurrentCustomer(): Kunde
    {
        $user = $this->getUser();
        if (!$user instanceof PortalCustomerUser) {
            throw $this->createAccessDeniedException();
        }

        return $user->getCustomer();
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProfileResponse(Kunde $customer): array
    {
        $normalized = $this->customerApiPayloadService->normalizeCustomer($customer);
        $apiToken = $this->apiTokenRepository->findLatestActiveForCustomer($customer);

        return [
            ...$normalized,
            'companyName' => $customer->getFirmenname(),
            'apiKey' => $apiToken instanceof ApiToken ? $apiToken->getApiToken() : null,
            'website' => $customer->getWebsite() ?? $customer->getFirma()?->getWebsite(),
            'hasDyno' => $customer->hasDyno(),
            'tuningTools' => $customer->getTuningTools(),
            'diagnoseTools' => $customer->getDiagnoseTools(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function mapProfilePayloadToCustomerPayload(array $payload): array
    {
        $mapped = [];

        $fieldMap = [
            'vorname' => 'vorname',
            'firstName' => 'vorname',
            'nachname' => 'nachname',
            'lastName' => 'nachname',
            'email' => 'email',
            'telefon' => 'telefon',
            'phone' => 'telefon',
            'firmenname' => 'firmenname',
            'company' => 'firmenname',
            'strasse' => 'strasse',
            'street' => 'strasse',
            'hausnummer' => 'hausnummer',
            'houseNumber' => 'hausnummer',
            'plz' => 'plz',
            'zip' => 'plz',
            'stadt' => 'stadt',
            'city' => 'stadt',
            'land' => 'land',
            'country' => 'land',
            'ustId' => 'ustId',
            'vatId' => 'ustId',
            'website' => 'website',
            'hasDyno' => 'hasDyno',
            'tuningTools' => 'tuningTools',
            'diagnoseTools' => 'diagnoseTools',
        ];

        foreach ($fieldMap as $input => $target) {
            if (!array_key_exists($input, $payload)) {
                continue;
            }

            $mapped[$target] = $payload[$input];
        }

        return $mapped;
    }
}
