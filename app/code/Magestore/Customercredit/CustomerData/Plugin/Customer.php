<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\Customercredit\CustomerData\Plugin;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Helper\View;
use Magestore\Customercredit\Model\Customercredit;
use Magestore\Customercredit\Model\CustomercreditFactory;


class Customer
{

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var View
     */
    private $customerViewHelper;

    /**
     * @var CustomercreditFactory
     */
    protected $customerCreditFactory;

    /**
     * @param CurrentCustomer $currentCustomer
     * @param View $customerViewHelper
     */
    public function __construct(
        CurrentCustomer $currentCustomer,
        View $customerViewHelper,
        CustomercreditFactory $customercreditFactory
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
        $this->customerCreditFactory = $customercreditFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $customer, array $customerData)
    {
        if (!$this->currentCustomer->getCustomerId()) {
            return $customerData;
        }

        /** @var Customercredit $customerCredit */
        $customerCredit = $this->customerCreditFactory->create();

        $creditBalance = $customerCredit->getCreditByCustomerId($this->currentCustomer->getCustomerId())->getCreditBalance();
        $customerData['creditBalance'] = $creditBalance;
        return $customerData;
    }
}
