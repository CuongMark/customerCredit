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

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
}