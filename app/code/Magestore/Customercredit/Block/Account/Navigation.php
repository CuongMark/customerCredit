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

namespace Magestore\Customercredit\Block\Account;

class Navigation extends \Magento\Framework\View\Element\Template
{

    protected $_navigationTitle = '';
    protected $_links = [];

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magestore\Customercredit\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magestore\Customercredit\Helper\Data $dataHelper,
        array $data = array()
    )
    {
        $this->_requestInterface = $context->getRequest();
        $this->_storeManager = $context->getStoreManager();
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    public function setNavigationTitle($title)
    {
        $this->_navigationTitle = $title;
        return $this;
    }

    public function getNavigationTitle()
    {
        return $this->_navigationTitle;
    }

    public function getStoreManager()
    {
        return $this->_storeManager;
    }

    public function getHelper()
    {
        return $this->_dataHelper;
    }

    public function addLink($name, $path, $label, $disabled = false, $order = 0, $urlParams = array())
    {
        if (isset($this->_links[$order])) {
            $order++;
        }

        $link = new \Magento\Framework\DataObject(array(
            'name' => $name,
            'path' => $path,
            'label' => $label,
            'disabled' => $disabled,
            'order' => $order,
            'url' => $this->getUrl($path, $urlParams),
        ));

        $this->_eventManager->dispatch('customercredit_account_navigation_add_link', array(
            'block' => $this,
            'link' => $link,
        ));

        $this->_links[$order] = $link;
        return $this;
    }

    public function getLinks()
    {
        $links = new \Magento\Framework\DataObject(array(
            'links' => $this->_links,
        ));

        $this->_eventManager->dispatch('customercredit_account_navigation_get_links', array(
            'block' => $this,
            'links_obj' => $links,
        ));

        $this->_links = $links->getLinks();

        ksort($this->_links);

        return $this->_links;
    }

    public function isActive($link)
    {
        $aciveLink = $this->_requestInterface->getFullActionName("/");
        if ($aciveLink == $link->getPath()) {
            return true;
        }
        return false;
    }

}
