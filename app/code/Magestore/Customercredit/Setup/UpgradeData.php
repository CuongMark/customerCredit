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

namespace Magestore\Customercredit\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;
    /**
     * @var \Magestore\Customercredit\Helper\Update
     */
    private $helperUpgrade;

    /**
     * @var array
     */
    protected $credit_product_data;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var []
     */
    protected $_calculators;

    /**
     * @var \Magento\Framework\Math\CalculatorFactory
     */
    protected $_calculatorFactory;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * UpgradeData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magestore\Customercredit\Helper\Upgrade $helperUpgrade
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Math\CalculatorFactory $_calculatorFactory
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magestore\Customercredit\Helper\Upgrade $helperUpgrade,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Math\CalculatorFactory $_calculatorFactory,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\App\State $appState
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->helperUpgrade = $helperUpgrade;
        $this->orderFactory = $orderFactory;
        $this->_calculatorFactory = $_calculatorFactory;
        $this->productMetadata = $productMetadata;
        $this->_appState = $appState;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $credit_product_data = $this->helperUpgrade->getProductData();
            $this->upgradeVersionOneZeroTwo($setup);
            $this->helperUpgrade->setProductData($credit_product_data);
        }
        if (version_compare($context->getVersion(), '2.1.0.1', '<')) {
            if ($this->helperUpgrade->checkMagentoEE()) {
                $creditTable = $resource->getTableName('customer_credit');
                $balanceTable = $resource->getTableName('magento_customerbalance');
                $connection->query('INSERT INTO ' . $creditTable . '(customer_id, credit_balance) SELECT customer_id, amount
             FROM ' . $balanceTable . ' WHERE customer_id NOT IN (SELECT customer_id FROM ' . $creditTable . ')');
            }
        }

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $version = $this->productMetadata->getVersion();
            try {
                if (version_compare($version, '2.2.0', '>=')) {
                    $this->_appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
                } else {
                    $this->_appState->setAreaCode('admin');
                }
            } catch (\Exception $e) {
                $this->_appState->getAreaCode();
            }
            $this->convertOrder($setup);
        }
        
        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function upgradeVersionOneZeroTwo($setup)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /* Prepare before add attribute */
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "credit_rate");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_type");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_rate");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_value");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_from");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_to");
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_dropdown");

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_type", $this->getAttributeStoreCreditTypeConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_rate", $this->getAttributeStoreCreditRateConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_value",  $this->getAttributeStoreCreditValueConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_from",  $this->getAttributeStoreCreditValueFromConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_to",  $this->getAttributeStoreCreditValueToConfig());
        $eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, "storecredit_dropdown",  $this->getAttributeStoreCreditValueDropdownConfig());
    }


    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditTypeConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'select';
        $attr['type'] = 'int';
        $attr['label'] = 'Type of Store Credit value';
        $attr['source'] = 'Magestore\Customercredit\Model\Source\Storecredittype';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditRateConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'text';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Credit Rate';
        $attr['frontend_class'] = 'validate-number';
        $attr['note'] = 'For example: 0.8';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditValueConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditValueFromConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Minimum Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditValueToConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'price';
        $attr['type'] = 'decimal';
        $attr['label'] = 'Maximum Store Credit value';
        $attr['frontend_class'] = 'validate-number';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeStoreCreditValueDropdownConfig()
    {
        $attr = $this->getAttributeDefaultConfig();
        $attr['input'] = 'text';
        $attr['type'] = 'varchar';
        $attr['label'] = 'Store Credit values';
        $attr['note'] = 'Seperated by comma, e.g. 10,20,30';
        return $attr;
    }

    /**
     * Example text field config
     *
     * @return array
     */
    protected function getAttributeDefaultConfig()
    {
        return [
            'group' => 'Credit Prices Settings',
            'sort_order' => 1,
            'backend' => '',
            'frontend' => '',
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
            'searchable' => true,
            'visible_in_advanced_search' => true,
            'used_in_product_listing' => true,
            'used_for_sort_by' => true,
            'comparable' => true,
            'wysiwyg_enabled' => true,
            'is_html_allowed_on_front' => true,
            'filterable' => true,
            'apply_to' => 'customercredit',
            'position' => 4,
            'required' => false,
            'user_defined' => true,
            'is_used_in_grid' => true,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => true,
            'visible_on_front' => true,
            'visible' => true,
        ];
    }



    protected function convertOrder(ModuleDataSetupInterface $setup)
    {
        $orderTable = $setup->getTable('sales_order');
        $select = $setup->getConnection()->select();
        $select->from(['main_table' => $orderTable], ['entity_id'])
            ->where('base_customercredit_discount > ?', 0);
        $data = $setup->getConnection()->fetchAll($select);
        foreach ($data as $item) {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $this->orderFactory->create()->load($item['entity_id']);
            $orderItems = $order->getAllItems();
            $store = $order->getStore();
            $totalItemsBaseCustomercreditDiscountInvoiced = $totalItemsCustomercreditDiscountInvoiced
                = $totalItemsBaseCustomercreditDiscountRefunded = $totalItemsCustomercreditDiscountRefunded = 0;
            foreach ($orderItems as $orderItem) {
                $qtyOrdered = $orderItem->getQtyOrdered();
                $qtyInvoiced = $orderItem->getQtyInvoiced();
                $qtyRefunded = $orderItem->getQtyRefunded();
                $baseCustomercreditDiscount = $this->roundPrice($orderItem->getBaseCustomercreditDiscount(), true, $store);
                $customercreditDiscount = $this->roundPrice($orderItem->getCustomercreditDiscount(), true, $store);
                $baseDiscountAmount = $orderItem->getBaseDiscountAmount();
                $discountAmount = $orderItem->getDiscountAmount();
                $baseDiscountInvoiced = $orderItem->getBaseDiscountInvoiced();
                $discountInvoiced = $orderItem->getDiscountInvoiced();
                $baseDiscountRefunded = $orderItem->getBaseDiscountRefunded() ? $orderItem->getBaseDiscountRefunded() : 0;
                $discountRefunded = $orderItem->getDiscountRefunded() ? $orderItem->getDiscountRefunded() : 0;
                $baseCustomercreditDiscountInvoiced = $baseCustomercreditDiscount / $qtyOrdered * $qtyInvoiced;
                $customercreditDiscountInvoiced = $customercreditDiscount / $qtyOrdered * $qtyInvoiced;
                $baseCustomercreditDiscountRefunded = $baseCustomercreditDiscount / $qtyOrdered * $qtyRefunded;
                $customercreditDiscountRefunded = $customercreditDiscount / $qtyOrdered * $qtyRefunded;
                $orderItem->setBaseDiscountAmount(
                    $baseDiscountAmount + $this->roundPrice($baseCustomercreditDiscount, true, $store)
                );
                $orderItem->setDiscountAmount(
                    $discountAmount + $this->roundPrice($customercreditDiscount, true, $store)
                );
                $orderItem->setBaseDiscountInvoiced(
                    $baseDiscountInvoiced + $this->roundPrice($baseCustomercreditDiscountInvoiced, true, $store)
                );
                $orderItem->setDiscountInvoiced(
                    $discountInvoiced + $this->roundPrice($customercreditDiscountInvoiced, true, $store)
                );
                $orderItem->setBaseDiscountRefunded(
                    $baseDiscountRefunded + $this->roundPrice($baseCustomercreditDiscountRefunded, true, $store)
                );
                $orderItem->setBaseDiscountRefunded(
                    $discountRefunded + $this->roundPrice($customercreditDiscountRefunded, true, $store)
                );
                $orderItem->setMagestoreBaseDiscount($orderItem->getMagestoreBaseDiscount() + $baseCustomercreditDiscount);
                $orderItem->setMagestoreDiscount($orderItem->getMagestoreDiscount() + $customercreditDiscount);
                $totalItemsBaseCustomercreditDiscountInvoiced += $baseCustomercreditDiscountInvoiced;
                $totalItemsCustomercreditDiscountInvoiced += $customercreditDiscountInvoiced;
                $totalItemsBaseCustomercreditDiscountRefunded += $baseCustomercreditDiscountRefunded;
                $totalItemsCustomercreditDiscountRefunded += $customercreditDiscountRefunded;
                $orderItem->save();
            }
            $baseDiscountAmount = $order->getBaseDiscountAmount();
            $discountAmount = $order->getDiscountAmount();
            $baseDiscountInvoiced = $order->getBaseDiscountInvoiced();
            $discountInvoiced = $order->getDiscountInvoiced();
            $baseDiscountRefunded = $order->getBaseDiscountRefunded() ? $order->getBaseDiscountRefunded() : 0;
            $discountRefunded = $order->getDiscountRefunded() ? $order->getDiscountRefunded() : 0;
            $baseCustomercreditDiscount = $order->getBaseCustomercreditDiscount();
            $customercreditDiscount = $order->getCustomercreditDiscount();
            $order->setBaseDiscountAmount($baseDiscountAmount - $baseCustomercreditDiscount);
            $order->setDiscountAmount($discountAmount - $customercreditDiscount);
            $order->setBaseDiscountInvoiced($baseDiscountInvoiced - $this->roundPrice($totalItemsBaseCustomercreditDiscountInvoiced, true, $store));
            $order->setDiscountInvoiced($discountInvoiced - $this->roundPrice($totalItemsCustomercreditDiscountInvoiced, true, $store));
            $order->setBaseDiscountRefunded($baseDiscountRefunded - $this->roundPrice($totalItemsBaseCustomercreditDiscountRefunded, true, $store));
            $order->setDiscountRefunded($discountRefunded - $this->roundPrice($totalItemsCustomercreditDiscountRefunded, true, $store));
            $order->save();
        }
    }


    /**
     * Round price considering delta
     *
     * @param float $price
     * @param string $type
     * @param bool $negative Indicates if we perform addition (true) or subtraction (false) of rounded value
     * @return float
     */
    public function roundPrice($price, $negative = false, $store)
    {
        $store->getStoreId();
        if ($price) {
            if (!isset($this->_calculators[$store->getStoreId()])) {
                $this->_calculators[$store->getStoreId()] = $this->_calculatorFactory->create(['scope' => $store]);
            }
            $price = $this->_calculators[$store->getStoreId()]->deltaRound($price, $negative);
        }
        return $price;
    }
}
