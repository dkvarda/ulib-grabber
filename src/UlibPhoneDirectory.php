<?php

namespace Ulib\Grabber;

use Ulib\Grabber\Entity\User;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;

class UlibPhoneDirectory extends BaseUlibClass
{
    protected $baseUrl = 'https://www.ulib.sk/sk/kontakty/telefonny-zoznam/';

    private $urlReplace = [
        'mail' => 'fieldF',
        'firstname' => 'fieldB',
        'lastname' => 'fieldA',
        'department' => 'fieldC',
        'room' => 'fieldD',
        'phone' => 'fieldE',
        'page' => 'd-4082824-p',
        'sort' => 'd-4082824-o',
        'column' => 'd-4082824-s'
    ];

    protected $allowedParams = [
        'sort' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1]
        ],
        'd-4082824-o' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1]
        ],
        'column' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1, 2, 3, 4, 5]
        ],
        'd-4082824-s' => [
            'type' => Constants::TYPE_INT,
            'values' => [0, 1, 2, 3, 4, 5]
        ]
    ];

    /**
     * @throws UlibException
     * @throws ParamException
     */
    public function __construct(array $queryParams = [], string $proxy = null)
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
        $xpathQuery="//tbody/tr[@class='odd']|//tbody/tr[@class='even']";
        $elements = $xpath->query($xpathQuery);
        $out = [];
        if (!is_null($elements)) {
            foreach ($elements as $element) {
                $parts = preg_split('/[\r\n]/', $element->nodeValue);
                $out[] = $this->hydrator->patch(new User(), [
                    'firstname' => trim($parts[7]),
                    'lastname' => trim($parts[3]),
                    'department' => trim($parts[11]),
                    'room' => trim($parts[15]),
                    'phone' => trim($parts[19]),
                    'mail' => trim($parts[23])
                ]);
            }
        }
        return $out;
    }

    public function getPageResult()
    {
        $xpath = $this->getXPath();
        $xpathQuery="//span[@class='pagebanner']";
        $elements = $xpath->query($xpathQuery);
        if (!is_null($elements)) {
            foreach ($elements as $element) {
                return $element->nodeValue;
            }
        }
        return null;
    }

    public function getPaginator(): array
    {
        $xpath = $this->getXPath();
        $xpathQuery="//div/p[@class='right']//a|//div/p[@class='right']//strong";
        $xpathQueryActive="//div/p[@class='right']//strong";
        $elements = $xpath->query($xpathQuery);
        $elementsActive = $xpath->query($xpathQueryActive);
        $out = [];
        if (!is_null($elements) && !is_null($elementsActive)) {
            $pages = [];
            foreach ($elements as $element) {
                if (is_numeric($element->nodeValue)) {
                    $pages[] = (int)$element->nodeValue;
                }
            }
            foreach ($elementsActive as $elementActive) {
                if (is_numeric($elementActive->nodeValue)) {
                    $out['activePage'] = (int)$elementActive->nodeValue;
                }
            }
            $out['pages'] = $pages;
        }
        return $out;
    }

    /**
     * @param array $queryParams
     * @return array
     */
    private function queryParamsReplace(array $queryParams): array
    {
        $out = [];
        foreach ($queryParams as $key => $value) {
            if (array_key_exists($key, $this->urlReplace)) {
                $out[$this->urlReplace[$key]] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    /**
     * @param array $params
     * @return void
     * @throws ParamException
     */
    private function validateParams(array $params)
    {
        $this->allowedParams($params);
        $array = array_merge(array_keys($this->urlReplace), array_values($this->urlReplace));
        foreach ($params as $key => $param) {
            if (!in_array($key, $array)) {
                throw new ParamException('Not supported query parameter: ' . $key, 400);
            }
        }
    }
}
