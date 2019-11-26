<?php

namespace Xigen\Voucher\Observer\Frontend\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Math\Random;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\Service\CouponManagementService;
use Psr\Log\LoggerInterface;

/**
 * RegisterSuccess class
 */
class RegisterSuccess implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepositoryInterface;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var RuleInterface
     */
    private $ruleInterface;

    /**
     * @var CouponFactory
     */
    private $couponFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var CouponGenerationSpecInterfaceFactory
     */
    private $couponGenerationSpecInterfaceFactory;

    /**
     * @var CouponManagementService
     */
    private $couponManagementService;

    /**
     * @var CouponRepositoryInterface
     */
    private $couponRepositoryInterface;

    /**
     * @var RuleInterfaceFactory
     */
    private $ruleInterfaceFactory;

    /**
     * RegisterSuccess constructor.
     * @param LoggerInterface $logger
     * @param RuleRepositoryInterface $ruleRepositoryInterface
     * @param RuleFactory $ruleFactory
     * @param RuleInterface $ruleInterface
     * @param RuleInterfaceFactory $ruleInterfaceFactory
     * @param CouponFactory $couponFactory
     * @param CouponRepositoryInterface $couponRepositoryInterface
     * @param Random $random
     * @param CouponGenerationSpecInterfaceFactory $couponGenerationSpecInterfaceFactory
     * @param CouponManagementService $couponManagementService
     */
    public function __construct(
        LoggerInterface $logger,
        RuleRepositoryInterface $ruleRepositoryInterface,
        RuleFactory $ruleFactory,
        RuleInterface $ruleInterface,
        RuleInterfaceFactory $ruleInterfaceFactory,
        CouponFactory $couponFactory,
        CouponRepositoryInterface $couponRepositoryInterface,
        Random $random,
        CouponGenerationSpecInterfaceFactory $couponGenerationSpecInterfaceFactory,
        CouponManagementService $couponManagementService
    ) {
        $this->logger = $logger;
        $this->ruleRepositoryInterface = $ruleRepositoryInterface;
        $this->ruleFactory = $ruleFactory;
        $this->ruleInterface = $ruleInterface;
        $this->ruleInterfaceFactory = $ruleInterfaceFactory;
        $this->couponFactory = $couponFactory;
        $this->couponRepositoryInterface = $couponRepositoryInterface;
        $this->random = $random;
        $this->couponGenerationSpecInterfaceFactory = $couponGenerationSpecInterfaceFactory;
        $this->couponManagementService = $couponManagementService;
    }

    /**
     * Execute observer
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        if ($event = $observer->getEvent()) {
            if ($customer = $event->getCustomer()) {
                $this->logger->addDebug($customer->getId());

                $rule =  $this->ruleInterfaceFactory
                    ->create()
                    ->setName((string) __("10% discount for %1 : %2",
                        $customer->getId(),
                        $customer->getEmail()
                    ))
                    ->setIsAdvanced(true)
                    ->setStopRulesProcessing(false)
                    ->setDiscountQty(10)
                    ->setCustomerGroupIds([$customer->getGroupId()])
                    ->setWebsiteIds([1])
                    ->setUsesPerCustomer(1)
                    ->setUsesPerCoupon(1)
                    ->setCouponType(RuleInterface::COUPON_TYPE_SPECIFIC_COUPON)
                    ->setSimpleAction(RuleInterface::DISCOUNT_ACTION_BY_PERCENT)
                    ->setDiscountAmount(10)
                    ->setIsActive(true);
                
                try {
                    $salesRule = $this->ruleRepositoryInterface->save($rule);
                    if ($salesRule && $salesRule->getRuleId()) {
                        $this->createCouponCode($salesRule);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }

    /**
     * Undocumented function
     * @param \Magento\SalesRule\Api\Data\RuleInterface $rule
     * @return void
     */
    private function createCouponCode(RuleInterface $rule)
    {
        $couponCode = $this->random->getRandomString(8);
        $coupon = $this->couponFactory
            ->create()
            ->setCode($couponCode)
            ->setIsPrimary(1)
            ->setRuleId($rule->getRuleId());

        try {
            $this->couponRepositoryInterface->save($coupon);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
