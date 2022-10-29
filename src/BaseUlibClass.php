<?php

namespace Ulib\Grabber;

use DOMDocument;
use DOMXPath;
use Ulib\Grabber\Exception\UlibException;

class BaseUlibClass
{
    protected $baseUrl;
     
    protected $proxy;
     
    private $content;

    /**
     * @throws UlibException
     */
    public function __construct(array $queryParams = [], string $proxy = null)
    {
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
            throw new UlibException(curl_error($ch), curl_errno($ch));
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
