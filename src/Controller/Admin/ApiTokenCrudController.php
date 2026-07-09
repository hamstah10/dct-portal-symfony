<?php

namespace App\Controller\Admin;

use App\Entity\ApiToken;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AdminCrud(routePath: '/api-tokens', routeName: 'admin_api_tokens')]
final class ApiTokenCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ApiToken::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('API-Token')
            ->setEntityLabelInPlural('API-Tokens')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $testApiAction = Action::new('testApi', 'API testen')
            ->setIcon('iconoir:flask-solid')
            ->linkToRoute('admin_api_tokens_test_api', static function (ApiToken $token): array {
                return ['id' => $token->getId()];
            })
            ->displayIf(static fn (ApiToken $token): bool => null !== $token->getId());
        $clearTestOutputAction = Action::new('clearApiTestOutput', 'Ausgabe loeschen')
            ->setIcon('boxicons:trash')
            ->linkToRoute('admin_api_tokens_clear_test_output', static function (ApiToken $token): array {
                return ['id' => $token->getId()];
            })
            ->displayIf(static fn (ApiToken $token): bool => null !== $token->getId());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $testApiAction)
            ->add(Crud::PAGE_DETAIL, $testApiAction)
            ->add(Crud::PAGE_DETAIL, $clearTestOutputAction);
    }

    public function configureFields(string $pageName): iterable
    {
        $apiTokenFormField = TextField::new('apiToken', 'API Token')
            ->onlyOnForms()
            ->setRequired(false)
            ->setHelp('Leer lassen, um automatisch einen sicheren Token zu erzeugen.');

        $apiTokenIndexField = TextField::new('apiToken', 'API Token')
            ->hideOnForm()
            ->renderAsHtml()
            ->formatValue(fn (?string $value): string => $this->renderCopyableTokenValue($value));

        $apiTestResultField = TextareaField::new('apiToken', 'API-Test Ausgabe')
            ->onlyOnDetail()
            ->renderAsHtml()
            ->formatValue(fn (mixed $value, ?ApiToken $token): string => $this->renderApiTestResult($token));

        return [
            IdField::new('id')->onlyOnDetail(),
            TextField::new('applicationName', 'Name der Anwendung'),
            TextField::new('applicationLink', 'Link zur Anwendung')->setRequired(false)->onlyOnDetail(),
            $apiTokenFormField,
            $apiTokenIndexField,
            TextField::new('tokenIdentifier', 'Token-ID')
                ->hideOnForm()
                ->renderAsHtml()
                ->formatValue(fn (?string $value): string => $this->renderCopyableTokenValue($value))
                ->setHelp('Muss als X-Token-Id bei der Fahrzeug-API mitgegeben werden.'),
            BooleanField::new('active', 'Aktiv'),
            AssociationField::new('vehicleDataContext', 'Datenkontext')
                ->setRequired(false)
                ->setHelp('Optional: legt fest, welcher Fahrzeugdaten-Kontext standardmäßig für diesen Token gilt.'),
            AssociationField::new('kunde', 'Kunde')
                ->setRequired(false)
                ->setHelp('Optional: Token einem bestimmten Kunden zuordnen.'),
            TextField::new('publicKey', 'Stripe Public Key')
                ->setRequired(false)
                ->setHelp('Optional: Öffentlicher Key für Stripe-Zahlarten.'),
            TextField::new('secretKey', 'Stripe Secret Key')
                ->setRequired(false)
                ->setHelp('Optional: Geheimer Stripe Key.'),
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('gueltigBis', 'Gültig bis')
                ->setRequired(false)
                ->setHelp('Optionales Ablaufdatum fuer den Token.'),
            
            $apiTestResultField,
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->ensureToken($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $this->ensureToken($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    #[Route('/admin/api-tokens/{id}/test-api', name: 'admin_api_tokens_test_api', methods: ['GET'])]
    public function testVehicleApi(ApiToken $token, HttpClientInterface $httpClient, Request $request): RedirectResponse
    {
        $tokenValue = trim((string) $token->getApiToken());
        $tokenIdentifier = trim((string) $token->getTokenIdentifier());
        if ('' === $tokenValue || '' === $tokenIdentifier) {
            $this->storeApiTestResult($request, $token, [
                'testedAt' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                'success' => false,
                'statusCode' => 0,
                'endpoint' => '',
                'message' => 'Token oder Token-ID fehlt.',
                'payload' => null,
                'rawBody' => '',
            ]);
            $this->addFlash('danger', 'API-Test nicht möglich: Token oder Token-ID fehlt.');

            return $this->redirect($this->buildTokenDetailUrl($token));
        }

        $url = $this->generateUrl('api_vehicle_database_index', [], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            $response = $httpClient->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $tokenValue,
                    'X-Token-Id' => $tokenIdentifier,
                ],
                'timeout' => 20,
                'max_duration' => 25,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = (string) $response->getContent(false);
            $payload = $this->decodeJsonResponse($responseBody);
            $message = $this->extractApiResponseMessage($payload, $responseBody);
            $success = $statusCode >= 200 && $statusCode < 300;

            $this->storeApiTestResult($request, $token, [
                'testedAt' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                'success' => $success,
                'statusCode' => $statusCode,
                'endpoint' => $url,
                'message' => $message,
                'payload' => $payload,
                'rawBody' => $this->truncateValue($responseBody, 12000),
            ]);

            if ($success) {
                $this->addFlash(
                    'success',
                    sprintf(
                        'API-Test erfolgreich (HTTP %d)%s',
                        $statusCode,
                        '' !== $message ? ': ' . $message : '.'
                    )
                );
            } else {
                $this->addFlash(
                    'danger',
                    sprintf(
                        'API-Test fehlgeschlagen (HTTP %d)%s',
                        $statusCode,
                        '' !== $message ? ': ' . $message : '.'
                    )
                );
            }
        } catch (\Throwable $throwable) {
            $errorMessage = $this->truncateValue($throwable->getMessage(), 260);
            $this->storeApiTestResult($request, $token, [
                'testedAt' => (new \DateTimeImmutable())->format('d.m.Y H:i:s'),
                'success' => false,
                'statusCode' => 0,
                'endpoint' => $url,
                'message' => $errorMessage,
                'payload' => null,
                'rawBody' => '',
            ]);
            $this->addFlash(
                'danger',
                sprintf('API-Test fehlgeschlagen: %s', $errorMessage)
            );
        }

        return $this->redirect($this->buildTokenDetailUrl($token));
    }

    #[Route('/admin/api-tokens/{id}/clear-test-output', name: 'admin_api_tokens_clear_test_output', methods: ['GET'])]
    public function clearApiTestOutput(ApiToken $token, Request $request): RedirectResponse
    {
        if ($request->hasSession() && null !== $token->getId()) {
            $request->getSession()->remove($this->buildApiTestSessionKey((int) $token->getId()));
        }

        $this->addFlash('info', 'API-Testausgabe wurde gelöscht.');

        return $this->redirect($this->buildTokenDetailUrl($token));
    }

    private function ensureToken(object $entityInstance): void
    {
        if (!$entityInstance instanceof ApiToken) {
            return;
        }

        $token = trim((string) $entityInstance->getApiToken());
        if ('' !== $token) {
            $entityInstance->setApiToken($token);
        } else {
            $entityInstance->setApiToken(bin2hex(random_bytes(32)));
        }

        $tokenIdentifier = trim((string) $entityInstance->getTokenIdentifier());
        if ('' === $tokenIdentifier) {
            $entityInstance->setTokenIdentifier(bin2hex(random_bytes(16)));
        }
    }

    private function renderCopyableTokenValue(?string $value): string
    {
        $token = trim((string) $value);
        if ('' === $token) {
            return '<span class="text-muted">-</span>';
        }

        $escapedToken = htmlspecialchars($token, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<span class="d-inline-flex align-items-center gap-2"><code class="mb-0">%1$s</code><button type="button" class="btn btn-link p-0 text-primary" title="Token kopieren" aria-label="Token kopieren" data-copy-clipboard="%1$s"><i class="fa fa-copy fs-5" aria-hidden="true"></i></button><span class="small d-none" data-copy-feedback></span></span>',
            $escapedToken
        );
    }

    private function buildTokenDetailUrl(ApiToken $token): string
    {
        $tokenId = $token->getId();
        if (null === $tokenId) {
            return $this->buildTokenIndexUrl();
        }

        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId((string) $tokenId)
            ->generateUrl();
    }

    private function buildTokenIndexUrl(): string
    {
        return $this->adminUrlGenerator
            ->unsetAll()
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }

    /**
     * @param array<string, mixed>|null $decodedPayload
     */
    private function extractApiResponseMessage(?array $decodedPayload, string $rawBody): string
    {
        $message = trim((string) ($decodedPayload['message'] ?? ''));
        if ('' !== $message) {
            return $this->truncateValue($message, 180);
        }

        return $this->truncateValue(trim($rawBody), 180);
    }

    private function truncateValue(string $value, int $maxLength): string
    {
        if (mb_strlen($value) <= $maxLength) {
            return $value;
        }

        return mb_substr($value, 0, $maxLength) . '...';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeJsonResponse(string $rawBody): ?array
    {
        $trimmed = trim($rawBody);
        if ('' === $trimmed) {
            return null;
        }

        try {
            $decoded = json_decode($trimmed, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param array{
     *     testedAt?: string,
     *     success?: bool,
     *     statusCode?: int,
     *     endpoint?: string,
     *     message?: string,
     *     payload?: array<string, mixed>|null,
     *     rawBody?: string
     * } $result
     */
    private function storeApiTestResult(Request $request, ApiToken $token, array $result): void
    {
        if (!$request->hasSession()) {
            return;
        }

        $tokenId = $token->getId();
        if (null === $tokenId) {
            return;
        }

        $request->getSession()->set($this->buildApiTestSessionKey($tokenId), $result);
    }

    private function renderApiTestResult(?ApiToken $token): string
    {
        if (!$token instanceof ApiToken || null === $token->getId()) {
            return '<span class="text-muted">Noch kein API-Test vorhanden.</span>';
        }

        $request = $this->getContext()?->getRequest();
        if (null === $request || !$request->hasSession()) {
            return '<span class="text-muted">Noch kein API-Test vorhanden.</span>';
        }

        $result = $request->getSession()->get($this->buildApiTestSessionKey((int) $token->getId()));
        if (!is_array($result)) {
            return '<span class="text-muted">Noch kein API-Test ausgefuehrt. Klick auf "API testen".</span>';
        }

        $success = (bool) ($result['success'] ?? false);
        $badgeClass = $success ? 'bg-success' : 'bg-danger';
        $statusCode = (int) ($result['statusCode'] ?? 0);
        $statusText = $statusCode > 0 ? 'HTTP ' . $statusCode : 'Request Error';

        $testedAt = htmlspecialchars(trim((string) ($result['testedAt'] ?? '-')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $endpoint = htmlspecialchars(trim((string) ($result['endpoint'] ?? '-')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $message = htmlspecialchars(trim((string) ($result['message'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $payloadText = '';
        if (isset($result['payload']) && is_array($result['payload'])) {
            $json = json_encode($result['payload'], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
            if (false !== $json && '' !== $json) {
                $payloadText = $json;
            }
        }

        if ('' === $payloadText) {
            $payloadText = trim((string) ($result['rawBody'] ?? ''));
        }

        if ('' === $payloadText && '' !== trim((string) ($result['message'] ?? ''))) {
            $payloadText = (string) $result['message'];
        }

        if ('' === $payloadText) {
            $payloadText = '-';
        }

        $escapedPayload = htmlspecialchars($payloadText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return sprintf(
            '<div class="mb-2"><span class="badge %1$s">%2$s</span></div><div class="small text-muted mb-2">Getestet: %3$s<br>Endpoint: %4$s%5$s</div><pre style="white-space:pre-wrap;max-height:520px;overflow:auto;margin:0;background:#0f1621;color:#dce4ef;padding:12px;border-radius:0;border:1px solid #29384d;">%6$s</pre>',
            $badgeClass,
            htmlspecialchars($statusText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $testedAt,
            $endpoint,
            '' !== $message ? '<br>Info: ' . $message : '',
            $escapedPayload
        );
    }

    private function buildApiTestSessionKey(int $tokenId): string
    {
        return 'admin.api_token.test_result.' . $tokenId;
    }
}
