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

//        $store = $storeManager->getStore();
//        $storeId = $store->getStoreId();
    }

    public function create()
    {
        try {

            $data = json_decode(file_get_contents('php://input'), true);
            $customer = $this->customerFactory->create();
            $websiteId = $this->_storeManager->getWebsite()->getWebsiteId();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($data['data']['email']);

            if ($customer->getEmail()) {
                throw new Exception("User with this email already exists!");
            }

            if ($data['data']['gender'] === 'M') {
                $gender = 1;
            } else {
                $gender = 2;
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

            $url = $_SERVER['REQUEST_URI'];
            $urlArrayInParts = explode('/', $url);
            $inputData = json_decode(file_get_contents('php://input'), true);

            if (!$this->customerRepository->getById($urlArrayInParts[5])) {
                throw new \Exception ("User with this id does not exists!");
            } else {
                $customerData = $this->customerRepository->getById($urlArrayInParts[5]);
            }

            if ($inputData['data']['gender'] === 'M') {
                $gender = 1;
            } else {
                $gender = 2;
            }

            $addressId = $customerData->getDefaultBilling();
            $street[] = $inputData['data']['streetAddress'];

            if (isset($inputData['data']['firstName'])) {

                $customer = $this->customerRepository->getById($urlArrayInParts[5]);
                $customer->setFirstname($inputData['data']['firstName']);
                $this->customerRepository->save($customer);

                $address = $this->addressRepository->getById($addressId);
                $address->setFirstname($inputData['data']['firstName']);
                $this->addressRepository->save($address);
            }

            if (isset($inputData['data']['lastName'])) {

                $customer = $this->customerRepository->getById($urlArrayInParts[5]);
                $customer->setLastname($inputData['data']['lastName']);
                $this->customerRepository->save($customer);

                $address = $this->addressRepository->getById($addressId);
                $address->setLastname($inputData['data']['lastName']);
                $this->addressRepository->save($address);
            }

            if (isset($inputData['data']['birthDate'])) {

                $customer = $this->customerRepository->getById($urlArrayInParts[5]);
                $customer->setDob($inputData['data']['birthDate']);
                $this->customerRepository->save($customer);
            }

            if (isset($inputData['data']['email'])) {

                $customer = $this->customerRepository->getById($urlArrayInParts[5]);
                $customer->setEmail($inputData['data']['email']);
                $this->customerRepository->save($customer);
            }

            if (isset($inputData['data']['gender'])) {

                $customer = $this->customerRepository->getById($urlArrayInParts[5]);
                $customer->setGender($gender);
                $this->customerRepository->save($customer);
            }

            if (isset($inputData['data']['zipCode'])) {

                $address = $this->addressRepository->getById($addressId);
                $address->setPostcode($inputData['data']['zipCode'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($inputData['data']['city'])) {

                $address = $this->addressRepository->getById($addressId);
                $address->setCity($inputData['data']['city'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($inputData['data']['msisdn'])) {


                $address = $this->addressRepository->getById($addressId);
                $address->setTelephone($inputData['data']['msisdn'])
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            if (isset($inputData['data']['streetAddress'])) {

                $address = $this->addressRepository->getById($addressId);
                $address->setStreet($street)
                    ->setCountryId('NO')
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1');
                $this->addressRepository->save($address);
            }

            $this->response->setHttpResponseCode(200);
            $this->response->setBody($urlArrayInParts[5]);
            $this->response->setStatusHeader(200, '1.1', 'User updated successfully');
            return $urlArrayInParts[5];

        } catch (\Exception $e) {

            $this->response->setHttpResponseCode(409);
            $this->response->setBody('User with this id does not exist!');
            $this->response->setStatusHeader(409, '1.1', 'Error');
        }
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }
}