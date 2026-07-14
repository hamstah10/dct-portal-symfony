<?php

namespace Tfd\PartnerWizardBundle\Service;

use App\Entity\MediaAsset;
use App\Entity\ShopProduct;
use App\Entity\ToolManufacturer;
use App\Entity\VehicleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardClient;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardPackage;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestion;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardQuestionOption;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardStep;
use Tfd\PartnerWizardBundle\Entity\PartnerWizardTool;

final class PartnerWizardConfigService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RequestStack $requestStack,
        private readonly string $projectDir,
    ) {
    }

    public function getClient(string $clientId): ?PartnerWizardClient
    {
        return $this->em->getRepository(PartnerWizardClient::class)->findOneBy([
            'clientId' => $clientId,
            'active' => true,
        ]);
    }

    public function toConfigArray(PartnerWizardClient $client): array
    {
        $tools = $this->em->getRepository(PartnerWizardTool::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC', 'name' => 'ASC']
        );

        $packages = $this->em->getRepository(PartnerWizardPackage::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC', 'title' => 'ASC']
        );

        return [
            'clientId' => $client->getClientId(),
            'name' => $client->getName(),
            'headline' => $client->getHeadline(),
            'introText' => $client->getIntroText(),
            'primaryColor' => $client->getPrimaryColor(),
            'theme' => $client->getTheme(),
            'toolRequired' => $client->isToolRequired(),
            'defaultVehiclePrice' => $client->getDefaultVehiclePrice(),
            'enabledVehicleCategories' => array_map(
                fn (VehicleType $vehicleType) => $this->serializeVehicleType($vehicleType),
                $client->getEnabledVehicleCategories()->toArray()
            ),
            'enabledServices' => $client->getEnabledServices(),
            'currentToolOptions' => array_map(
                fn (ToolManufacturer $toolManufacturer) => $this->serializeCurrentToolOption($toolManufacturer),
                $client->getEnabledCurrentTools()->toArray()
            ),
            'customLabels' => $client->getCustomLabels(),
            'privacyNotice' => $client->getPrivacyNotice(),
            'toolDisclaimer' => $client->getToolDisclaimer(),
            'tools' => array_map(fn (PartnerWizardTool $tool) => [
                'key' => $tool->getToolKey(),
                'name' => $tool->getName(),
                'description' => $tool->getDescription(),
                'vehicleCategories' => array_map(
                    fn (VehicleType $vehicleType) => $this->serializeVehicleType($vehicleType),
                    $tool->getRecommendedForVehicleCategories()->toArray()
                ),
                'services' => $tool->getRecommendedForServices(),
                'level' => $tool->getLevel(),
                'imageUrl' => $tool->getImageUrl(),
                'product' => $this->serializeProduct($tool),
            ], $tools),
            'packages' => array_map(fn (PartnerWizardPackage $package) => [
                'key' => $package->getPackageKey(),
                'title' => $package->getTitle(),
                'description' => $package->getDescription(),
                'requiredStatus' => $package->getRequiredStatus(),
                'features' => $package->getFeatures(),
                'ctaLabel' => $package->getCtaLabel(),
            ], $packages),
            'steps' => $this->serializeSteps($client),
        ];
    }

    private function serializeSteps(PartnerWizardClient $client): array
    {
        $steps = $this->em->getRepository(PartnerWizardStep::class)->findBy(
            ['client' => $client, 'active' => true],
            ['priority' => 'ASC']
        );

        return array_map(fn (PartnerWizardStep $step) => [
            'key' => $step->getStepKey(),
            'label' => $step->getLabel(),
            'type' => $step->getType(),
            'questions' => array_map(
                fn (PartnerWizardQuestion $question) => $this->serializeQuestion($client, $question),
                array_values(array_filter(
                    $step->getQuestions()->toArray(),
                    static fn (PartnerWizardQuestion $question): bool => $question->isActive()
                ))
            ),
        ], $steps);
    }

    private function serializeQuestion(PartnerWizardClient $client, PartnerWizardQuestion $question): array
    {
        return [
            'answerKey' => $question->getAnswerKey(),
            'title' => $question->getTitle(),
            'helpText' => $question->getHelpText(),
            'questionType' => $question->getQuestionType(),
            'multiSelect' => $question->isMultiSelect(),
            'fieldType' => $question->getFieldType(),
            'required' => $question->isRequired(),
            'dependsOnAnswerKey' => $question->getDependsOnAnswerKey(),
            'dependsOnValue' => $question->getDependsOnValue(),
            'options' => $this->resolveQuestionOptions($client, $question),
        ];
    }

    private function resolveQuestionOptions(PartnerWizardClient $client, PartnerWizardQuestion $question): array
    {
        return match ($question->getQuestionType()) {
            'vehicleType' => array_map(
                fn (VehicleType $vehicleType) => $this->serializeVehicleType($vehicleType),
                $client->getEnabledVehicleCategories()->toArray()
            ),
            'toolManufacturer' => array_map(
                fn (ToolManufacturer $toolManufacturer) => $this->serializeCurrentToolOption($toolManufacturer),
                $client->getEnabledCurrentTools()->toArray()
            ),
            default => array_map(
                fn (PartnerWizardQuestionOption $option) => $this->serializeQuestionOption($option),
                array_values(array_filter(
                    $question->getOptions()->toArray(),
                    static fn (PartnerWizardQuestionOption $option): bool => $option->isActive()
                ))
            ),
        };
    }

    private function serializeQuestionOption(PartnerWizardQuestionOption $option): array
    {
        $iconUrl = $option->getMediaAsset() instanceof MediaAsset
            ? $this->publicAssetUrl($option->getMediaAsset()->getFilePath())
            : $this->publicAssetUrl($option->getIcon());

        return [
            'value' => $option->getValue(),
            'title' => $option->getTitle(),
            'text' => $option->getText(),
            'icon' => $iconUrl,
            'imageUrl' => $iconUrl,
            'numericValue' => $option->getNumericValue(),
        ];
    }

    /**
     * @return array{value: string, title: string, text: string, id: int|null, icon: ?string, imageUrl: ?string}
     */
    private function serializeCurrentToolOption(ToolManufacturer $toolManufacturer): array
    {
        $description = trim(strip_tags((string) $toolManufacturer->getBeschreibung()));
        $logoUrl = $this->publicAssetUrl($toolManufacturer->getLogo());

        return [
            'value' => 'tool-' . $toolManufacturer->getId(),
            'title' => $toolManufacturer->getName(),
            'text' => $description !== '' ? mb_substr($description, 0, 140) : $toolManufacturer->getName() . ' Tool vorhanden.',
            'id' => $toolManufacturer->getId(),
            'icon' => $logoUrl,
            'imageUrl' => $logoUrl,
        ];
    }

    private function serializeProduct(PartnerWizardTool $tool): ?array
    {
        $product = $tool->getShopProduct();
        if (!$product instanceof ShopProduct || !$product->isActive()) {
            return null;
        }

        return [
            'uid' => $product->getUid(),
            'title' => $product->getTitle(),
            'sku' => $product->getSku(),
            'price' => $product->getPrice(),
            'specialPrice' => $this->resolveSpecialPrice($product),
            'slug' => $product->getSlug(),
            'imageUrl' => $this->firstProductImageUrl($product),
        ];
    }

    private function resolveSpecialPrice(ShopProduct $product): ?string
    {
        $specialPrice = (float) $product->getSpecialPrice();

        return $specialPrice > 0 ? $product->getSpecialPrice() : null;
    }

    private function firstProductImageUrl(ShopProduct $product): ?string
    {
        foreach ($product->getMediaAssets() as $asset) {
            $path = trim((string) $asset->getFilePath());
            if ('' === $path) {
                continue;
            }

            return $this->publicAssetUrl($path);
        }

        return null;
    }

    private function publicAssetUrl(?string $path): ?string
    {
        $path = trim((string) ($path ?? ''));
        if ('' === $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }

        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = null !== $request ? $request->getSchemeAndHttpHost() : '';

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    /**
     * @return array{id: int, name: string, slug: ?string, icon: ?string, imageUrl: ?string}
     */
    private function serializeVehicleType(VehicleType $vehicleType): array
    {
        $imageUrl = $this->publicAssetUrl($vehicleType->getImage());
        $iconUrl = $imageUrl ?? $this->resolveUxIconDataUri($vehicleType->getIcon());

        return [
            'id' => $vehicleType->getId(),
            'name' => $vehicleType->getName(),
            'slug' => $vehicleType->getSlug(),
            'icon' => $iconUrl,
            'imageUrl' => $imageUrl,
        ];
    }

    private function resolveUxIconDataUri(?string $iconId): ?string
    {
        $iconId = trim((string) ($iconId ?? ''));
        if ('' === $iconId || !str_contains($iconId, ':')) {
            return null;
        }

        [$collection, $iconName] = explode(':', $iconId, 2);
        $collection = trim($collection);
        $iconName = trim($iconName);
        if ('' === $collection || '' === $iconName) {
            return null;
        }

        $path = sprintf('%s/assets/icons/%s/%s.svg', rtrim($this->projectDir, '/'), $collection, $iconName);
        if (!is_file($path)) {
            return null;
        }

        $svg = @file_get_contents($path);
        if (false === $svg || '' === trim($svg)) {
            return null;
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
