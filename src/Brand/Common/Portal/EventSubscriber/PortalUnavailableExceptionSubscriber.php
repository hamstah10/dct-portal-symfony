<?php

declare(strict_types=1);

namespace App\Brand\Common\Portal\EventSubscriber;

use App\Brand\Common\Context\SystemType;
use App\Brand\Common\Portal\Exception\PortalUnavailableException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class PortalUnavailableExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof PortalUnavailableException) {
            return;
        }

        $portal = $throwable->getPortal();
        $systemType = $throwable->getSystemType();
        $isPortal = $systemType === SystemType::PORTAL;

        $headline = $isPortal ? 'Portal momentan nicht verfuegbar' : 'Website momentan nicht verfuegbar';
        $message = $isPortal
            ? 'Der Kundenbereich ist gerade deaktiviert oder im Wartungsmodus. Bitte probiere es spaeter erneut.'
            : 'Die Website ist gerade deaktiviert oder im Wartungsmodus. Bitte probiere es spaeter erneut.';

        if ($throwable->getReason() === PortalUnavailableException::REASON_INACTIVE) {
            $headline = 'Marke momentan nicht verfuegbar';
            $message = 'Dieses Portal ist aktuell nicht aktiv geschaltet. Bitte versuche es spaeter erneut.';
        }

        $html = $this->twig->render('brand/common/portal_unavailable.html.twig', [
            'portalConfig' => $portal,
            'systemType' => $systemType,
            'headline' => $headline,
            'message' => $message,
            'primaryHex' => $portal->getPrimaryColor(),
            'secondaryHex' => $portal->getSecondaryColor(),
            'primaryRgb' => $portal->getPrimaryRgb(),
            'logoPath' => $portal->getLogoPath(),
            'websiteUrl' => $portal->getWebsiteUrl(),
            'portalUrl' => $portal->getPortalUrl(),
        ]);

        $event->setResponse(new Response($html, Response::HTTP_SERVICE_UNAVAILABLE));
    }
}
