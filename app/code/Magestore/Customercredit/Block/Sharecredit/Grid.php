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

namespace Magestore\Customercredit\Block\Sharecredit;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Zend\Uri\Uri;

class Grid extends \Magento\Framework\View\Element\Template
{

    protected $_columns = array();

    /**
     * Grid's Collection
     */
    protected $_collection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Url\DecoderInterface $decode,
        PriceCurrencyInterface $priceCurrency,
        uri $uri,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Session $customersession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_priceCurrency = $priceCurrency;
        $this->uri = $uri;
        $this->_customer = $customer;
        $this->_customersession = $customersession;
        $this->urlDecoder = $decode;
    }

    public function getPriceCurrency()
    {
        return $this->_priceCurrency;
    }

    public function getCustomer()
    {
        $customer_id = $this->_customersession->getId();
        return $this->_customer->load($customer_id);
    }

    public function getColumns()
    {
        return $this->_columns;
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        if (!$this->getData('add_searchable_row')) {
            return $this;
        }
        foreach ($this->getColumns() as $columnId => $column) {
            if (isset($column['searchable']) && $column['searchable']) {
                if (isset($column['filter_function']) && $column['filter_function']) {
                    $this->fetchFilter($column['filter_function']);
                } else {
                    $field = isset($column['index']) ? $column['index'] : $columnId;
                    $field = isset($column['filter_index']) ? $column['filter_index'] : $field;
                    if ($filterValue = $this->getFilterValue($columnId)) {
                        $this->_collection->addFieldToFilter($field, array('like' => "%$filterValue%"));
                    }
                    if ($filterValue = $this->getFilterValue($columnId, '-from')) {
                        if ($column['type'] == 'price') {
                            $store = $this->_storeManager->getStore();
                            $filterValue /= $store->getBaseCurrency()->convert(1, $store->getCurrentCurrency());
                        } elseif ($column['type'] == 'date' || $column['type'] == 'datetime') {
                            $filterValue = date('Y-m-d', strtotime($filterValue));
                        }
                        $this->_collection->addFieldToFilter($field, array('gteq' => $filterValue));
                    }
                    $filterValue = $this->getFilterValue($columnId, '-to');
                    if ($filterValue || $this->getFilterValue($columnId, '-to') == '0') {
                        if ($column['type'] == 'price') {
                            $store = $this->_storeManager->getStore();
                            $filterValue /= $store->getBaseCurrency()->convert(1, $store->getCurrentCurrency());
                        } elseif ($column['type'] == 'date' || $column['type'] == 'datetime') {
                            $filterValue = date('Y-m-d', strtotime($filterValue) + 86400);
                        }
                        $this->_collection->addFieldToFilter($field, array('lteq' => $filterValue));
                    }
                }
            }
        }

        return $this;
    }

    public function getFilterValue($columnId = null, $offset = '')
    {
        if (!$this->hasData('filter_value')) {
            if ($filter = $this->getRequest()->getParam('filter')) {
                $filter = $this->urlDecoder->decode($filter);
                $this->uri->setQuery($filter);
                $filter = $this->uri->getQueryAsArray();
            }
            $this->setData('filter_value', $filter);
        }
        if (is_null($columnId)) {
            return $this->getData('filter_value');
        } else {
            return $this->getData('filter_value/' . $columnId . $offset);
        }
    }

    public function fetchFilter($parentFuction)
    {
        $parentBlock = $this->getParentBlock();
        return $parentBlock->$parentFuction($this->_collection, $this->getFilterValue());
    }

    public function getFilterUrl()
    {
        if (!$this->hasData('filter_url')) {
            $this->setData('filter_url', $this->getUrl('*/*/*'));
        }
        return $this->getData('filter_url');
    }

    public function getPagerHtml()
    {
        if ($this->getData('add_searchable_row')) {
            return $this->getParentBlock()->getPagerHtml();
        }
        return '';
    }

    public function getCollection()
    {
        return $this->_collection;
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('customercredit/share/grid.phtml');
        return $this;
    }

    public function addColumn($columnId, $params)
    {
        if (isset($params['searchable']) && $params['searchable']) {
            $this->setData('add_searchable_row', true);
            if (isset($params['type']) && ($params['type'] == 'date' || $params['type'] == 'datetime')) {
                $this->setData('add_calendar_js_to_grid', true);
            }
        }
        $this->_columns[$columnId] = $params;
        return $this;
    }

    public function fetchRender($parentFunction, $row)
    {
        $parentBlock = $this->getParentBlock();

        $fetchObj = new \Magento\Framework\DataObject(array(
            'function' => $parentFunction,
            'html' => false,
        ));

        if ($fetchObj->getHtml())
            return $fetchObj->getHtml();

        return $parentBlock->$parentFunction($row);
    }

}
