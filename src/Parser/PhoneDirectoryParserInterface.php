<?php

declare(strict_types=1);

namespace Ulib\Grabber\Parser;

use DOMXPath;
use Ulib\Grabber\Entity\User;

interface PhoneDirectoryParserInterface
{
    /**
     * @return User[]
     */
    public function parseUsers(DOMXPath $xpath): array;

    public function parsePageResult(DOMXPath $xpath): ?string;

    /**
     * @return array{activePage?: int, pages?: int[]}
     */
    public function parsePaginator(DOMXPath $xpath): array;
}
