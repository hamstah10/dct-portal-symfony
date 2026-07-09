<?php

namespace App\Service\Lexware;

final class LexwareInvoicesService
{
    public function __construct(
        private readonly LexwareApiClient $client,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     *
     * @return array<string, mixed>
     */
    public function list(array $query = []): array
    {
        return $this->client->requestJson('GET', '/v1/invoices', ['query' => $query]);
    }

    /**
     * @return array<string, mixed>
     */
    public function get(string $id): array
    {
        return $this->client->requestJson('GET', sprintf('/v1/invoices/%s', $id));
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function create(array $payload, bool $finalize = false): array
    {
        $options = ['json' => $payload];
        if ($finalize) {
            $options['query'] = ['finalize' => 'true'];
        }

        return $this->client->requestJson('POST', '/v1/invoices', $options);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    public function update(string $id, array $payload): array
    {
        return $this->client->requestJson('PUT', sprintf('/v1/invoices/%s', $id), ['json' => $payload]);
    }

    /**
     * @return array{content:string,contentType:?string,filename:?string}
     */
    public function downloadFile(string $id, string $accept = 'application/pdf'): array
    {
        $response = $this->client->requestRaw('GET', sprintf('/v1/invoices/%s/file', $id), [
            'headers' => [
                'Accept' => $accept,
            ],
        ]);

        $content = $response->getContent(false);
        $headers = $response->getHeaders(false);

        return [
            'content' => $content,
            'contentType' => $this->getHeaderValue($headers, 'content-type'),
            'filename' => $this->extractFilename($headers),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function renderDocument(string $id): array
    {
        return $this->client->requestJson('GET', sprintf('/v1/invoices/%s/document', $id), [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Markiert eine Rechnung in Lexoffice als bezahlt.
     *
     * Da Lexoffice je nach API-Version unterschiedliche Endpunkte nutzt,
     * werden mehrere gaengige Varianten nacheinander versucht.
     *
     * @return array{endpoint:string,details:array<string,mixed>}
     */
    public function markAsPaid(string $id, ?\DateTimeInterface $paidAt = null): array
    {
        $invoiceId = trim($id);
        if ('' === $invoiceId) {
            throw new \RuntimeException('Lexoffice Invoice-ID fehlt.');
        }

        $paidAt ??= new \DateTimeImmutable();
        $paidDate = $paidAt->format('Y-m-d');
        $paidAtIso = $paidAt->format('Y-m-d\TH:i:sP');

        $attempts = [
            ['POST', sprintf('/v1/invoices/%s/state/paid', $invoiceId), ['json' => ['paidDate' => $paidDate, 'paidAt' => $paidAtIso]]],
            ['POST', sprintf('/v1/invoices/%s/paid', $invoiceId), ['json' => ['paidDate' => $paidDate, 'paidAt' => $paidAtIso]]],
            ['POST', sprintf('/v1/invoices/%s/payment', $invoiceId), ['json' => ['paymentDate' => $paidDate, 'paidAt' => $paidAtIso]]],
            ['PUT', sprintf('/v1/invoices/%s/state', $invoiceId), ['json' => ['status' => 'paid', 'paidDate' => $paidDate]]],
        ];

        $lastException = null;

        foreach ($attempts as [$method, $path, $options]) {
            try {
                $this->client->requestRaw($method, $path, $options);
                $details = $this->get($invoiceId);

                return [
                    'endpoint' => $path,
                    'details' => $details,
                ];
            } catch (LexwareApiException $exception) {
                $statusCode = $exception->getStatusCode();
                if (in_array($statusCode, [400, 404, 405, 415, 422], true)) {
                    $lastException = $exception;
                    continue;
                }

                throw $exception;
            }
        }

        if ($lastException instanceof LexwareApiException) {
            throw $lastException;
        }

        throw new \RuntimeException('Lexoffice Status-Update auf "paid" ist fehlgeschlagen.');
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function getHeaderValue(array $headers, string $name): ?string
    {
        $key = strtolower($name);
        foreach ($headers as $headerName => $values) {
            if (strtolower($headerName) === $key) {
                return $values[0] ?? null;
            }
        }

        return null;
    }

    /**
     * @param array<string, array<int, string>> $headers
     */
    private function extractFilename(array $headers): ?string
    {
        $disposition = $this->getHeaderValue($headers, 'content-disposition');
        if (null === $disposition) {
            return null;
        }

        if (preg_match("/filename\\*=UTF-8''([^;]+)/i", $disposition, $matches)) {
            return rawurldecode($matches[1]);
        }

        if (preg_match('/filename=\"?([^\";]+)\"?/i', $disposition, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
