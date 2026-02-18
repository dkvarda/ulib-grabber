<?php

declare(strict_types=1);

namespace Ulib\Grabber\Parser;

use DOMElement;
use DOMNode;
use DOMXPath;
use Ulib\Grabber\Entity\User;
use Ulib\Grabber\Hydrator\Hydrator;

final class PhoneDirectoryParser implements PhoneDirectoryParserInterface
{
    private const XPATH_USER_ROWS = "//tbody/tr[contains(@class, 'odd') or contains(@class, 'even')]";
    private const XPATH_PAGE_BANNER = "//span[@class='pagebanner']";
    private const XPATH_PAGINATOR_ITEMS = "//div/p[@class='right']//a|//div/p[@class='right']//strong";
    private const XPATH_PAGINATOR_ACTIVE = "//div/p[@class='right']//strong";
    private const MIN_USER_COLUMNS = 6;

    private const USER_COLUMN_MAP = [
        0 => 'lastname',
        1 => 'firstname',
        2 => 'department',
        3 => 'room',
        4 => 'phone',
        5 => 'mail',
    ];

    public function __construct(private readonly Hydrator $hydrator = new Hydrator())
    {
    }

    /**
     * @return User[]
     */
    public function parseUsers(DOMXPath $xpath): array
    {
        $rows = $xpath->query(self::XPATH_USER_ROWS);
        if ($rows === false || $rows === null) {
            return [];
        }

        $users = [];
        foreach ($rows as $row) {
            if (!$row instanceof DOMElement) {
                continue;
            }

            $tdNodes = $xpath->query('./td', $row);
            if ($tdNodes === false || $tdNodes === null || $tdNodes->length < self::MIN_USER_COLUMNS) {
                continue;
            }

            $userData = [];
            foreach (self::USER_COLUMN_MAP as $index => $key) {
                $userData[$key] = $this->nodeText($tdNodes->item($index));
            }

            $users[] = $this->hydrator->patch(new User(), $userData);
        }

        return $users;
    }

    public function parsePageResult(DOMXPath $xpath): ?string
    {
        $elements = $xpath->query(self::XPATH_PAGE_BANNER);
        if ($elements !== false && $elements !== null && $elements->item(0) instanceof DOMNode) {
            return trim($elements->item(0)->nodeValue ?? '');
        }

        return null;
    }

    /**
     * @return array{activePage?: int, pages?: int[]}
     */
    public function parsePaginator(DOMXPath $xpath): array
    {
        $elements = $xpath->query(self::XPATH_PAGINATOR_ITEMS);
        $elementsActive = $xpath->query(self::XPATH_PAGINATOR_ACTIVE);
        if ($elements === false || $elements === null || $elementsActive === false || $elementsActive === null) {
            return [];
        }

        $out = [];
        $pages = [];
        foreach ($elements as $element) {
            $value = trim($element->nodeValue ?? '');
            if (is_numeric($value)) {
                $pages[] = (int) $value;
            }
        }

        foreach ($elementsActive as $elementActive) {
            $value = trim($elementActive->nodeValue ?? '');
            if (is_numeric($value)) {
                $out['activePage'] = (int) $value;
                break;
            }
        }

        $out['pages'] = array_values(array_unique($pages));

        return $out;
    }

    private function nodeText(?DOMNode $node): string
    {
        return trim($node?->textContent ?? '');
    }
}
