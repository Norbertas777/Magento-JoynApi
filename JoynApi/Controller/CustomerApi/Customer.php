<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.6
 * Time: 15.35
 */

namespace Trollweb\JoynApi\Controller\CustomerApi;

use Trollweb\JoynApi\Model\Authentication;
use Trollweb\JoynApi\Model\CustomerModel;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Trollweb\JoynApi\Helper\Data;

class Customer extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    public $customer;
    public $authentication;
    public $response;
    public $helperData;
    public $collectionFactory;
    public $addressFactory;
    public $customerFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Helper\Context $contextt,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
    )
    {
        $this->customer = new CustomerModel($objectManager, $storeManager, $customerRepository, $customerFactory, $addressFactory, $response,$addressRepository);
        $this->authentication = new Authentication($response);
        $this->helperData = new Data($contextt);
        $this->response = $response;
        $this->collectionFactory = $collectionFactory;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
        return parent::__construct($context);
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        if ($_SERVER['REQUEST_METHOD']) {
//            $this->response = $this->authentication->checkAuthentication("1:UW+gQz95pt:5be40133e5a9c:1541669171");
            if ($this->helperData->getApiKey() === $_SERVER['HTTP_API_KEY']) {

                if ($_SERVER['REQUEST_METHOD'] == 'POST') {

                    $this->customer->create();

                    } elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {

                    $this->customer->update();

                } elseif ($_SERVER['REQUEST_METHOD'] == 'DELETE') {

                    $this->customer->delete();
                }

            } else {
                $this->response->setHttpResponseCode(403);
                $this->response->setBody("Rejected: Api-Key does not match!");
                $this->response->setStatusHeader(403, '1.1', 'Rejected');
                return $this->response;
            }
        }
    }
}

