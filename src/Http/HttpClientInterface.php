<?php

declare(strict_types=1);

namespace Ulib\Grabber\Http;

use Ulib\Grabber\Exception\UlibException;

interface HttpClientInterface
{
    /**
     * @throws UlibException
     */
    public function get(string $url, ?string $proxy = null): string;
}
