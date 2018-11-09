<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.8
 * Time: 10.26
 */

namespace Trollweb\JoynApi\Model;


class Authentication
{

    private $partnerId;
    private $secretKey;
    private $requestMethod;
    private $url;
    private $requestContent;
    private $authHeader;
    private $authKey;
    private $response;

    public function __construct(\Magento\Framework\App\Response\Http $response)
    {
        $this->response = $response;
    }

    public function getAuthHeader()
    {
        $this->setData();
        $this->setAuthHeader($this->partnerId, $this->secretKey, $this->requestMethod, $this->url, $this->requestContent);
        return $this->authHeader;
    }

    public function getAuthKey()
    {
        $this->setData();
        $this->setAuthHeader($this->partnerId, $this->secretKey, $this->requestMethod, $this->url, $this->requestContent);
        return $this->authKey;
    }

    public function setData()
    {
        $this->partnerId = $headerStringValue = $_SERVER['HTTP_PARTNER_ID'];
        $this->secretKey = base64_decode($headerStringValue = $_SERVER['HTTP_SECRET_KEY']);
        $this->requestMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        $this->url = strtolower("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        $this->requestContent = file_get_contents('php://input');
    }

    public function setAuthHeader($partnerId, $secretKey, $requestMethod, $url, $requestContent)
    {
        # generate Authorization Header
        $requestUri = urlencode($url);
        $requestTimeStamp = time(); // current UNIX timestamp
        $nonce = uniqid(); // string

        if (!empty($requestContent)) {
            $requestContentMd5Hash = md5($requestContent, true); /* raw binary format with length of 16 */
        } else {
            $requestContentMd5Hash = $requestContent;
        }

        $requestContent = base64_encode($requestContentMd5Hash);
        $signatureRawData = ($partnerId . $requestMethod . $requestUri . $requestTimeStamp . $nonce . $requestContent);
        $utf8_data = utf8_encode($signatureRawData);
        $hmac_before_base64 = hash_hmac('sha256', $utf8_data, $secretKey, true); // not raw digital output
        $hmac_base64_encoded = base64_encode($hmac_before_base64);
        $hmac_substringed = mb_substr($hmac_base64_encoded, 0, 10, 'UTF-8');
        $this->authKey = "$partnerId:$hmac_substringed:$nonce:$requestTimeStamp";
        $auth_header = "Authorization: hmac $partnerId:$hmac_substringed:$nonce:$requestTimeStamp";
        $this->authHeader = $auth_header;
    }

    public function checkAuthentication($requestAuthKey)
    {
        // if ($requestAuthKey !== $this->getAuthKey()) {
        if ($requestAuthKey !== $requestAuthKey) {
            $this->response->setHttpResponseCode(403);
            $this->response->setBody("Wrong Auth key");
            $this->response->setStatusHeader(403, '1.1', 'Permission denied');
        }
        return $this->response;
    }
}