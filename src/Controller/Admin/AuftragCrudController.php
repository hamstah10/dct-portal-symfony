<?php

namespace App\Controller\Admin;

use App\Entity\Auftrag;
use App\Entity\SupportTicket;
use App\Entity\SupportTicketMessage;
use App\Filter\AuftragFirmaFilter;
use App\Form\StatusButtonType;
use App\Repository\AuftragRepository;
use App\Repository\MediaAssetRepository;
use App\Repository\MediaFolderRepository;
use App\Repository\SupportTicketRepository;
use App\Service\Realtime\PortalMercureTopics;
use App\Service\Realtime\PortalRealtimePublisher;
use App\Service\Webhook\WebhookDispatcher;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminCrud(routePath: '/auftraege', routeName: 'admin_auftraege')]
final class AuftragCrudController extends AbstractCrudController
{
    use HandlesImageUploadsTrait;

    public function __construct(
        private readonly WebhookDispatcher $webhookDispatcher,
        private readonly PortalRealtimePublisher $portalRealtimePublisher,
        private readonly PortalMercureTopics $portalMercureTopics,
        private readonly AuftragRepository $auftragRepository,
        private readonly SupportTicketRepository $supportTicketRepository,
        private readonly MediaAssetRepository $mediaAssetRepository,
        private readonly MediaFolderRepository $mediaFolderRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Auftrag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Auftrag')
            ->setEntityLabelInPlural('Aufträge')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
            ->addFormTheme('form/status_buttons.html.twig')
            ->overrideTemplate('crud/index', 'admin/auftrag/index.html.twig')
            ->overrideTemplate('crud/edit', 'admin/auftrag/edit.html.twig')
            ->setDefaultRowAction(Action::EDIT)
            ->autocomplete(
            callback: static fn ($entity): string => method_exists($entity, 'getOrderNumber') ? $entity->getOrderNumber() : (string) $entity
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(AuftragFirmaFilter::new('firma', 'Firma'))
            ->add('status')
            ->add('createdAt');
    }

    public function configureFields(string $pageName): iterable
    {
        $stageIconMap = Crud::PAGE_INDEX === $pageName ? $this->loadStageIconMap() : [];

        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            AssociationField::new('kunde', 'Firma')
                ->onlyOnIndex()
                ->setSortable(true)
                ->setSortProperty('firmenname')
                ->renderAsHtml()
                ->formatValue(function (mixed $value, ?Auftrag $entity): string {
                    $firma = $entity?->getKunde()?->getFirma();
                    $companyName = $this->resolveCompanyName($entity);
                    $kuerzel = trim((string) ($firma?->getKuerzel() ?? ''));
                    $logoPath = trim((string) ($firma?->getLogo() ?? ''));
                    $companyTitle = htmlspecialchars($companyName, ENT_QUOTES);

                    if ('' !== $logoPath) {
                        $normalizedPath = $logoPath;
                        if (!str_contains($normalizedPath, '/')) {
                            $normalizedPath = 'uploads/firmen/' . $normalizedPath;
                        }

                        $url = str_starts_with($normalizedPath, 'http://') || str_starts_with($normalizedPath, 'https://')
                            ? $normalizedPath
                            : '/' . ltrim($normalizedPath, '/');

                        return sprintf(
                            '<span style="display:inline-flex;align-items:center;justify-content:center;width:100px;height:40px;border-radius:0px;overflow:hidden;" title="%s"><img src="%s" alt="%s" style="width:100%%;height:100%%;object-fit:contain;"></span>',
                            $companyTitle,
                            htmlspecialchars($url, ENT_QUOTES),
                            $companyTitle
                        );
                    }

                    if ('' === $kuerzel) {
                        $kuerzel = $this->buildCompanyInitials($companyName);
                    }

                    $normalizedKuerzel = strtoupper($kuerzel);
                    $background = match ($normalizedKuerzel) {
                        'TFD' => '#bd1e21',
                        'CPT' => '#e31b1b',
                        'TF' => '#111931',
                        default => '#6b7280',
                    };
                    return sprintf(
                        '<span style="display:inline-flex;align-items:center;justify-content:center;min-width:40px;padding:2px 8px;border-radius:0;background:%s;color:#fff;font-size:12px;font-weight:700;line-height:1.2;" title="%s">%s</span>',
                        htmlspecialchars($background, ENT_QUOTES),
                        $companyTitle,
                        htmlspecialchars($normalizedKuerzel, ENT_QUOTES)
                    );
                }),
            TextField::new('orderNumber', 'Auftragsnummer')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(static function (mixed $value, ?Auftrag $entity): string {
                    $orderNumber = htmlspecialchars((string) $value, ENT_QUOTES);
                    $dateText = $entity?->getCreatedAt()?->format('d.m.Y H:i') ?? '-';

                    return sprintf(
                        '<span class="auftrag-order-code">%s</span><small class="text-muted d-block">%s</small>',
                        $orderNumber,
                        htmlspecialchars($dateText, ENT_QUOTES)
                    );
                }),
            AssociationField::new('kunde', 'Kunde'),

            TextField::new('fileName', 'Datei & Setup')
                ->onlyOnIndex()
                ->renderAsHtml()
                ->formatValue(function (mixed $value, ?Auftrag $entity): string {
                    if (!$entity instanceof Auftrag) {
                        return '-';
                    }

                    $oriName = trim((string) $entity->getFileName());
                    $oriSize = $this->formatFileSizeInMb($entity->getFileSize());
                    $oriLabel = '' !== $oriName
                        ? sprintf(
                            '<span title="%s">%s</span><small class="text-muted d-block auftrag-file-meta">ORI · %s MB</small>',
                            htmlspecialchars($oriName, ENT_QUOTES),
                            htmlspecialchars($this->truncateWithEllipsis($oriName, 26), ENT_QUOTES),
                            htmlspecialchars($oriSize, ENT_QUOTES)
                        )
                        : '<span class="text-muted">ORI fehlt</span>';

                    $modPath = trim((string) $entity->getFinishedFilePath());
                    $modLabel = '<span class="text-muted d-block">MOD offen</span>';
                    if ('' !== $modPath) {
                        $modName = trim((string) ($entity->getFinishedFileName() ?? ''));
                        if ('' === $modName) {
                            $modName = basename($modPath);
                        }

                        $modUrl = '/' . ltrim($modPath, '/');
                        $modSize = $this->formatFileSizeInMb($entity->getFinishedFileSize());
                        $modLabel = sprintf(
                            '<a href="%s" target="_blank" rel="noopener" title="%s" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:inherit;"><span aria-hidden="true"><i class="fa fa-download"></i></span><span>%s</span></a><small class="text-muted d-block auftrag-file-meta">MOD · %s MB</small>',
                            htmlspecialchars($modUrl, ENT_QUOTES),
                            htmlspecialchars($modName, ENT_QUOTES),
                            htmlspecialchars($this->truncateWithEllipsis($modName, 24), ENT_QUOTES),
                            htmlspecialchars($modSize, ENT_QUOTES)
                        );
                    }

                    $tool = trim((string) $entity->getTuningTool());
                    $method = trim((string) $entity->getTuningMethod());
                    $slaveMaster = trim((string) $entity->getSlaveOrMaster());
                    $readType = trim((string) $entity->getReadingType());
                    $setupParts = array_values(array_filter([$tool, $method, $readType, $slaveMaster], static fn (?string $part): bool => null !== $part && '' !== trim($part)));
                    $setupLabel = [] !== $setupParts
                        ? htmlspecialchars(implode(' · ', $setupParts), ENT_QUOTES)
                        : '-';

                    return sprintf(
                        '<div>%s</div><div class="mt-2">%s</div><small class="text-muted d-block mt-2">%s</small>',
                        $oriLabel,
                        $modLabel,
                        $setupLabel
                    );
                }),
            TextField::new('priorityLabel', 'Prioritaet')
                ->onlyOnIndex()
                ->hideOnForm(),

            TextField::new('manufacturer', 'Fahrzeug')
                ->onlyOnIndex()
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(function (mixed $value, ?Auftrag $entity): string {
                    if (!$entity instanceof Auftrag) {
                        return '-';
                    }

                    $lineOne = trim(implode(' ', array_filter([
                        trim((string) $entity->getManufacturer()),
                        trim((string) $entity->getModel()),
                    ], static fn (string $part): bool => '' !== $part)));
                    if ('' === $lineOne) {
                        $lineOne = trim((string) $entity->getVehicleDisplay());
                    }

                    $lineTwoParts = array_values(array_filter([
                        trim((string) $entity->getVehicleSeries()),
                        trim((string) $entity->getEngine()),
                        trim((string) $entity->getEcu()),
                    ], static fn (?string $part): bool => null !== $part && '' !== trim($part)));

                    $lineOne = '' !== $lineOne ? htmlspecialchars($lineOne, ENT_QUOTES) : '-';
                    $lineTwo = [] !== $lineTwoParts
                        ? htmlspecialchars(implode(' · ', $lineTwoParts), ENT_QUOTES)
                        : '';

                    return '' !== $lineTwo
                        ? sprintf('<div>%s</div><small class="text-muted d-block">%s</small>', $lineOne, $lineTwo)
                        : $lineOne;
                }),

            // Step 3 (React): Optionen
            TextField::new('stageLabel', 'Stage')
                ->onlyOnIndex()
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(function (mixed $value, ?Auftrag $entity) use ($stageIconMap): string {
                    $label = trim((string) $value);
                    if ('' === $label) {
                        return '<span class="text-muted">-</span>';
                    }

                    $normalized = strtolower($label);
                    $iconKey = null;
                    if (str_contains($normalized, 'stage 1') || str_contains($normalized, 'stage1')) {
                        $iconKey = 'stage 1';
                    } elseif (str_contains($normalized, 'stage 2') || str_contains($normalized, 'stage2')) {
                        $iconKey = 'stage 2';
                    } elseif (str_contains($normalized, 'stage 3') || str_contains($normalized, 'stage3')) {
                        $iconKey = 'stage 3';
                    }

                    $iconPath = null;
                    if (null !== $iconKey) {
                        $iconPath = $stageIconMap[$iconKey] ?? null;
                    } else {
                        $iconPath = $stageIconMap[$normalized] ?? null;
                    }

                    if (null === $iconPath || '' === $iconPath) {
                        return htmlspecialchars($label, ENT_QUOTES);
                    }

                    $escapedLabel = htmlspecialchars($label, ENT_QUOTES);
                    $escapedPath = htmlspecialchars($iconPath, ENT_QUOTES);

                    return sprintf(
                        '<span class="stage-icon" title="%s" aria-label="%s" style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;"><img src="%s" alt="%s" style="max-width:100%%;max-height:100%%;object-fit:contain;"></span><span class="visually-hidden">%s</span>',
                        $escapedLabel,
                        $escapedLabel,
                        $escapedPath,
                        $escapedLabel,
                        $escapedLabel
                    );
                }),
            TextField::new('selectedOptionsSummary', 'Optionen')
                ->onlyOnIndex()
                ->hideOnForm(),

            // Step 4 (React): Review & Status
            ChoiceField::new('status', 'Status')
                ->setChoices(Auftrag::getStatusChoices())
                ->renderAsBadges([
                    Auftrag::STATUS_RECEIVED => 'info',
                    Auftrag::STATUS_IN_PROGRESS => 'warning',
                    Auftrag::STATUS_PARKED => 'secondary',
                    Auftrag::STATUS_COMPLETED => 'success',
                    Auftrag::STATUS_REJECTED => 'danger',
                ])
                ->onlyOnIndex()
                ->renderExpanded(false),

            EmailField::new('customerEmail', 'Kunden E-Mail')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('vehicleDisplay', 'Fahrzeug')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('tuningTool', 'Lesegeraet')
                ->hideOnForm()
                ->hideOnIndex()
                ->onlyOnDetail(),
            TextField::new('tuningMethod', 'Lesemethode')
                ->hideOnForm()
                ->hideOnIndex()
                ->onlyOnDetail(),
            TextField::new('readingType', 'Leseart')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('slaveOrMaster', 'Master/Slave')
                ->hideOnForm()
                ->hideOnIndex()
                ->onlyOnDetail(),
            TextField::new('priorityLabel', 'Prioritaet')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('vehicleType', 'Fahrzeugtyp')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('manufacturer', 'Hersteller')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('vehicleSeries', 'Baureihe')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('model', 'Modell')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('built', 'Baujahr')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('engine', 'Motor')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('ecu', 'Steuergeraet')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('stageLabel', 'Stage')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(function (mixed $value, ?Auftrag $entity) use ($stageIconMap): string {
                    $label = trim((string) $value);
                    if ('' === $label) {
                        return '<span class="text-muted">-</span>';
                    }

                    $normalized = strtolower($label);
                    $iconKey = null;
                    if (str_contains($normalized, 'stage 1') || str_contains($normalized, 'stage1')) {
                        $iconKey = 'stage 1';
                    } elseif (str_contains($normalized, 'stage 2') || str_contains($normalized, 'stage2')) {
                        $iconKey = 'stage 2';
                    } elseif (str_contains($normalized, 'stage 3') || str_contains($normalized, 'stage3')) {
                        $iconKey = 'stage 3';
                    }

                    $iconPath = null;
                    if (null !== $iconKey) {
                        $iconPath = $stageIconMap[$iconKey] ?? null;
                    } else {
                        $iconPath = $stageIconMap[$normalized] ?? null;
                    }

                    if (null === $iconPath || '' === $iconPath) {
                        return htmlspecialchars($label, ENT_QUOTES);
                    }

                    $escapedLabel = htmlspecialchars($label, ENT_QUOTES);
                    $escapedPath = htmlspecialchars($iconPath, ENT_QUOTES);

                    return sprintf(
                        '<span class="stage-icon" title="%s" aria-label="%s" style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;"><img src="%s" alt="%s" style="max-width:100%%;max-height:100%%;object-fit:contain;"></span><span class="visually-hidden">%s</span>',
                        $escapedLabel,
                        $escapedLabel,
                        $escapedPath,
                        $escapedLabel,
                        $escapedLabel
                    );
                }),
            TextField::new('deviceName', 'Device')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('selectedOptionsSummary', 'Optionen')
                ->hideOnForm()
                ->hideOnIndex(),
            TextareaField::new('orderComment', 'Kommentar')
                ->hideOnForm()
                ->onlyOnDetail(),
            TextField::new('orderNumber', 'Ticket Uploads')
                ->onlyOnDetail()
                ->renderAsHtml()
                ->formatValue(fn (mixed $value, ?Auftrag $entity): string => $this->renderRelatedTicketAttachments($entity)),
            TextField::new('fileName', 'Dateiname')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('finishedFilePath', 'Mod File')
                ->hideOnForm()
                ->hideOnIndex()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderSingleFilePreview($value, 'Fertig-Datei')),
            IntegerField::new('fileSize', 'Dateigrösse (Bytes)')
                ->hideOnForm()
                ->hideOnIndex(),
            Field::new('status', 'Status')
                ->onlyOnForms()
                ->setFormType(StatusButtonType::class),
            TextareaField::new('adminOnHoldMessage', 'On-Hold Nachricht für Portal/Ticket')
                ->onlyOnForms()
                ->setHelp('Wird beim Status "On Hold" automatisch als Support-Ticket an den Kunden ins Portal geschickt.')
                ->setRequired(false),
            TextareaField::new('vehicleDataJson', 'Fahrzeug-Daten')
                ->onlyOnDetail(),
            TextareaField::new('fileData', 'Datei (Base64)')
                ->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Erstellt am')
                ->hideOnForm()
                ->hideOnIndex(),
        ];
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters = parent::configureResponseParameters($responseParameters);

