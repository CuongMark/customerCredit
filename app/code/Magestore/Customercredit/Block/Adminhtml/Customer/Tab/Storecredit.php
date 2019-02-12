<?php
/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Customercredit
 * @copyright   Copyright (c) 2017 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 *
 */

namespace Magestore\Customercredit\Block\Adminhtml\Customer\Tab;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class Storecredit extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCreditFactory;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $_coreHelper;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Pricing\Helper\Data $coreHelper
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Pricing\Helper\Data $coreHelper,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory,
        array $data = []
    )
    {
        $this->_coreHelper = $coreHelper;
        $this->_customerCreditFactory = $customerCreditFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('customercredit_fieldset', array(
            'legend' => __('Credit Information')
        ));

        $fieldset->addField('credit_balance', 'note', array(
            'label' => __('Current Credit Balance'),
            'title' => __('Current Credit Balance'),
            'text' => $this->getBalanceCredit(),
        ));
        $fieldset->addField('credit_value', 'text', array(
            'label' => __('Add or subtract  a credit value'),
            'title' => __('Add or subtract  a credit value'),
            'name' => 'credit_value',
            'data-form-part' => $this->getData('target_form'),
            'note' => __('You can add or subtract an amount from customer’s balance by entering a number. For example, enter “99” to add $99 and “-99” to subtract $99'),
        ));
        $fieldset->addField('description', 'textarea', array(
            'label' => __('Comment'),
            'title' => __('Comment'),
            'name' => 'description',
            'data-form-part' => $this->getData('target_form'),
        ));
        $fieldset->addField('sendemail', 'checkbox', array(
            'after_element_html' => __('Send email to customer'),
            'title' => __('Send email to customer'),
            'type' => 'checkbox',
            'name' => 'send_mail',
            'data-form-part' => $this->getData('target_form'),
            'onclick' => 'this.value = this.checked ? 1 : 0;'
        ));

        $form->addFieldset('balance_history_fieldset', array(
            'legend' => __('Transaction History')
        ))->setRenderer($this->_layout
            ->getBlockSingleton('Magento\Backend\Block\Widget\Form\Renderer\Fieldset')
            ->setTemplate('Magestore_Customercredit::customercredit/transactionhistory.phtml'));

        $this->setForm($form);
        return parent::_prepareForm();
    }

    public function getCredit()
    {
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        return $this->_customerCreditFactory->create()->load($customerId, 'customer_id');
    }

    public function getTabLabel()
    {
        return _('Store Credit');
    }

    public function getTabTitle()
    {
        return __('Store Credit');
    }

    public function canShowTab()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if ($this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)) {
            return false;
        }
        return true;
    }

    public function getBalanceCredit()
    {
        $customerCredit = $this->getCredit()->getCreditBalance();
        return $this->_coreHelper->currency($customerCredit);
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

}
