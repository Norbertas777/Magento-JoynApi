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
    protected $_logger;
    protected $collectionFactory;
    protected $addressFactory;
    protected $customerFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    )
    {
        $this->_logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->addressFactory = $addressFactory;
        $this->customerFactory = $customerFactory;
    }

    public function execute()
    {

        $to = date("Y-m-d h:i:s");
        $from = strtotime('-24 hours', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $customerCollectionUpdatedAt = $this->customerFactory->create()->getCollection();
        $customerCollectionUpdatedAt->addFieldToSelect('*');
        $customerCollectionUpdatedAt->addFieldToFilter('updated_at', array('from' => $from));

        foreach ($customerCollectionUpdatedAt as $data) {

            $idInfo = $data->getId();
            $billingInfo = $data->getDefaultBilling();

            if ($data->getGender() === '1') {
                $gender = 'M';
            } else {
                $gender = 'W';
            }

            $customerInfo[$idInfo]['firstName'] = $data->getFirstname();
            $customerInfo[$idInfo]['lastName'] = $data->getLastname();
            $customerInfo[$idInfo]['email'] = $data->getEmail();
            $customerInfo[$idInfo]['birthDate'] = $data->getDob();
            $customerInfo[$idInfo]['gender'] = $gender;

            $customerAddressCollectionUpdatedAt = $this->addressFactory->create()->load($billingInfo);

            $customerInfo[$idInfo]['msisdn'] = $customerAddressCollectionUpdatedAt->getTelephone();
            $customerInfo[$idInfo]['streetAddress'] = $customerAddressCollectionUpdatedAt->getStreetFull();
            $customerInfo[$idInfo]['city'] = $customerAddressCollectionUpdatedAt->getCity();
            $customerInfo[$idInfo]['zipCode'] = $customerAddressCollectionUpdatedAt->getPostcode();

            $jsonCustomerInfo = json_encode($customerInfo);


//        $userFile = fopen(__DIR__ . "/users.txt", "a+");
//        fwrite($userFile, "User:  ,was updated at:   \n");
        }
    }
}