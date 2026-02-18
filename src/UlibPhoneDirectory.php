<?php

declare(strict_types=1);

namespace Ulib\Grabber;

use DOMElement;
use DOMNode;
use Ulib\Grabber\Entity\User;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;

class UlibPhoneDirectory extends BaseUlibClass
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

    protected string $baseUrl = 'https://www.ulib.sk/sk/kontakty/telefonny-zoznam/';

    /**
     * @var array<string, string>
     */
    private array $urlReplace = [
        'mail' => 'fieldF',
        'firstname' => 'fieldB',
        'lastname' => 'fieldA',
        'department' => 'fieldC',
        'room' => 'fieldD',
        'phone' => 'fieldE',
        'page' => 'd-4082824-p',
        'sort' => 'd-4082824-o',
        'column' => 'd-4082824-s',
    ];

    protected array $allowedParams = [
        'sort' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1],
        ],
        'd-4082824-o' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1],
        ],
        'column' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1, 2, 3, 4, 5],
        ],
        'd-4082824-s' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1, 2, 3, 4, 5],
        ],
    ];

    /**
     * @throws UlibException
     * @throws ParamException
     */
    public function __construct(array $queryParams = [], ?string $proxy = null)
    {
        $this->validateParams($queryParams);
        parent::__construct($this->queryParamsReplace($queryParams), $proxy);
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        $xpath = $this->getXPath();
        $rows = $xpath->query(self::XPATH_USER_ROWS);
        $out = [];

        if ($rows === false || $rows === null) {
            return $out;
        }

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

            $out[] = $this->hydrator->patch(new User(), $userData);
        }

        return $out;
    }

    public function getPageResult(): ?string
    {
        $xpath = $this->getXPath();
        $elements = $xpath->query(self::XPATH_PAGE_BANNER);
        if ($elements !== false && $elements !== null && $elements->item(0) instanceof DOMNode) {
            return trim($elements->item(0)->nodeValue ?? '');
        }

        return null;
    }

    /**
     * @return array{activePage?: int, pages?: int[]}
     */
    public function getPaginator(): array
    {
        $xpath = $this->getXPath();
        $elements = $xpath->query(self::XPATH_PAGINATOR_ITEMS);
        $elementsActive = $xpath->query(self::XPATH_PAGINATOR_ACTIVE);
        $out = [];

        if ($elements === false || $elements === null || $elementsActive === false || $elementsActive === null) {
            return $out;
        }

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

    private function queryParamsReplace(array $queryParams): array
    {
        $out = [];
        foreach ($queryParams as $key => $value) {
            $out[$this->urlReplace[$key] ?? $key] = $value;
        }

        return $out;
    }

    /**
     * @throws ParamException
     */
    private function validateParams(array $params): void
    {
        $this->allowedParams($params);
        $allowedKeys = array_flip(array_merge(array_keys($this->urlReplace), array_values($this->urlReplace)));

        foreach ($params as $key => $_param) {
            if (!array_key_exists((string) $key, $allowedKeys)) {
                throw new ParamException('Not supported query parameter: ' . $key, 400);
            }
        }
    }

    private function nodeText(?DOMNode $node): string
    {
        return trim($node?->textContent ?? '');
    }
}
