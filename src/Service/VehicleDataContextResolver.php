<?php

namespace App\Service;

use App\Entity\ApiToken;
use App\Entity\VehicleDataContext;
use App\Entity\VehicleDataPool;
use App\Repository\VehicleDataContextRepository;
use App\Repository\VehicleDataPoolRepository;
use Symfony\Component\HttpFoundation\Request;

final class VehicleDataContextResolver
{
    public const DEFAULT_POOL_KEY = 'live';
    public const HEADER_CONTEXT_KEY = 'X-Vehicle-Data-Context';
    public const HEADER_POOL_KEY = 'X-Vehicle-Data-Pool';

    public function __construct(
        private readonly VehicleDataContextRepository $vehicleDataContextRepository,
        private readonly VehicleDataPoolRepository $vehicleDataPoolRepository,
    ) {
    }

    public function resolvePoolForRequest(Request $request, ?ApiToken $apiToken = null): ?VehicleDataPool
    {
        $context = $this->resolveContextForRequest($request, $apiToken);
        $requestedPool = trim((string) (
            $request->headers->get(self::HEADER_POOL_KEY)
            ?? $request->query->get('poolKey', '')
        ));

        if ('' !== $requestedPool) {
            $pool = $this->vehicleDataPoolRepository->findActiveByPoolKey($requestedPool);
            if (!$pool instanceof VehicleDataPool) {
                return null;
            }

            if ($context instanceof VehicleDataContext && !$context->allowsDataPool($pool)) {
                return null;
            }

            return $pool;
        }

        if ($context instanceof VehicleDataContext && $context->getDefaultVehicleDataPool() instanceof VehicleDataPool) {
            $defaultPool = $context->getDefaultVehicleDataPool();
            if ($defaultPool->isActive()) {
                return $defaultPool;
            }
        }

        if ($context instanceof VehicleDataContext && 1 === $context->getAllowedDataPools()->count()) {
            $onlyPool = $context->getAllowedDataPools()->first();
            if ($onlyPool instanceof VehicleDataPool && $onlyPool->isActive()) {
                return $onlyPool;
            }
        }

        return $this->vehicleDataPoolRepository->findActiveByPoolKey(self::DEFAULT_POOL_KEY);
    }

    public function resolveContextForRequest(Request $request, ?ApiToken $apiToken = null): ?VehicleDataContext
    {
        $requestedContext = trim((string) (
            $request->headers->get(self::HEADER_CONTEXT_KEY)
            ?? $request->query->get('contextKey', '')
        ));

        if ('' !== $requestedContext) {
            $context = $this->vehicleDataContextRepository->findActiveByContextKey($requestedContext);
            if (!$context instanceof VehicleDataContext) {
                return null;
            }

            if ($apiToken instanceof ApiToken && $apiToken->getVehicleDataContext() instanceof VehicleDataContext) {
                $tokenContext = $apiToken->getVehicleDataContext();
                if ($tokenContext->getId() !== $context->getId()) {
                    return null;
                }
            }

            return $context;
        }

        return $apiToken?->getVehicleDataContext();
    }

    /**
     * Combines the visibility toggles of the resolved pool and (if present) the resolved
     * context. A feature is only visible if neither the pool nor the context disables it.
     *
     * @return array{showPrices: bool, showHints: bool, showGearbox: bool, showDyno: bool, showEcoStage: bool, showOptionCategories: bool, allowVehicleEditing: bool}
     */
    public function resolveFeatureFlags(?VehicleDataPool $pool, ?VehicleDataContext $context): array
    {
        return [
            'showPrices' => (!$pool instanceof VehicleDataPool || $pool->isShowPrices())
                && (!$context instanceof VehicleDataContext || $context->isShowPrices()),
            'showHints' => (!$pool instanceof VehicleDataPool || $pool->isShowHints())
                && (!$context instanceof VehicleDataContext || $context->isShowHints()),
            'showGearbox' => (!$pool instanceof VehicleDataPool || $pool->isShowGearbox())
                && (!$context instanceof VehicleDataContext || $context->isShowGearbox()),
            'showDyno' => (!$pool instanceof VehicleDataPool || $pool->isShowDyno())
                && (!$context instanceof VehicleDataContext || $context->isShowDyno()),
            'showEcoStage' => (!$pool instanceof VehicleDataPool || $pool->isShowEcoStage())
                && (!$context instanceof VehicleDataContext || $context->isShowEcoStage()),
            'showOptionCategories' => (!$context instanceof VehicleDataContext || $context->isShowOptionCategories()),
            'allowVehicleEditing' => (!$context instanceof VehicleDataContext || $context->isAllowVehicleEditing()),
        ];
    }
}
