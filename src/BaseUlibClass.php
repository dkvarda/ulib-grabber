<?php

namespace Ulib\Grabber;

use DOMDocument;
use DOMXPath;
use Ulib\Grabber\Exception\ParamException;
use Ulib\Grabber\Exception\UlibException;
use Ulib\Grabber\Hydrator\Hydrator;

class BaseUlibClass
{
    protected $baseUrl;
     
    protected $proxy;
     
    private $content;

    protected $allowedParams = [];
    
    public $hydrator;
    
    /**
     * @throws UlibException
     */
    public function __construct(array $queryParams = [], string $proxy = null)
    {
        $this->hydrator = new Hydrator();
        $this->proxy = $proxy;
        $this->setContent($this->getCurlData($this->createCurl($queryParams)));
    }
    
    protected function getContent()
    {
        return $this->content;
    }

    protected function setContent($data)
    {
        $this->content = $data;
    }

    /**
     * @throws UlibException
     */
    protected function getCurlData($ch)
    {
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new UlibException(curl_error($ch), 404);
        }
        curl_close($ch);
        return $result;
    }
    
    protected function getXPath(): DOMXPath
    {
        $doc = new DOMDocument();
        $doc->loadHTML($this->getContent(), LIBXML_NOWARNING | LIBXML_NOERROR);
        return new DOMXpath($doc);
    }

    /**
     * @param array $params
     * @return void
     * @throws ParamException
     */
    protected function allowedParams(array $params)
    {
        foreach ($params as $key => $param) {
            if (key_exists($key, $this->allowedParams)) {
                $data = $this->allowedParams[$key];
                if (key_exists('values', $data)) {
                    $values = $data['values'];
                    if (!in_array($param, $values)) {
                        throw new ParamException(
                            'Query parameter ' . $key . ' has invalid value. Allowed: ' . implode(', ', $values),
                            400
                        );
                    }
                }
            }
        }
    }

    private function createCurl(array $queryParams = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrlWithParameters($queryParams));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // RETURN SERVER RESPONSE
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // DON'T VERIFY SSL CERTIFICATE
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // DON'T VERIFY HOST NAME

        if (!empty($this->proxy)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy); // PROXY URL WITH PORT
        }
        
        return $ch;
    }
    
    private function getUrlWithParameters(array $queryParams = []): string
    {
        $queryUrl = http_build_query($queryParams);
        return $this->getBaseUrl() . (!empty($queryUrl) ? '?' . $queryUrl : '');
    }

    private function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
