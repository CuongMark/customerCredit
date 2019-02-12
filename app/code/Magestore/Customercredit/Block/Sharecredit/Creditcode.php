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

class Creditcode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magestore\Customercredit\Model\CustomercreditFactory
     */
    protected $_customerCreditFactory;
    /**
     * @var \Magestore\Customercredit\Helper\Data
     */
    protected $_creditHelper;
    /**
     * @var \Magestore\Customercredit\Model\CreditcodeFactory
     */
    protected $_creditcodeFactory;
    /**
     * @var \Magestore\Customercredit\Model\Source\Status
     */
    protected $_creditStatus;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory
     * @param \Magestore\Customercredit\Helper\Data $creditHelper
     * @param \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory
     * @param \Magestore\Customercredit\Model\Source\Status $creditStatus
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magestore\Customercredit\Model\CustomercreditFactory $customerCreditFactory,
        \Magestore\Customercredit\Helper\Data $creditHelper,
        \Magestore\Customercredit\Model\CreditcodeFactory $creditcodeFactory,
        \Magestore\Customercredit\Model\Source\Status $creditStatus
    )
    {
        $this->_customerSession = $customerSession;
        $this->_customerCreditFactory = $customerCreditFactory;
        $this->_creditHelper = $creditHelper;
        $this->_creditcodeFactory = $creditcodeFactory;
        $this->_creditStatus = $creditStatus;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context);
    }

    protected function _construct()
    {
        parent::_construct();
        $validate_config = $this->_creditHelper->getGeneralConfig('validate', null);
        $customer_id = $this->_customerSession->getCustomer()->getId();
        $collection = $this->_creditcodeFactory->create()->getCollection()
            ->addFieldToFilter('main_table.customer_id', $customer_id);
        $collection->setOrder('transaction_time', 'DESC');
        if ($validate_config == 0) {
            $collection->addFieldToFilter('status', array('neq' => '4'));
        }

        $this->setCollection($collection);
    }

    public function _prepareLayout()
    {
        parent::_prepareLayout();
        $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'customercredit.history.pager')->setCollection($this->getCollection());
        $this->setChild('cutomercredit_pager', $pager);

        $grid = $this->getLayout()->createBlock('Magestore\Customercredit\Block\Sharecredit\Grid', 'customercredit_grid');
        // prepare column

        $grid->addColumn('credit_code', array(
            'header' => __('Credit Code'),
            'index' => 'credit_code',
            'format' => 'medium',
            'align' => 'left',
            'render' => 'getCodeTxt',
            'searchable' => true,
        ));

        $grid->addColumn('recipient_email', array(
            'header' => __('Recipient\'s Email'),
            'align' => 'left',
            'index' => 'recipient_email',
            'searchable' => true,
        ));

        $grid->addColumn('amount_credit', array(
            'header' => __('Amount'),
            'align' => 'left',
            'type' => 'price',
            'index' => 'amount_credit',
            'render' => 'getBalanceFormat',
            'searchable' => true,
        ));


        $grid->addColumn('transaction_time', array(
            'header' => __('Sending Date'),
            'index' => 'transaction_time',
            'type' => 'date',
            'format' => 'medium',
            'align' => 'left',
            'searchable' => true,
        ));
        $statuses = $this->_creditStatus->getOptionArray();
        $grid->addColumn('status', array(
            'header' => __('Status'),
            'align' => 'left',
            'index' => 'status',
            'type' => 'options',
            'options' => $statuses,
            'searchable' => true,
        ));
        $grid->addColumn('action', array(
            'header' => __('Action'),
            'align' => 'left',
            'type' => 'action',
            'width' => '50px',
            'render' => 'getActions',
        ));

        $this->setChild('customercredit_grid', $grid);
        return $this;
    }

    public function getNoNumber($row)
    {
        return sprintf('#%d', $row->getId());
    }

    public function getCodeTxt($row)
    {
        $input = '<input id="input-credit-code' . $row->getId() . '" readonly type="text" class="input-text" value="' . $row->getCreditCode() . '" onblur="hiddencode' . $row->getId() . '(this);">';
        $aelement = '<a href="javascript:void(0);" onclick="viewcreditcode' . $row->getId() . '()">' . $this->_creditHelper->getHiddenCode($row->getCreditCode()) . '</a>';
        $html = '<div id="inputboxcustomercredit' . $row->getId() . '" >' . $aelement . '</div>
                <script type="text/javascript">
                        function viewcreditcode' . $row->getId() . '(){
                            $(\'inputboxcustomercredit' . $row->getId() . '\').innerHTML=\'' . $input . '\';
                            $(\'input-credit-code' . $row->getId() . '\').focus();
                        }
                        function hiddencode' . $row->getId() . '(el) {
                            $(\'inputboxcustomercredit' . $row->getId() . '\').innerHTML=\'' . $aelement . '\';
                        }
                </script>';
        return $html;
    }

    public function getBalanceFormat($row)
    {
//        $amount = $this->_customerCredit->getConvertedFromBaseCustomerCredit($row->getAmountCredit()); Gin fix muti currentcy
        $amount =$row->getAmountCredit();
        return $this->_creditHelper->getFormatAmount($amount);
    }

    public function getActions($row)
    {
        $creditcode = $row->getCreditCode();
        $recipient_email = $row->getRecipientEmail();
        $credit_amount = $row->getAmountCredit();
        $confirmText = __('If you do this, the recipient will not be able to use the code and the credit will be given back to your account. Are you sure you want to continue?');
        $cancelurl = $this->getUrl('customercredit/index/cancel', array('id' => $row->getId()));
        $verify_sender_url = $this->getUrl('customercredit/index/verifySender', array(
            'id' => $row->getId(),
            'customercredit_email_input' => $recipient_email,
            'customercredit_value_input' => $credit_amount
        ));

        $action = '';
        if ($row->getStatus() == \Magestore\Customercredit\Model\Source\Status::STATUS_UNUSED) {

            $action .= ' <a href="javascript:void(0);" onclick="remove' . $row->getId() . '()">' . __('Cancel') . '</a>';
            $action .= '<script type="text/javascript">
                        //<![CDATA[
                            function remove' . $row->getId() . '(){
                                if (confirm(\'' . $confirmText . '\')){
                                    setLocation(\'' . $cancelurl . '\');
                                }
                            }
                        //]]>
                    </script>';
        }
        if ($row->getStatus() == \Magestore\Customercredit\Model\Source\Status::STATUS_AWAITING_VERIFICATION) {
            $action .= ' <a href="javascript:void(0);" onclick="verify' . $row->getId() . '()">' . __('Verify') . '</a>';
            $action .= '<script type="text/javascript">
                        //<![CDATA[
                            function verify' . $row->getId() . '(){
                                    setLocation(\'' . $verify_sender_url . '\');
                            }
                        //]]>
                    </script>';
        }
        return $action;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('cutomercredit_pager');
    }

    public function getGridHtml()
    {
        return $this->getChildHtml('customercredit_grid');
    }

    protected function _toHtml()
    {
        $this->getChildBlock('customercredit_grid')->setCollection($this->getCollection());
        return parent::_toHtml();
    }

}
