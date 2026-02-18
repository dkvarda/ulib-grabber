<?php

declare(strict_types=1);

namespace Ulib\Grabber;

use Ulib\Grabber\Entity\User;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;
use Ulib\Grabber\Http\HttpClientInterface;
use Ulib\Grabber\Parser\PhoneDirectoryParser;
use Ulib\Grabber\Parser\PhoneDirectoryParserInterface;

class UlibPhoneDirectory extends BaseUlibClass
{
    protected string $baseUrl = 'https://www.ulib.sk/sk/kontakty/telefonny-zoznam/';

    private PhoneDirectoryParserInterface $parser;

    /**
     * @var array<string, string>
     */
    private const URL_REPLACE = [
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
    public function __construct(
        array $queryParams = [],
        ?string $proxy = null,
        ?PhoneDirectoryParserInterface $parser = null,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->validateParams($queryParams);
        parent::__construct($this->queryParamsReplace($queryParams), $proxy, $httpClient);
        $this->parser = $parser ?? new PhoneDirectoryParser($this->hydrator);
    }

    /**
     * @return User[]
     */
    public function getUsers(): array
    {
        return $this->parser->parseUsers($this->getXPath());
    }

    public function getPageResult(): ?string
    {
        return $this->parser->parsePageResult($this->getXPath());
    }

    /**
     * @return array{activePage?: int, pages?: int[]}
     */
    public function getPaginator(): array
    {
        return $this->parser->parsePaginator($this->getXPath());
    }

    private function queryParamsReplace(array $queryParams): array
    {
        $out = [];
        foreach ($queryParams as $key => $value) {
            $out[self::URL_REPLACE[$key] ?? $key] = $value;
        }

        return $out;
    }

    /**
     * @throws ParamException
     */
    private function validateParams(array $params): void
    {
        $this->allowedParams($params);
        $allowedKeys = array_flip(array_merge(array_keys(self::URL_REPLACE), array_values(self::URL_REPLACE)));

        foreach ($params as $key => $_param) {
            if (!array_key_exists((string) $key, $allowedKeys)) {
                throw new ParamException('Not supported query parameter: ' . $key, 400);
            }
        }
    }
}
