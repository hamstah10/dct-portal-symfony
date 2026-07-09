<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Portals\Chiptuningfile\Service\PortalHeaderSearchService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('portal_header_search', template: 'components/portal_header_search.html.twig')]
final class PortalHeaderSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $query = '';

    /**
     * @var list<string>
     */
    #[LiveProp]
    public array $enabledModules = [];

    public function __construct(
        private readonly PortalHeaderSearchService $searchService,
    ) {
    }

    /**
     * @return list<array{
     *     text:string,
     *     subtext:?string,
     *     url:string,
     *     group:string,
     *     meta:?string,
     *     keywords:string
     * }>
     */
    public function getResults(): array
    {
        return $this->searchService->search($this->query, $this->enabledModules, 8);
    }

    public function getSearchActionUrl(): string
    {
        return $this->searchService->getDefaultSearchUrl($this->enabledModules);
    }
}
