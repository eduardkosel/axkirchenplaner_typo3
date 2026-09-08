<?php

declare(strict_types=1);

namespace Axist\AxKirchenplaner\Hook;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;

final class AddCustomFieldsToFlexFormHook
{
    public function __construct(
        private readonly ExtensionConfiguration $extensionConfiguration,
        private readonly RequestFactory $requestFactory,
    ) {}

    public function addTopics(array &$config): void
    {
        $config['items'] = array_merge($config['items'] ?? [], $this->loadPipeSeparatedItems('/api/themen'));
    }

    public function addTargetAudiences(array &$config): void
    {
        $config['items'] = array_merge($config['items'] ?? [], $this->loadPipeSeparatedItems('/api/zielgruppen'));
    }

    public function addLocations(array &$config): void
    {
        $config['items'] = array_merge($config['items'] ?? [], $this->loadJsonItems('/api/raeume/'));
    }

    public function addCommunities(array &$config): void
    {
        $config['items'] = array_merge($config['items'] ?? [], $this->loadJsonItems('/api/organisationen/'));
    }

    /** @return list<array{label: string, value: string}> */
    private function loadPipeSeparatedItems(string $path): array
    {
        $body = $this->request($path);
        if ($body === null) {
            return [];
        }

        $items = [];
        foreach (preg_split('/\R/', html_entity_decode($body)) ?: [] as $line) {
            [$label, $value] = array_pad(explode('|', trim($line), 2), 2, '');
            if ($label !== '' && $value !== '') {
                $items[] = ['label' => $label, 'value' => $value];
            }
        }
        return $items;
    }

    /** @return list<array{label: string, value: string}> */
    private function loadJsonItems(string $path): array
    {
        $configuration = $this->getConfiguration();
        $apiKey = (string)($configuration['apiKey'] ?? '');
        if ($apiKey === '') {
            return [];
        }

        $payload = rawurlencode(json_encode(['key' => $apiKey], JSON_THROW_ON_ERROR));
        $body = $this->request($path . $payload);
        $rows = $body !== null ? json_decode(html_entity_decode($body), true) : null;
        if (!is_array($rows)) {
            return [];
        }

        $items = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row[0], $row[1])) {
                $label = (string)$row[0];
                $value = (string)$row[1];
                $items[] = ['label' => $label . ' (' . $value . ')', 'value' => $value];
            }
        }
        return $items;
    }

    private function request(string $path): ?string
    {
        $apiUrl = rtrim((string)($this->getConfiguration()['apiUrl'] ?? ''), '/');
        if ($apiUrl === '') {
            return null;
        }

        try {
            $response = $this->requestFactory->request($apiUrl . $path, 'GET', ['timeout' => 10]);
            return $response->getStatusCode() === 200 ? (string)$response->getBody() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function getConfiguration(): array
    {
        return $this->extensionConfiguration->get('axkirchenplaner');
    }
}
