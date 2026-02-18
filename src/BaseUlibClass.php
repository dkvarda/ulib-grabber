<?php

declare(strict_types=1);

namespace Ulib\Grabber;

use DOMDocument;
use DOMXPath;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;
use Ulib\Grabber\Http\CurlHttpClient;
use Ulib\Grabber\Http\HttpClientInterface;
use Ulib\Grabber\Hydrator\Hydrator;

class BaseUlibClass
{
    protected string $baseUrl = '';

    protected ?string $proxy;

    private string $content = '';
    private ?DOMXPath $xpath = null;

    /**
     * @var array<string, array{type?: string, values?: array<int, int|string>}>
     */
    protected array $allowedParams = [];

    public Hydrator $hydrator;
    protected HttpClientInterface $httpClient;

    /**
     * @throws UlibException
     */
    public function __construct(
        array $queryParams = [],
        ?string $proxy = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient ?? new CurlHttpClient();
        $this->hydrator = new Hydrator();
        $this->proxy = $proxy;
        $this->setContent($this->httpClient->get($this->getUrlWithParameters($queryParams), $this->proxy));
    }

    protected function getContent(): string
    {
        return $this->content;
    }

    protected function setContent(string $data): void
    {
        $this->content = $data;
        $this->xpath = null;
    }

    protected function getXPath(): DOMXPath
    {
        if ($this->xpath instanceof DOMXPath) {
            return $this->xpath;
        }

        $doc = new DOMDocument();
        $doc->loadHTML($this->getContent(), LIBXML_NOWARNING | LIBXML_NOERROR);

        $this->xpath = new DOMXPath($doc);

        return $this->xpath;
    }

    /**
     * @throws ParamException
     */
    protected function allowedParams(array $params): void
    {
        foreach ($params as $key => $param) {
            if (!array_key_exists($key, $this->allowedParams)) {
                continue;
            }

            $rule = $this->allowedParams[$key];
            if (!array_key_exists('values', $rule)) {
                continue;
            }

            $value = $this->normalizeParamValue($param, $rule['type'] ?? null);
            if (!in_array($value, $rule['values'], true)) {
                throw new ParamException(
                    'Query parameter ' . $key . ' has invalid value. Allowed: ' . implode(', ', $rule['values']),
                    400
                );
            }
        }
    }

    private function getUrlWithParameters(array $queryParams = []): string
    {
        $queryUrl = http_build_query($queryParams);

        return $this->getBaseUrl() . ($queryUrl !== '' ? '?' . $queryUrl : '');
    }

    private function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    private function normalizeParamValue(mixed $value, ?string $type): mixed
    {
        if ($type === Constants::TYPE_INT && is_numeric($value)) {
            return (int) $value;
        }

        if ($type === Constants::TYPE_STRING) {
            return (string) $value;
        }

        return $value;
    }
}
