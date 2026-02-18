<?php

declare(strict_types=1);

namespace Ulib\Grabber\Http;

use CurlHandle;
use Ulib\Grabber\Exception\UlibException;

final class CurlHttpClient implements HttpClientInterface
{
    private const DEFAULT_CONNECT_TIMEOUT = 10;
    private const DEFAULT_TIMEOUT = 30;

    /**
     * @throws UlibException
     */
    public function get(string $url, ?string $proxy = null): string
    {
        $ch = $this->createHandle($url, $proxy);
        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new UlibException($error !== '' ? $error : 'Unable to fetch remote data.', 500);
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @throws UlibException
     */
    private function createHandle(string $url, ?string $proxy): CurlHandle
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new UlibException('Unable to initialize cURL.', 500);
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => self::DEFAULT_CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::DEFAULT_TIMEOUT,
        ]);

        if ($proxy !== null && $proxy !== '') {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        return $ch;
    }
}
