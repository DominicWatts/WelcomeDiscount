<?php

namespace Xigen\Voucher\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Template\Context;
use Xigen\Voucher\Helper\Email;

/**
 * Block class
 */
class Voucher extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Email
     */
    private $email;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var Session
     */
    private $session;

    /**
     * Voucher constructor.
     * @param Context $context
     * @param Email $email
     * @param Http $request
     * @param Session $session
     * @param array $data
     */
    public function __construct(
        Context $context,
        Email $email,
        Http $request,
        Session $session,
        array $data = []
    ) {
        $this->email = $email;
        $this->request = $request;
        $this->session = $session;
        parent::__construct($context, $data);
    }

    /**
     * Get voucher code
     * @return string
     */
    public function getVoucherCode()
    {
        if ($this->request->isPost()) {
            if ($email = $this->request->getPostValue('email')) {
                return $this->email->getCustomerVoucherByEmail($email);
            }
        }
        if ($customer = $this->session->getCustomer()) {
            return $this->email->getCustomerVoucherByEmail($customer->getEmail());
        }
        return __("Please register");
    }
}
