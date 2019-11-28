<?php

namespace Xigen\Voucher\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Email helper class
 */
class Email extends AbstractHelper
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepositoryInterface;
  
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepositoryInterface,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get customer by email
     * @param string $email
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerByEmail($email)
    {
        try {
            return $this->customerRepositoryInterface->get($email, $this->getWebsiteId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Get customer voucher by email
     * @param string $email
     * @return string
     */
    public function getCustomerVoucherByEmail($email)
    {
        if ($customer = $this->getCustomerByEmail($email)) {
            if ($attribute = $customer->getCustomAttribute('voucher')) {
                return $attribute->getValue();
            }
        }
        return false;
    }

    /**
     * Get website identifier
     * @return string|int|null
     */
    public function getWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }
}
