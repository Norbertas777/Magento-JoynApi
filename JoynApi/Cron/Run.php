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

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory
    )
    {
        $this->_logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->addressFactory = $addressFactory;
    }

    public function execute()
    {
        $to = date("Y-m-d h:i:s");
        $from = strtotime('-24 hours', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $customerAddressCollectionUpdatedAt = $this->addressFactory->create()->getCollection();
        $customerAddressCollectionUpdatedAt->addFieldToSelect('*');
        $customerAddressCollectionUpdatedAt->addFieldToFilter('updated_at', array('from' => $from));

        $customerCollectionUpdatedAt = $this->collectionFactory->create();
        $customerCollectionUpdatedAt->addFieldToSelect('*');
        $customerCollectionUpdatedAt->addFieldToFilter('updated_at', array('from' => $from));

        $customerAddressData = $customerAddressCollectionUpdatedAt->getData();
        $customerData = $customerCollectionUpdatedAt->getData();

//        $userFile = fopen(__DIR__ . "/users.txt", "a+");
//        fwrite($userFile, "User:  ,was updated at:   \n");
    }
}