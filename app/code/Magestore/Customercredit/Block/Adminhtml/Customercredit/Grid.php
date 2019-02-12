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

namespace Magestore\Customercredit\Block\Adminhtml\Customercredit;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $_groupFactory;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $creditHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        array $data = array()
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $context->getStoreManager();
        $this->_groupFactory = $groupFactory;
        $this->_systemStore = $systemStore;
        $this->creditHelper = $creditHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customercreditGrid');
        $this->setDefaultSort('customercredit_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_customerFactory->create()->getCollection()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
//        $collection->getSelect()->joinLeft(array(
//            'table_customer_credit' => $collection->getTable('customer_credit')),
//            'table_customer_credit.customer_id = e.entity_id',
//            array('credit_value' => 'table_customer_credit.credit_balance')
//        );
        $collection->joinTable(
            ['table_customer_credit' =>  $collection->getTable('customer_credit')],
            'customer_id = entity_id',
            array('credit_value'=>'credit_balance'),
            null ,
            'left'
        );

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header' => __('ID'),
            'width' => '50px',
            'index' => 'entity_id',
            'type' => 'number',
        ));
        $this->addColumn('name', array(
            'header' => __('Name'),
            'index' => 'name'
        ));
        $this->addColumn('email', array(
            'header' => __('Email'),
            'width' => '150',
            'index' => 'email',
            'renderer' => 'Magestore\Customercredit\Block\Adminhtml\Customer\Renderer\Customer'
        ));

        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $this->addColumn('credit_value', array(
            'header' => __('Credit Balance'),
            'width' => '100',
            'align' => 'right',
            'currency_code' => $currency,
            'index' => 'credit_value',
            'type' => 'price',
            'renderer' => 'Magestore\Customercredit\Block\Adminhtml\Customer\Renderer\Customerprice',
            'filter_condition_callback' => [$this,'filterCreditValue'],
        ));
        $groups = $this->_groupFactory->create()->getCollection()
            ->addFieldToFilter('customer_group_id', array('gt' => 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header' => __('Group'),
            'width' => '100',
            'index' => 'group_id',
            'type' => 'options',
            'options' => $groups,
        ));

        $this->addColumn('Telephone', array(
            'header' => __('Telephone'),
            'width' => '100',
            'index' => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header' => __('ZIP'),
            'width' => '90',
            'index' => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header' => __('Country'),
            'width' => '100',
            'type' => 'country',
            'index' => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header' => __('State/Province'),
            'width' => '100',
            'index' => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header' => __('Customer Since'),
            'type' => 'datetime',
            'align' => 'center',
            'index' => 'created_at',
            'gmtoffset' => true
        ));

        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header' => __('Website'),
                'align' => 'center',
                'width' => '80px',
                'type' => 'options',
                'options' => $this->_systemStore->getWebsiteOptionHash(true),
                'index' => 'website_id',
            ));
        }

        $this->addColumn('action', array(
            'header' => __('Action'),
            'width' => '100',
            'type' => 'action',
            'getter' => 'getId',
            'actions' => array(
                array(
                    'caption' => __('Edit'),
                    'url' => array(
                        'base' => 'customer/index/edit/',
                        'params' => ['store' => $this->getRequest()->getParam('store'), 'type' => 'customercredit']
                    ),
                    'field' => 'id'
                )
            ),
            'filter' => false,
            'sortable' => false,
            'index' => 'stores',
            'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('Excel XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/index', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('customer/index/edit/', array(
                'id' => $row->getId(),
                'type' => 'customercredit'
            )
        );
    }

    public function getCsv()
    {
        $csv = '';
        $this->_isExport = true;
        $this->_prepareGrid();
        $this->getCollection()->getSelect()->limit();
        $this->getCollection()->setPageSize(0);
        $this->getCollection()->load();
        $this->_afterLoadCollection();

        $data = array();
        $data[] = '"' . __('ID') . '"';
        $data[] = '"' . __('Name') . '"';
        $data[] = '"' . __('Email') . '"';
        $data[] = '"' . __('Credit Balance') . '"';
        $data[] = '"' . __('Group') . '"';
        $data[] = '"' . __('Telephone') . '"';
        $data[] = '"' . __('ZIP') . '"';
        $data[] = '"' . __('Country') . '"';
        $data[] = '"' . __('State/Province') . '"';
        $data[] = '"' . __('Customer Since') . '"';
        $data[] = '"' . __('Website') . '"';
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $item) {
            $data = $this->creditHelper->getValueToCsv($item);
            $csv .= $data . "\n";
        }
        return $csv;
    }

    function filterCreditValue($collection, $column)
    {
        if (!$column->getFilter()->getCondition()) {
            return;
        }

        $condition = $collection->getConnection()
            ->prepareSqlCondition('table_customer_credit.credit_balance', $column->getFilter()->getCondition());
        $collection->getSelect()->where($condition);
    }
}