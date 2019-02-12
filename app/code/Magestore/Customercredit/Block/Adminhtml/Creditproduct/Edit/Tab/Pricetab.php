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
namespace Magestore\Customercredit\Block\Adminhtml\Creditproduct\Edit\Tab;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Pricetab extends \Magento\Backend\Block\Widget\Form\Generic  implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $product = $this->_coreRegistry->registry('product');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('add_review_form', ['legend' => __('Credit Prices Settings')]);

        $fieldset->addField('head_text', 'note', [
            'label' => __(' '),
            'text' => __('Credit product let customers choose the price type they want.')
            ]
        );

        $storecredit_type = $fieldset->addField(
            'storecredit_type',
            'select',
            [
                'name' => 'product[storecredit_type]',
                'label' => __('Type of Store Credit Value'),
                'required' => true,
                'options' => [
                    '' => 'Select',
                    '1' => 'Fixed value',
                    '2' => 'Range of values',
                    '3' => 'Dropdown values'
                ]
            ]
        );
        $storecredit_rate = $fieldset->addField(
            'storecredit_rate',
            'text',
            [
                'name' => 'product[storecredit_rate]',
                'label' => __('Credit Rate'),
                'required' => true,
                'note' => 'For example: 1.5',
                'value' => '1'
            ]
        );
        $storecredit_value = $fieldset->addField(
            'storecredit_value',
            'text',
            [
                'name' => 'product[storecredit_value]',
                'label' => __('Store Credit Value'),
                'required' => true
            ]
        );
        $storecredit_dropdown = $fieldset->addField(
            'storecredit_dropdown',
            'text',
            [
                'name' => 'product[storecredit_dropdown]',
                'label' => __('Store Credit Values'),
                'required' => true,
                'note' => 'Seperated by comma, e.g. 10,20,30'
            ]
        );
        $storecredit_from = $fieldset->addField(
            'storecredit_from',
            'text',
            [
                'name' => 'product[storecredit_from]',
                'label' => __('Minimum Store Credit value'),
                'required' => true,
            ]
        );
        $storecredit_to = $fieldset->addField(
            'storecredit_to',
            'text',
            [
                'name' => 'product[storecredit_to]',
                'label' => __('Maximum Store Credit value'),
                'required' => true,
            ]
        );

        $form->setValues($product->getData());
        $this->setForm($form);

        // field dependencies
        $dependence = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($storecredit_type->getHtmlId(),$storecredit_type->getName())
            ->addFieldMap($storecredit_value->getHtmlId(),$storecredit_value->getName())
            ->addFieldMap($storecredit_dropdown->getHtmlId(),$storecredit_dropdown->getName())
            ->addFieldMap($storecredit_from->getHtmlId(),$storecredit_from->getName())
            ->addFieldMap($storecredit_to->getHtmlId(),$storecredit_to->getName())
            ->addFieldDependence($storecredit_value->getName(),$storecredit_type->getName(),1)
            ->addFieldDependence($storecredit_from->getName(),$storecredit_type->getName(),2)
            ->addFieldDependence($storecredit_to->getName(),$storecredit_type->getName(),2)
            ->addFieldDependence($storecredit_dropdown->getName(),$storecredit_type->getName(),3
        );

        $this->setChild('form_after', $dependence);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label.
     *
     * @return string
     *
     * @api
     */
    public function getTabLabel()
    {
        return __('Credit Prices Settings');
    }

    /**
     * Return Tab title.
     *
     * @return string
     *
     * @api
     */
    public function getTabTitle()
    {
        return __('Credit Prices Settings');
    }

    /**
     * Can show tab in tabs.
     *
     * @return bool
     *
     * @api
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden.
     *
     * @return bool
     *
     * @api
     */
    public function isHidden()
    {
        return false;
    }
}