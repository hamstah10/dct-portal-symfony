<?php

namespace Tfd\PartnerWizardBundle\Service;

use App\Repository\VehicleTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardLead;

final class PartnerWizardLeadService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PartnerWizardRecommendationService $recommendationService,
        private readonly PartnerWizardMailer $mailer,
        private readonly PartnerWizardDomainGuard $domainGuard,
        private readonly VehicleTypeRepository $vehicleTypeRepository,
    ) {
    }

    public function createLead(PartnerWizardClient $client, array $payload, Request $request): PartnerWizardLead
    {
        $contact = $payload['contact'] ?? [];
        $recommendation = $this->recommendationService->recommend($client, $payload);

        $vehicleTypeIds = array_map('intval', (array) ($payload['vehicleCategories'] ?? []));
        $mainVehicleTypeId = (int) ($payload['mainVehicleCategory'] ?? 0);

        $lead = new PartnerWizardLead();
        $lead
            ->setClient($client)
            ->setName((string) ($contact['name'] ?? ''))
            ->setEmail((string) ($contact['email'] ?? ''))
            ->setCompany($contact['company'] ?? null)
            ->setPhone($contact['phone'] ?? null)
            ->setWebsite($contact['website'] ?? null)
            ->setGoal($payload['goal'] ?? null)
            ->setExperience($payload['experience'] ?? null)
            ->setMainVehicleType($mainVehicleTypeId > 0 ? $this->vehicleTypeRepository->find($mainVehicleTypeId) : null)
            ->setCurrentTool($payload['currentTool'] ?? null)
            ->setDesiredToolLevel($payload['desiredToolLevel'] ?? null)
            ->setServices($payload['services'] ?? [])
            ->setMonthlyVehicleCount((int) ($payload['monthlyVehicleCount'] ?? 0))
            ->setRecommendationStatus($recommendation['status'])
            ->setRecommendedPackageKey($recommendation['packageKey'])
            ->setRecommendedPackageTitle($recommendation['packageTitle'])
            ->setRecommendedPackageText($recommendation['packageText'])
            ->setRecommendedTools($recommendation['recommendedTools'])
            ->setToolDirection($recommendation['toolDirection'])
            ->setNextStep($recommendation['nextStep'])
            ->setEstimatedMonthlyRevenue((int) $recommendation['estimatedMonthlyRevenue'])
            ->setPayload($payload)
            ->setSource($payload['source'] ?? 'widget')
            ->setOriginDomain($this->domainGuard->getOriginDomain($request))
            ->setStatus($recommendation['status'] === 'tool_required' ? 'tool_consulting' : 'new');

        if ($vehicleTypeIds !== []) {
            foreach ($this->vehicleTypeRepository->findBy(['id' => $vehicleTypeIds]) as $vehicleType) {
                $lead->addVehicleCategory($vehicleType);
            }
        }

        $this->em->persist($lead);
        $this->em->flush();

        $this->mailer->sendLeadNotification($client, $lead);

        return $lead;
    }
}
