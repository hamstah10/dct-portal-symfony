<?php

namespace App\Service\Ai;

use App\Entity\User;
use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\Memory\Memory;
use Symfony\AI\Agent\Memory\MemoryProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\SecurityBundle\Security;

final class AdminMemoryProvider implements MemoryProviderInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return list<Memory>
     */
    public function load(Input $input): array
    {
        $memories = [];

        $memories[] = new Memory(
            "## App Context\n"
            ."- Name: TFD CRM\n"
            ."- Language: Deutsch\n"
            ."- Timezone: Europe/Berlin\n"
        );

        $userMemory = $this->buildUserMemory();
        if (null !== $userMemory) {
            $memories[] = $userMemory;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null !== $request) {
            $memories[] = new Memory(
                "## Request Context\n"
                ."- Path: ".$request->getPathInfo()."\n"
                ."- Method: ".$request->getMethod()."\n"
            );
        }

        return $memories;
    }

    private function buildUserMemory(): ?Memory
    {
        $user = $this->security->getUser();
        if (!$user instanceof \Symfony\Component\Security\Core\User\UserInterface) {
            return null;
        }

        $identifier = $user->getUserIdentifier();
        $name = $identifier;
        $roles = $user->getRoles();

        if ($user instanceof User) {
            $first = trim((string) ($user->getVorname() ?? ''));
            $last = trim((string) ($user->getNachname() ?? ''));
            $full = trim($first.' '.$last);
            if ('' !== $full) {
                $name = $full;
            }
        }

        $roleList = [];
        foreach ($roles as $role) {
            $role = trim((string) $role);
            if ('' !== $role) {
                $roleList[] = $role;
            }
        }

        $roleText = '' !== implode(', ', $roleList) ? implode(', ', $roleList) : '-';

        return new Memory(
            "## User Context\n"
            ."- Name: ".$name."\n"
            ."- Identifier: ".$identifier."\n"
            ."- Roles: ".$roleText."\n"
        );
    }
}