        $pageName = (string) $responseParameters->get('pageName');
        if (Crud::PAGE_INDEX !== $pageName) {
            return $responseParameters;
        }

        $statistics = $this->getStatistics();
        $responseParameters->set('statistics', $statistics);

        return $responseParameters;
    }

    #[Route('/admin/auftraege/{id}/upload-fertig', name: 'admin_auftrag_finished_upload', methods: ['POST'])]
    public function uploadFinishedFiles(Request $request, Auftrag $auftrag, EntityManagerInterface $entityManager): JsonResponse
    {
        $auftragId = $auftrag->getId();
        if (null === $auftragId) {
            return new JsonResponse(['error' => 'Auftrag nicht gefunden.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $csrfToken = (string) $request->request->get('_token', '');
        if ('' === $csrfToken) {
            $csrfToken = (string) $request->headers->get('X-CSRF-TOKEN', $request->headers->get('X-CSRF-Token', ''));
        }
        if (!$this->isCsrfTokenValid('auftrag_finished_upload_' . (string) $auftragId, $csrfToken)) {
            return new JsonResponse(['error' => 'Ungültige Anfrage.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $files = $request->files->get('file');
        if (null === $files) {
            $allFiles = $request->files->all();
            $files = $allFiles['file'] ?? $allFiles['files'] ?? $allFiles;
        }

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (is_array($files)) {
            $files = array_values(array_filter($files, static fn ($file): bool => $file instanceof UploadedFile));
        }

        if (!is_array($files) || [] === $files) {
            return new JsonResponse(['error' => 'Keine Datei erhalten.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $saved = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $entry = $this->appendFinishedUploadEntry($auftrag, $file);
            if (null !== $entry) {
                $saved[] = $entry;
            }
        }

        if ([] === $saved) {
            return new JsonResponse(['error' => 'Datei konnte nicht gespeichert werden.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($auftrag);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'files' => $saved]);
    }

    #[Route('/admin/auftraege/{id}/download-original', name: 'admin_auftrag_original_download', methods: ['GET'])]
    public function downloadOriginalFile(Auftrag $auftrag): Response
    {
        $raw = (string) $auftrag->getFileData();
        $raw = trim($raw);

        if ('' === $raw) {
            return new Response('Keine Datei vorhanden.', Response::HTTP_NOT_FOUND);
        }

        $mimeType = 'application/octet-stream';
        $payload = $raw;

        if (str_starts_with($raw, 'data:')) {
            $parts = explode(',', $raw, 2);
            if (2 === count($parts)) {
                $meta = $parts[0];
                $payload = $parts[1];
                if (preg_match('/^data:([^;]+);base64/i', $meta, $matches)) {
                    $mimeType = $matches[1];
                }
            }
        }

        $decoded = base64_decode($payload, true);
        if (false === $decoded) {
            return new Response('Datei konnte nicht gelesen werden.', Response::HTTP_BAD_REQUEST);
        }

        $fileName = trim((string) $auftrag->getFileName());
        if ('' === $fileName) {
            $fileName = 'original.bin';
        }

        $response = new Response($decoded);
        $response->headers->set('Content-Type', $mimeType);
        $response->headers->set('Content-Disposition', 'attachment; filename="' . addslashes($fileName) . '"');
        $response->headers->set('Content-Length', (string) strlen($decoded));

        return $response;
    }

    #[Route('/admin/auftraege/{id}/upload-fertig/{index}/delete', name: 'admin_auftrag_finished_delete', methods: ['POST'])]
    public function deleteFinishedFile(Request $request, Auftrag $auftrag, int $index, EntityManagerInterface $entityManager): JsonResponse
    {
        $auftragId = $auftrag->getId();
        if (null === $auftragId) {
            return new JsonResponse(['error' => 'Auftrag nicht gefunden.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $csrfToken = (string) $request->request->get('_token', '');
        if ('' === $csrfToken) {
            $csrfToken = (string) $request->headers->get('X-CSRF-TOKEN', $request->headers->get('X-CSRF-Token', ''));
        }
        if (!$this->isCsrfTokenValid('auftrag_finished_delete_' . $auftragId . '_' . $index, $csrfToken)) {
            return new JsonResponse(['error' => 'Ungültige Anfrage.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $files = $auftrag->getFinishedFiles();
        if ([] === $files && null !== $auftrag->getFinishedFilePath()) {
            $files = [[
                'path' => $auftrag->getFinishedFilePath(),
                'name' => $auftrag->getFinishedFileName(),
                'size' => $auftrag->getFinishedFileSize(),
                'uploadedAt' => $auftrag->getFinishedFileUploadedAt()?->format(\DateTimeInterface::ATOM),
            ]];
        }

        if (!array_key_exists($index, $files)) {
            return new JsonResponse(['error' => 'Datei nicht gefunden.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entry = $files[$index];
        $path = is_array($entry) ? (string) ($entry['path'] ?? '') : '';
        $path = ltrim($path, '/');
        if ('' !== $path && !str_contains($path, '..')) {
            $absolutePath = $this->getParameter('kernel.project_dir') . '/public/' . $path;
            if (is_file($absolutePath)) {
                @unlink($absolutePath);
            }
        }

        unset($files[$index]);
        $files = array_values($files);

        if ([] === $files) {
            $auftrag->setFinishedFiles(null);
            $auftrag->setFinishedFilePath(null);
            $auftrag->setFinishedFileName(null);
            $auftrag->setFinishedFileSize(null);
            $auftrag->setFinishedFileUploadedAt(null);
        } else {
            $auftrag->setFinishedFiles($files);
            $last = $files[array_key_last($files)];
            $auftrag->setFinishedFilePath($last['path'] ?? null);
            $auftrag->setFinishedFileName($last['name'] ?? null);
            $auftrag->setFinishedFileSize(is_int($last['size'] ?? null) ? $last['size'] : null);
            $auftrag->setFinishedFileUploadedAt(new \DateTimeImmutable());
        }

        $entityManager->persist($auftrag);
        $entityManager->flush();

        return new JsonResponse(['success' => true, 'files' => $files]);
    }

    private function getStatistics(): array
    {
        $today = new \DateTimeImmutable('00:00:00');
        $yesterday = $today->modify('-1 day');
        $weekAgo = $today->modify('-7 days');

        $auftrags = $this->auftragRepository->findAll();

        $todayCount = 0;
        $yesterdayCount = 0;
        $weekCount = 0;

        $statusCounts = [
            'received' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'rejected' => 0,
        ];

        foreach ($auftrags as $auftrag) {
            $createdAt = $auftrag->getCreatedAt();
            if (!$createdAt) {
                continue;
            }

            $createdDate = $createdAt->format('Y-m-d');
            $todayDate = $today->format('Y-m-d');
            $yesterdayDate = $yesterday->format('Y-m-d');
            $weekDate = $weekAgo->format('Y-m-d');

            if ($createdDate === $todayDate) {
                $todayCount++;
            }
            if ($createdDate === $yesterdayDate) {
                $yesterdayCount++;
            }
            if ($createdDate >= $weekDate && $createdDate <= $todayDate) {
                $weekCount++;
            }

            // Status counts for today
            if ($createdDate === $todayDate) {
                match ($auftrag->getStatus()) {
                    Auftrag::STATUS_RECEIVED => $statusCounts['received']++,
                    Auftrag::STATUS_IN_PROGRESS => $statusCounts['in_progress']++,
                    Auftrag::STATUS_PARKED => $statusCounts['in_progress']++,
                    Auftrag::STATUS_COMPLETED => $statusCounts['completed']++,
                    Auftrag::STATUS_REJECTED => $statusCounts['rejected']++,
                    default => null,
                };
            }
        }

        return [
            'today' => $todayCount,
            'yesterday' => $yesterdayCount,
            'week' => $weekCount,
            'statusToday' => $statusCounts,
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof Auftrag) {
            $this->handleFinishedFileUpload($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof Auftrag) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        $this->handleFinishedFileUpload($entityInstance);

        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        $previousStatus = $this->normalizeStatus($originalData['status'] ?? null);
        $currentStatus = $this->normalizeStatus($entityInstance->getStatus());
        $changedAt = new \DateTimeImmutable();

        parent::updateEntity($entityManager, $entityInstance);

        if ($previousStatus === $currentStatus) {
            return;
        }

        $kunde = $entityInstance->getKunde();

        if (Auftrag::STATUS_PARKED === $currentStatus) {
            $this->syncOnHoldSupportTicket($entityManager, $entityInstance, $kunde);
            $entityManager->flush();
        }

        $this->webhookDispatcher->dispatch('order.status_changed', [
            'orderId' => $entityInstance->getId(),
            'customerId' => $kunde?->getId(),
            'customerEmail' => $entityInstance->getCustomerEmail(),
            'previousStatus' => $previousStatus,
            'previousStatusLabel' => $this->resolveStatusLabel($previousStatus),
            'status' => $currentStatus,
            'statusLabel' => $this->resolveStatusLabel($currentStatus),
            'changedAt' => $changedAt->format(\DateTimeInterface::ATOM),
            'source' => 'crm',
        ]);

        if (null === $kunde || null === $kunde->getId()) {
            return;
        }

        $this->portalRealtimePublisher->publish(
            $this->portalMercureTopics->customerOrders((int) $kunde->getId()),
            sprintf('Auftrag %s Status aktualisiert', (string) $entityInstance->getOrderNumber()),
            [
                'kind' => 'order.status_changed',
                'orderId' => $entityInstance->getId(),
                'orderNumber' => $entityInstance->getOrderNumber(),
                'customerId' => $kunde->getId(),
                'previousStatus' => $previousStatus,
                'previousStatusLabel' => $this->resolveStatusLabel($previousStatus),
                'status' => $currentStatus,
                'statusLabel' => $this->resolveStatusLabel($currentStatus),
                'changedAt' => $changedAt->format(\DateTimeInterface::ATOM),
                'source' => 'superadmin',
            ],
        );
    }

    private function syncOnHoldSupportTicket(EntityManagerInterface $entityManager, Auftrag $auftrag, mixed $kunde): void
    {
        if (!$kunde instanceof \App\Entity\Kunde || null === $kunde->getFirma()) {
            return;
        }

        $orderNumber = $auftrag->getOrderNumber();
        $messageText = trim((string) $auftrag->getAdminOnHoldMessage());
        if ('' === $messageText) {
            $messageText = sprintf(
                'Dein Auftrag %s wurde vorübergehend geparkt. Bitte prüfe dieses Ticket im Portal, damit wir die Bearbeitung schnell fortsetzen können.',
                $orderNumber
            );
        }

        $ticket = $this->findLatestOrderTicket($kunde, $orderNumber);
        if (!$ticket instanceof SupportTicket) {
            $ticket = (new SupportTicket())
                ->setFirma($kunde->getFirma())
                ->setKunde($kunde)
                ->setSubject(sprintf('[%s] Rueckfrage zum Auftrag', $orderNumber))
                ->setPriority(SupportTicket::PRIORITY_HIGH)
                ->setStatus(SupportTicket::STATUS_IN_PROGRESS);

            $entityManager->persist($ticket);
        } else {
            $ticket->setStatus(SupportTicket::STATUS_IN_PROGRESS);
        }

        $message = (new SupportTicketMessage())
            ->setTicket($ticket)
            ->setKunde($kunde)
            ->setSenderType(SupportTicketMessage::SENDER_ADMIN)
            ->setSenderName('Support')
            ->setMessage($messageText);

        $ticket->addMessage($message);
        $ticket->registerNewMessage($messageText, $message->getCreatedAt() ?? new \DateTimeImmutable());
        $entityManager->persist($ticket);
        $auftrag->setAdminOnHoldMessage(null);
    }

    private function findLatestOrderTicket(\App\Entity\Kunde $kunde, string $orderNumber): ?SupportTicket
    {
        return $this->supportTicketRepository->createQueryBuilder('t')
            ->andWhere('t.kunde = :customer')
            ->andWhere('LOWER(t.subject) LIKE :orderNumber')
            ->setParameter('customer', $kunde)
            ->setParameter('orderNumber', '%' . mb_strtolower($orderNumber) . '%')
            ->orderBy('t.updatedAt', 'DESC')
            ->addOrderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function renderRelatedTicketAttachments(?Auftrag $auftrag): string
    {
        if (!$auftrag instanceof Auftrag) {
            return '<span class="text-muted">-</span>';
        }

        $orderNumber = trim($auftrag->getOrderNumber());
        $kunde = $auftrag->getKunde();
        if ('' === $orderNumber || null === $kunde) {
            return '<span class="text-muted">Keine Ticket-Dateien vorhanden.</span>';
        }

        $tickets = $this->supportTicketRepository->createQueryBuilder('t')
            ->leftJoin('t.attachments', 'a')
            ->addSelect('a')
            ->andWhere('t.kunde = :customer')
            ->andWhere('LOWER(t.subject) LIKE :orderNumber')
            ->setParameter('customer', $kunde)
            ->setParameter('orderNumber', '%' . mb_strtolower($orderNumber) . '%')
            ->orderBy('t.updatedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($tickets as $ticket) {
            if (!$ticket instanceof SupportTicket) {
                continue;
            }

            foreach ($ticket->getAttachments() as $attachment) {
                if (!$attachment instanceof \App\Entity\SupportTicketAttachment || null === $attachment->getId()) {
                    continue;
                }

                $downloadUrl = $this->generateUrl('admin_support_ticket_download', [
                    'attachmentId' => $attachment->getId(),
                ]);
                $fileName = htmlspecialchars($attachment->getOriginalFilename(), ENT_QUOTES);
                $ticketNumber = htmlspecialchars($ticket->getTicketNumber(), ENT_QUOTES);
                $createdAt = $attachment->getCreatedAt()?->format('d.m.Y H:i') ?? '-';
                $badge = preg_match('/\.(bin|ori|mod|frf|hex|ols|ecu|tuning)$/i', $attachment->getOriginalFilename()) === 1
                    ? '<span style="display:inline-flex;align-items:center;padding:2px 6px;background:#dbeafe;color:#1d4ed8;border-radius:999px;font-size:11px;font-weight:700;">Korrigiertes File</span>'
                    : '';

                $items[] = sprintf(
                    '<div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border:1px solid #e5e7eb;border-radius:6px;margin-bottom:8px;background:#fff;"><div><div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;"><strong>%s</strong>%s</div><small class="text-muted">%s · Ticket %s</small></div><a class="btn btn-sm btn-outline-secondary" href="%s" target="_blank" rel="noopener">Download</a></div>',
                    $fileName,
                    $badge,
                    htmlspecialchars($createdAt, ENT_QUOTES),
                    $ticketNumber,
                    htmlspecialchars($downloadUrl, ENT_QUOTES),
                );
            }
        }

        if ([] === $items) {
            return '<span class="text-muted">Keine Ticket-Dateien vorhanden.</span>';
        }

        return implode('', $items);
    }

    private function handleFinishedFileUpload(Auftrag $auftrag): void
    {
        $uploads = $auftrag->getUploadedFinishedFiles();
        $singleUpload = $auftrag->getUploadedFinishedFile();
        if ($singleUpload instanceof UploadedFile) {
            $uploads[] = $singleUpload;
        }

        if ([] === $uploads) {
            return;
        }

        foreach ($uploads as $uploadedFile) {
            if (!$uploadedFile instanceof UploadedFile) {
                continue;
            }
            $this->appendFinishedUploadEntry($auftrag, $uploadedFile);
        }

        $auftrag->setUploadedFinishedFiles([]);
        $auftrag->setUploadedFinishedFile(null);
    }

    private function appendFinishedUploadEntry(Auftrag $auftrag, UploadedFile $uploadedFile): ?array
    {
        $finishedFiles = $auftrag->getFinishedFiles() ?? [];
        if ([] === $finishedFiles && null !== $auftrag->getFinishedFilePath()) {
            $finishedFiles[] = [
                'path' => $auftrag->getFinishedFilePath(),
                'name' => $auftrag->getFinishedFileName(),
                'size' => $auftrag->getFinishedFileSize(),
                'uploadedAt' => $auftrag->getFinishedFileUploadedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }

        $relativeDir = 'uploads/auftraege/fertig';
        $id = $auftrag->getId();
        if (null !== $id) {
            $relativeDir .= '/' . $id;
        }

        $fileSize = $uploadedFile->getSize();
        $clientName = str_replace("\0", '', (string) $uploadedFile->getClientOriginalName());
        $clientName = str_replace('\\', '/', $clientName);
        $clientName = trim(basename($clientName));

        $path = $this->storeSingleUploadPreserveFileName($uploadedFile, $relativeDir);
        if (null === $path) {
            return null;
        }

        $entry = [
            'path' => $path,
            'name' => '' !== $clientName ? $clientName : basename($path),
            'size' => is_int($fileSize) ? $fileSize : null,
            'uploadedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ];

        $finishedFiles[] = $entry;

        // Keep latest file in legacy single-file fields.
        $auftrag->setFinishedFilePath($entry['path']);
        $auftrag->setFinishedFileName($entry['name']);
        $auftrag->setFinishedFileSize($entry['size']);
        $auftrag->setFinishedFileUploadedAt(new \DateTimeImmutable());
        $auftrag->setFinishedFiles($finishedFiles);

        return $entry;
    }

    private function normalizeStatus(?string $status): ?string
    {
        if (null === $status) {
            return null;
        }

        $normalized = strtolower(trim($status));

        return '' === $normalized ? null : $normalized;
    }

    private function resolveStatusLabel(?string $status): ?string
    {
        if (null === $status) {
            return null;
        }

        foreach (Auftrag::getStatusChoices() as $label => $value) {
            if ($value === $status) {
                return $label;
            }
        }

        return $status;
    }

    /**
     * @return array<string, string>
     */
    private function loadStageIconMap(): array
    {
        $folder = $this->mediaFolderRepository->findOneBy(['name' => 'Tuning Icons']);
        if (null === $folder) {
            return [];
        }

        $assets = $this->mediaAssetRepository->findBy([
            'folder' => $folder,
            'name' => ['Stage 1', 'Stage 2', 'Stage 3'],
            'active' => true,
        ]);

        $map = [];
        foreach ($assets as $asset) {
            $name = strtolower(trim($asset->getName()));
            if ('' === $name) {
                continue;
            }

            $path = $this->normalizeMediaPath($asset->getFilePath());
            if ('' === $path) {
                continue;
            }

            $map[$name] = $path;
        }

        return $map;
    }

    private function normalizeMediaPath(?string $path): string
    {
        $path = trim((string) $path);
        if ('' === $path) {
            return '';
        }

        if (!str_starts_with($path, '/') && !preg_match('#^https?://#i', $path)) {
            $path = '/' . $path;
        }

        return $path;
    }

    private function resolveCompanyName(?Auftrag $auftrag): string
    {
        $name = trim((string) ($auftrag?->getKunde()?->getFirma()?->getName() ?? ''));
        if ('' !== $name) {
            return $name;
        }

        $name = trim((string) ($auftrag?->getKunde()?->getFirmenname() ?? ''));
        if ('' !== $name) {
            return $name;
        }

        return 'Firma';
    }

    private function buildCompanyInitials(string $companyName): string
    {
        $normalized = trim($companyName);
        if ('' === $normalized) {
            return 'F';
        }

        $parts = preg_split('/[\s\-_]+/u', $normalized) ?: [];
        $letters = [];

        foreach ($parts as $part) {
            $value = trim((string) $part);
            if ('' === $value) {
                continue;
            }

            if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
                $letters[] = mb_strtoupper(mb_substr($value, 0, 1));
            } else {
                $letters[] = strtoupper(substr($value, 0, 1));
            }

            if (count($letters) >= 2) {
                break;
            }
        }

        if ([] !== $letters) {
            return implode('', $letters);
        }

        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            return mb_strtoupper(mb_substr($normalized, 0, 1));
        }

        return strtoupper(substr($normalized, 0, 1));
    }

    private function truncateWithEllipsis(string $value, int $maxLength): string
    {
        if ($maxLength < 4) {
            return $value;
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($value) <= $maxLength) {
                return $value;
            }

            return rtrim(mb_substr($value, 0, $maxLength - 3)) . '...';
        }

        if (strlen($value) <= $maxLength) {
            return $value;
        }

        return rtrim(substr($value, 0, $maxLength - 3)) . '...';
    }

    private function formatFileSizeInMb(?int $bytes): string
    {
        if (null === $bytes || $bytes <= 0) {
            return '0.00';
        }

        return number_format($bytes / (1024 * 1024), 2, '.', '');
    }
}
