<?php
/**
 * Created by PhpStorm.
 * User: norbertas
 * Date: 18.11.8
 * Time: 16.16
 */

namespace Trollweb\JoynApi\Cron;


class Run
{
    protected $addressFactory;
    protected $customerFactory;

    public function __construct(
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
    }

    public function execute()
    {

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-24 hours', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $customerCollection = $this->customerFactory->create()->getCollection();
        $customerCollection->addFieldToSelect('*');
        $customerCollection->addFieldToFilter('updated_at', array('from' => $from));

        foreach ($customerCollection as $customer) {

            $addressId = $customer->getDefaultBilling();

            if ($customer->getGender() === '1') {
                $gender = 'M';
            } else {
                $gender = 'W';
            }

            $customerInfo['data']['externalId'] = $customer->getId();
            $customerInfo['data']['firstName'] = $customer->getFirstname();
            $customerInfo['data']['lastName'] = $customer->getLastname();
            $customerInfo['data']['email'] = $customer->getEmail();
            $customerInfo['data']['birthDate'] = $customer->getDob();
            $customerInfo['data']['gender'] = $gender;

            $customerAddressCollection = $this->addressFactory->create()->load($addressId);
            $customerInfo['data']['msisdn'] = $customerAddressCollection->getTelephone();
            $customerInfo['data']['streetAddress'] = $customerAddressCollection->getStreetFull();
            $customerInfo['data']['city'] = $customerAddressCollection->getCity();
            $customerInfo['data']['zipCode'] = $customerAddressCollection->getPostcode();

            $jsonCustomerInfo = json_encode($customerInfo);
            $this->makeRequest($jsonCustomerInfo);
        }
    }

    public function makeRequest($jsonCustomerInfo){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://your.url/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonCustomerInfo);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return false;
        } else {
            return $result;
        }
    }
}

