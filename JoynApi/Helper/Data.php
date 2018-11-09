<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.8
 * Time: 14.26
 */

namespace Trollweb\JoynApi\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /*
     * @return bool
     */
    public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            'trollweb/general/enabled',
            $scope
        );
    }

    /*
     * @return string
     */
    public function getApiKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'trollweb/general/apikey',
            $scope
        );
    }

    /*
     * @return string
     */
    public function getPartnerId($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'trollweb/general/partnerId',
            $scope
        );
    }

    /*
     * @return string
     */
    public function getSecretKey($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'trollweb/general/secretKey',
            $scope
        );
    }

    /*
     * @return string
     */
//    public function getOption($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
//    {
//        return $this->scopeConfig->getValue(
//            'inviqa/general/option',
//            $scope
//        );
//    }
}