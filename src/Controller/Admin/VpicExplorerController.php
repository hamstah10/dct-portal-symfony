<?php

namespace App\Controller\Admin;

use App\Service\VpicApiService;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class VpicExplorerController extends AbstractController
{
    public function __construct(
        private readonly VpicApiService $vpicApiService,
    ) {
    }

    #[AdminRoute(path: '/vpic', name: 'vpic_explorer', options: ['methods' => ['GET', 'POST']])]
    public function index(Request $request): Response
    {
        $endpointGroups = $this->vpicApiService->getEndpointGroups();
        $result = null;
        $errors = [];
        $submitted = null;
        $activeGroupIndex = null;

        if ($request->isMethod('POST')) {
            $csrfToken = (string) $request->request->get('_csrf_token');
            if (!$this->isCsrfTokenValid('admin_vpic_explorer', $csrfToken)) {
                $errors[] = 'Ungültiges Formular-Token.';
            } else {
                $endpointId = trim((string) $request->request->get('endpoint_id', ''));
                $endpointInfo = '' !== $endpointId ? $this->vpicApiService->findEndpointWithGroup($endpointId) : null;
                $endpoint = $endpointInfo['endpoint'] ?? null;
                $activeGroupIndex = $endpointInfo['groupIndex'] ?? null;

                if (null === $endpoint) {
                    $errors[] = 'Endpoint nicht gefunden.';
                } else {
                    $fields = (array) $request->request->all('fields');
                    $query = (array) $request->request->all('query');
                    $format = trim((string) ($query['format'] ?? 'json'));
                    $format = '' !== $format ? $format : 'json';
                    $maxChars = (int) ($query['max_chars'] ?? 0);
                    if ($maxChars < 0) {
                        $maxChars = 0;
                    }
                    unset($query['max_chars']);

                    $pathParams = [];
                    foreach ($endpoint['pathParams'] as $param) {
                        $name = (string) ($param['name'] ?? '');
                        if ('' === $name) {
                            continue;
                        }
                        $value = $fields[$name] ?? '';
                        $pathParams[$name] = $value;
                        if (($param['required'] ?? false) && '' === trim((string) $value)) {
                            $errors[] = sprintf('Pflichtfeld fehlt: %s', $param['label'] ?? $name);
                        }
                    }

                    foreach ($endpoint['queryParams'] as $param) {
                        $name = (string) ($param['name'] ?? '');
                        if ('' === $name) {
                            continue;
                        }
                        if (($param['required'] ?? false) && '' === trim((string) ($query[$name] ?? ''))) {
                            $errors[] = sprintf('Pflichtfeld fehlt: %s', $param['label'] ?? $name);
                        }
                    }

                    if ([] === $errors) {
                        $response = $this->vpicApiService->request(
                            (string) $endpoint['path'],
                            $pathParams,
                            $query,
                            $format
                        );
                        $raw = (string) ($response['raw'] ?? '');
                        $isJson = is_array($response['data']);
                        $body = $raw;
                        if ($isJson) {
                            $pretty = json_encode(
                                $response['data'],
                                \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES
                            );
                            if (false !== $pretty) {
                                $body = $pretty;
                            }
                        }
                        $fullLength = strlen($body);
                        $isTruncated = false;
                        if ($maxChars > 0 && $fullLength > $maxChars) {
                            $body = substr($body, 0, $maxChars) . "\n...\n[gekürzt]";
                            $isTruncated = true;
                        }

                        $result = [
                            'endpointId' => $endpoint['id'],
                            'endpointTitle' => $endpoint['title'],
                            'url' => $response['url'],
                            'status' => $response['status'],
                            'ok' => $response['ok'],
                            'contentType' => $response['contentType'],
                            'data' => $response['data'],
                            'raw' => $response['raw'],
                            'isJson' => $isJson,
                            'display' => [
                                'body' => $body,
                                'maxChars' => $maxChars,
                                'fullLength' => $fullLength,
                                'isTruncated' => $isTruncated,
                            ],
                            'error' => $response['error'],
                        ];
                    }

                    $submitted = [
                        'endpointId' => $endpoint['id'],
                        'fields' => $fields,
                        'query' => $query,
                        'maxChars' => $maxChars,
                    ];
                }
            }
        }

        return $this->render('admin/vpic_explorer/index.html.twig', [
            'endpointGroups' => $endpointGroups,
            'result' => $result,
            'errors' => $errors,
            'submitted' => $submitted,
            'activeGroupIndex' => $activeGroupIndex,
        ]);
    }
}
