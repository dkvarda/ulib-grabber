<?php

declare(strict_types=1);

namespace Ulib\Grabber;

use CurlHandle;
use DOMDocument;
use DOMXPath;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;
use Ulib\Grabber\Hydrator\Hydrator;

class BaseUlibClass
{
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const DEFAULT_TIMEOUT = 30;

    protected string $baseUrl = '';

    protected ?string $proxy;

    private string $content = '';
    private ?DOMXPath $xpath = null;

    /**
     * @var array<string, array{type?: string, values?: array<int, int|string>}>
     */
    protected array $allowedParams = [];

    public Hydrator $hydrator;

    /**
     * @throws UlibException
     */
    public function __construct(array $queryParams = [], ?string $proxy = null)
    {
        $this->hydrator = new Hydrator();
        $this->proxy = $proxy;
        $this->setContent($this->getCurlData($this->createCurl($queryParams)));
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

    /**
     * @throws UlibException
     */
    protected function getCurlData(CurlHandle $ch): string
    {
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new UlibException($error !== '' ? $error : 'Unable to fetch remote data.', 500);
        }

        curl_close($ch);

        return $result;
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

    /**
     * @throws UlibException
     */
    private function createCurl(array $queryParams = []): CurlHandle
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new UlibException('Unable to initialize cURL.', 500);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $this->getUrlWithParameters($queryParams),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
        ]);

        if ($this->proxy !== null && $this->proxy !== '') {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        }

        return $ch;
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
