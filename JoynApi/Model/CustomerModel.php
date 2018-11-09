<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.6
 * Time: 10.19
 */

namespace Trollweb\JoynApi\Model;

use Magento\Setup\Exception;
use Trollweb\JoynApi\Api\CustomerInterface;

class CustomerModel implements CustomerInterface
{
    protected $_storeManager;
    protected $customerRepository;
    protected $customerFactory;
    protected $addressFactory;
    protected $objectManager;
    protected $response;
    protected $addressRepository;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    )
    {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->addressFactory = $addressFactory;
        $this->response = $response;
        $this->addressRepository = $addressRepository;

        $store = $storeManager->getStore();
        $storeId = $store->getStoreId();
    }

    public function create()
    {
        try {

            $data = json_decode(file_get_contents('php://input'), true);

            if ($data['data']['gender'] === 'M') {
                $gender = 1;
            } else {
                $gender = 2;
            }

            $customer = $this->customerFactory->create();


            $websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($data['data']['email']);

            if ($customer->getEmail()) {

                throw new Exception("User with this email already exists!");

            }

            $customer->setWebsiteId($websiteId)
                ->setEmail($data['data']['email'])
                ->setFirstname($data['data']['firstName'])
                ->setLastname($data['data']['lastName'])
                ->setGender($gender)
                ->setDob($data['data']['birthDate']);


            $customer->save();

            $address = $this->addressFactory->create();

            $address->setCustomerId($customer->getId())
                ->setFirstname($data['data']['firstName'])
                ->setLastname($data['data']['lastName'])
                ->setPostcode($data['data']['zipCode'])
                ->setCity($data['data']['city'])
                ->setTelephone($data['data']['msisdn'])
                ->setStreet($data['data']['streetAddress'])
                ->setCountryId('NO')
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');


            $address->save();

            $this->response->setHttpResponseCode(201);
            $this->response->setBody($customer->getId());
            $this->response->setStatusHeader(201, '1.1', 'User created successfully');
            return $customer->getId();

        } catch (Exception $e) {

            $this->response->setHttpResponseCode(409);
            $this->response->setBody("User with this email already exists!");
            $this->response->setStatusHeader(409, '1.1', 'Error');
        }
    }

    public function update()
    {

        try {

            $queries = $_SERVER['REQUEST_URI'];
            $sanitize = preg_replace('/[?&=]/', '/', $queries);
            $urlArray = explode('/', $sanitize);

            $data = json_decode(file_get_contents('php://input'), true);

            $street[] = $data['data']['streetAddress'];

            if ($data['data']['gender'] === 'M') {
                $gender = 1;
            } else {
                $gender = 2;
            }

            if (!$this->customerRepository->getById($urlArray[5])) {

                throw new \Exception("User with this id does not exists!");

            } else {
                $getData = $this->customerRepository->getById($urlArray[5]);
            }

            $adressId = $getData->getDefaultBilling();

            if (isset($data['data']['firstName'])) {

                $customer = $this->customerRepository->getById($urlArray[5]);
                $customer->setFirstname($data['data']['firstName']);
                $this->customerRepository->save($customer);

                $address = $this->addressRepository->getById($adressId);
                $address->setFirstname($data['data']['firstName']);
                $this->addressRepository->save($address);
            }

            if (isset($data['data']['lastName'])) {

                $customer = $this->customerRepository->getById($urlArray[5]);
                $customer->setLastname($data['data']['lastName']);
                $this->customerRepository->save($customer);

                $address = $this->addressRepository->getById($adressId);
                $address->setLastname($data['data']['lastName']);
                $this->addressRepository->save($address);
            }

            if (isset($data['data']['birthDate'])) {

                $customer = $this->customerRepository->getById($urlArray[5]);
                $customer->setDob($data['data']['birthDate']);
                $this->customerRepository->save($customer);
            }

            if (isset($data['data']['email'])) {

                $customer = $this->customerRepository->getById($urlArray[5]);
                $customer->setEmail($data['data']['email']);
                $this->customerRepository->save($customer);
            }

            if (isset($data['data']['gender'])) {

                $customer = $this->customerRepository->getById($urlArray[5]);
                $customer->setGender($gender);
                $this->customerRepository->save($customer);
            }

            if (isset($data['data']['zipCode'])) {

                $address = $this->addressRepository->getById($adressId);
                $address->setPostcode($data['data']['zipCode'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($data['data']['city'])) {

                $address = $this->addressRepository->getById($adressId);
                $address->setCity($data['data']['city'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($data['data']['msisdn'])) {


                $address = $this->addressRepository->getById($adressId);
                $address->setTelephone($data['data']['msisdn'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($data['data']['streetAddress'])) {

                $address = $this->addressRepository->getById($adressId);
                $address->setStreet($street)
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            $this->response->setHttpResponseCode(200);
            $this->response->setBody($urlArray[5]);
            $this->response->setStatusHeader(200, '1.1', 'User updated successfully');
            return $urlArray[5];

        } catch (\Exception $e) {

            $this->response->setHttpResponseCode(409);
            $this->response->setBody("User with this id does not exists!");
            $this->response->setStatusHeader(409, '1.1', 'Error');
        }
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}