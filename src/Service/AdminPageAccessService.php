<?php

namespace App\Service;

use App\Entity\AdminPageAccess;
use App\Entity\User;
use App\Repository\AdminPageAccessRepository;
use App\Service\AdminPageRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Stopwatch\Stopwatch;

final class AdminPageAccessService
{
    /**
     * @var array<string, AdminPageAccess>|null
     */
    private ?array $rulesByKey = null;

    public function __construct(
        private readonly AdminPageAccessRepository $repository,
        private readonly AdminPageRegistry $pageRegistry,
        #[Autowire('%admin_page_access_default_groups%')]
        private readonly array $defaultGroups,
        private readonly ?Stopwatch $stopwatch = null,
    ) {
    }

    public function isAllowed(string $pageKey, ?User $user): bool
    {
        $this->profileStart('easyadmin.access.is_allowed');

        try {
            if (!$user instanceof User) {
                return false;
            }

            if ($this->isInDefaultGroup($user)) {
                return true;
            }

            $rules = $this->getRulesByKey();
            if ([] === $rules) {
                return true;
            }
            $rule = $rules[$pageKey] ?? null;
            if ($rule instanceof AdminPageAccess) {
                return $this->isRuleAllowed($rule, $user);
            }

            $parentRule = $this->getMostSpecificParentRule($pageKey, $rules);
            if ($parentRule instanceof AdminPageAccess) {
                return $this->isRuleAllowed($parentRule, $user);
            }

            return $this->isDefaultAllowed($user);
        } finally {
            $this->profileStop('easyadmin.access.is_allowed');
        }
    }

    private function isRuleAllowed(AdminPageAccess $rule, User $user): bool
    {
        if (!$rule->isEnabled()) {
            return false;
        }

        $allowedGroups = $rule->getAllowedGroups();
        if ($allowedGroups->isEmpty()) {
            return true;
        }

        $userGroup = $user->getGruppe();
        if (null === $userGroup) {
            return false;
        }

        $userGroupId = $userGroup->getId();
        foreach ($allowedGroups as $group) {
            if (null !== $userGroupId && $group->getId() === $userGroupId) {
                return true;
            }

            if (null === $userGroupId && 0 === strcasecmp($group->getName(), $userGroup->getName())) {
                return true;
            }
        }

        return false;
    }

    private function isDefaultAllowed(User $user): bool
    {
        if ([] === $this->defaultGroups) {
            return true;
        }

        return $this->isInDefaultGroup($user);
    }

    private function isInDefaultGroup(User $user): bool
    {
        if ([] === $this->defaultGroups) {
            return false;
        }

        $userGroup = $user->getGruppe();
        if (null === $userGroup) {
            return false;
        }

        $userGroupName = $userGroup->getName();
        foreach ($this->defaultGroups as $allowedGroup) {
            if (0 === strcasecmp((string) $allowedGroup, $userGroupName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, AdminPageAccess> $rules
     */
    private function getMostSpecificParentRule(string $pageKey, array $rules): ?AdminPageAccess
    {
        $matched = null;
        foreach ($this->pageRegistry->getParentMenuKeys($pageKey) as $parentKey) {
            $parentRule = $rules[$parentKey] ?? null;
            if (!$parentRule instanceof AdminPageAccess) {
                continue;
            }

            if (!$parentRule->isIncludeChildren()) {
                continue;
            }

            $matched = $parentRule;
        }

        return $matched;
    }

    /**
     * @return array<string, AdminPageAccess>
     */
    private function getRulesByKey(): array
    {
        if (null !== $this->rulesByKey) {
            return $this->rulesByKey;
        }

        $this->profileStart('easyadmin.access.load_rules');

        try {
            $rules = [];
            foreach ($this->repository->findAll() as $rule) {
                $rules[$rule->getPageKey()] = $rule;
            }
        } finally {
            $this->profileStop('easyadmin.access.load_rules');
        }

        $this->rulesByKey = $rules;

        return $rules;
    }

    private function profileStart(string $name): void
    {
        $this->stopwatch?->start($name, 'easyadmin-access');
    }

    private function profileStop(string $name): void
    {
        if (null === $this->stopwatch || !$this->stopwatch->isStarted($name)) {
            return;
        }

        $this->stopwatch->stop($name);
    }
}
