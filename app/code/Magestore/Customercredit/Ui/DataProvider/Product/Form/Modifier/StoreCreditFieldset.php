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

namespace Magestore\Customercredit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Ui\Component\Form\Element\DataType\Text;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Field;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class StoreCreditFieldset implements ModifierInterface
{

    const FORM_NAME = 'product_form';
    const DATA_SOURCE_DEFAULT = 'product';
    const DATA_SCOPE_PRODUCT = 'data.product';

    /**
     * Name of default general panel
     */
    const DEFAULT_GENERAL_PANEL = 'product-details';

    /**
     * Default general panel order
     */
    const GENERAL_PANEL_ORDER = 10;

    /**
     * Container fieldset prefix
     */
    const CONTAINER_PREFIX = 'container_';

    /**
     * Meta config path
     */
    const META_CONFIG_PATH = '/arguments/data/config';
    // Components indexes
    const CUSTOM_FIELDSET_INDEX = 'storecredit_fieldset';
    const CUSTOM_FIELDSET_CONTENT = 'storecredit_fieldset_content';
    const CONTAINER_HEADER_NAME = 'storecredit_fieldset_content_header';

    // Fields names
    const FIELD_PRICE_TYPE = 'storecredit_type';
    const FIELD_PRICE_RATE = 'storecredit_rate';
    const FIELD_PRICE_VALUE = 'storecredit_value';
    const FIELD_PRICE_VALUES = 'storecredit_dropdown';
    const FIELD_PRICE_VALUE_FROM = 'storecredit_from';
    const FIELD_PRICE_VALUE_TO = 'storecredit_to';

    //    Storecredit type
    const CREDIT_TYPE_NONE = 0;
    const CREDIT_TYPE_FIX = 1;
    const CREDIT_TYPE_RANGE = 2;
    const CREDIT_TYPE_DROPDOWN = 3;

    const CREDIT_FIELD_COMPONENT = 'Magestore_Customercredit/js/components/credit-field';

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ArrayManager $arrayManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        LocatorInterface $locator,
        ArrayManager $arrayManager,
        UrlInterface $urlBuilder
    ) {
        $this->locator = $locator;
        $this->arrayManager = $arrayManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Data modifier, does nothing in our example.
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Meta-data modifier: adds ours fieldset
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;
        $product = $this->locator->getProduct();
        if($product->getTypeId() == 'customercredit'){
            $this->addCustomFieldset();
        }
        return $this->meta;
    }

    /**
     * Merge existing meta-data with our meta-data (do not overwrite it!)
     *
     * @return void
     */
    protected function addCustomFieldset()
    {
        if(array_key_exists('credit-prices-settings', $this->meta))
            unset($this->meta['credit-prices-settings']);

        $this->meta = array_merge_recursive(
            $this->meta,
            [
                static::CUSTOM_FIELDSET_INDEX => $this->getFieldsetConfig(),
            ]
        );
    }

    /**
     * Declare ours fieldset config
     *
     * @return array
     */
    protected function getFieldsetConfig()
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Credit Prices Settings '),
                        'componentType' => Fieldset::NAME,
                        'dataScope' => static::DATA_SCOPE_PRODUCT, // save data in the product data
                        'provider' => static::DATA_SCOPE_PRODUCT . '_data_source',
                        'ns' => static::FORM_NAME,
                        'collapsible' => true,
                        'sortOrder' => 10,
                        'opened' => true,
                    ],
                ],
            ],
            'children' => [
                static::CONTAINER_HEADER_NAME => $this->getHeaderContainerConfig(10),
                static::FIELD_PRICE_TYPE => $this->getSelectFieldConfig(20),
                static::FIELD_PRICE_RATE => $this->getRateFieldConfig(30),
                static::FIELD_PRICE_VALUE => $this->getValueFieldConfig(35),
                static::FIELD_PRICE_VALUES => $this->getValuesFieldConfig(40),
                static::FIELD_PRICE_VALUE_FROM => $this->getValueFromFieldConfig(45),
                static::FIELD_PRICE_VALUE_TO => $this->getValueToFieldConfig(50),
            ],
        ];
    }

    /**
     * Get config for header container
     *
     * @param int $sortOrder
     * @return array
     */
    protected function getHeaderContainerConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => null,
                        'formElement' => Container::NAME,
                        'componentType' => Container::NAME,
                        'template' => 'ui/form/components/complex',
                        'sortOrder' => $sortOrder,
                        'content' => __('Credit product let customers choose the price type they want.'),
                    ],
                ],
            ],
            'children' => [],
        ];
    }

    /**
     * Example select field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getSelectFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Type of Store Credit Value'),
                        'componentType' => Field::NAME,
                        'formElement' => Select::NAME,
                        'dataScope' => static::FIELD_PRICE_TYPE,
                        'dataType' => Text::NAME,
                        'sortOrder' => $sortOrder,
                        'options' => $this->_getOptions(),
                        'visible' => true,
                        'validation' => [
                            'required-entry' => 'true'
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getRateFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Credit Rate'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_RATE,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'default' => 1.0,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '1' => '1',
                            '2' => '2',
                            '3' => '3'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value'
                        ],
                        'notice' => __('For example: 1.5'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '1' => '1'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValuesFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Store Credit Values'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUES,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                        ],
                        'valuesForEnable' => [
                            '3' => '3'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                        ],
                        'addbefore' => '$',
                        'notice' => __('Seperated by comma, e.g. 10,20,30'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueFromFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Minimum Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE_FROM,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true',
                        ],
                        'valuesForEnable' => [
                            '2' => '2'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                            'handleChangeMin' => '${$.parentName}.' . static::FIELD_PRICE_VALUE_TO . ':value'
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Example text field config
     *
     * @param $sortOrder
     * @return array
     */
    protected function getValueToFieldConfig($sortOrder)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Maximum Store Credit Value'),
                        'component' => self::CREDIT_FIELD_COMPONENT,
                        'formElement' => Field::NAME,
                        'componentType' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_VALUE_TO,
                        'dataType' => Number::NAME,
                        'sortOrder' => $sortOrder,
                        'disabled' => true,
                        'validation' => [
                            'required-entry' => 'true',
                            'validate-greater-than-zero' => 'true'
                        ],
                        'valuesForEnable' => [
                            '2' => '2'
                        ],
                        'imports' => [
                            'toggleDisable' => '${$.parentName}.' . static::FIELD_PRICE_TYPE . ':value',
                            'handleChangeMax' => '${$.parentName}.' . static::FIELD_PRICE_VALUE_FROM . ':value'
                        ],
                        'addbefore' => '$'
                    ],
                ],
            ],
        ];
    }

    /**
     * Get example options as an option array:
     *      [
     *          label => string,
     *          value => option_id
     *      ]
     *
     * @return array
     */
    protected function _getOptions()
    {
        $options = [
            [
                'label' => __('Select'),
                'value' => self::CREDIT_TYPE_NONE
            ],
            [
                'label' => __('Fixed value'),
                'value' => self::CREDIT_TYPE_FIX
            ],
            [
                'label' => __('Range of values'),
                'value' => self::CREDIT_TYPE_RANGE
            ],
            [
                'label' => __('Dropdown values'),
                'value' => self::CREDIT_TYPE_DROPDOWN
            ]
        ];

        return $options;
    }

    /**
     * Retrieve next group sort order
     *
     * @param array $meta
     * @param array|string $groupCodes
     * @param int $defaultSortOrder
     * @param int $iteration
     * @return int
     * @since 101.0.0
     */
    protected function getNextGroupSortOrder(array $meta, $groupCodes, $defaultSortOrder, $iteration = 1)
    {
        $groupCodes = (array)$groupCodes;

        foreach ($groupCodes as $groupCode) {
            if (isset($meta[$groupCode]['arguments']['data']['config']['sortOrder'])) {
                return $meta[$groupCode]['arguments']['data']['config']['sortOrder'] + $iteration;
            }
        }

        return $defaultSortOrder;
    }

    /**
     * Retrieve next attribute sort order
     *
     * @param array $meta
     * @param array|string $attributeCodes
     * @param int $defaultSortOrder
     * @param int $iteration
     * @return int
     * @since 101.0.0
     */
    protected function getNextAttributeSortOrder(array $meta, $attributeCodes, $defaultSortOrder, $iteration = 1)
    {
        $attributeCodes = (array)$attributeCodes;

        foreach ($meta as $groupMeta) {
            $defaultSortOrder = $this->_getNextAttributeSortOrder(
                $groupMeta,
                $attributeCodes,
                $defaultSortOrder,
                $iteration
            );
        }

        return $defaultSortOrder;
    }

    /**
     * Retrieve next attribute sort order
     *
     * @param array $meta
     * @param array $attributeCodes
     * @param int $defaultSortOrder
     * @param int $iteration
     * @return mixed
     */
    private function _getNextAttributeSortOrder(array $meta, $attributeCodes, $defaultSortOrder, $iteration = 1)
    {
        if (isset($meta['children'])) {
            foreach ($meta['children'] as $attributeCode => $attributeMeta) {
                if ($this->startsWith($attributeCode, self::CONTAINER_PREFIX)) {
                    $defaultSortOrder = $this->_getNextAttributeSortOrder(
                        $attributeMeta,
                        $attributeCodes,
                        $defaultSortOrder,
                        $iteration
                    );
                } elseif (in_array($attributeCode, $attributeCodes)
                    && isset($attributeMeta['arguments']['data']['config']['sortOrder'])
                ) {
                    $defaultSortOrder = $attributeMeta['arguments']['data']['config']['sortOrder'] + $iteration;
                }
            }
        }

        return $defaultSortOrder;
    }

    /**
     * Search backwards starting from haystack length characters from the end
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     * @since 101.0.0
     */
    protected function startsWith($haystack, $needle)
    {
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Return name of first panel (general panel)
     *
     * @param array $meta
     * @return string
     * @since 101.0.0
     */
    protected function getGeneralPanelName(array $meta)
    {
        if (!$meta) {
            return null;
        }

        if (isset($meta[self::DEFAULT_GENERAL_PANEL])) {
            return self::DEFAULT_GENERAL_PANEL;
        }

        return $this->getFirstPanelCode($meta);
    }

    /**
     * Retrieve first panel name
     *
     * @param array $meta
     * @return string|null
     * @since 101.0.0
     */
    protected function getFirstPanelCode(array $meta)
    {
        $min = null;
        $name = null;

        foreach ($meta as $fieldSetName => $fieldSetMeta) {
            if (isset($fieldSetMeta['arguments']['data']['config']['sortOrder'])
                && (null === $min || $fieldSetMeta['arguments']['data']['config']['sortOrder'] <= $min)
            ) {
                $min = $fieldSetMeta['arguments']['data']['config']['sortOrder'];
                $name = $fieldSetName;
            }
        }

        return $name;
    }

    /**
     * Get group code by field
     *
     * @param array $meta
     * @param string $field
     * @return string|bool
     * @since 101.0.0
     */
    protected function getGroupCodeByField(array $meta, $field)
    {
        foreach ($meta as $groupCode => $groupData) {
            if (isset($groupData['children'][$field])
                || isset($groupData['children'][static::CONTAINER_PREFIX . $field])
            ) {
                return $groupCode;
            }
        }

        return false;
    }

    /**
     * Format price to have only two decimals after delimiter
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatPrice($value)
    {
        return $value !== null ? number_format((float)$value, PriceCurrencyInterface::DEFAULT_PRECISION, '.', '') : '';
    }

    /**
     * Strip excessive decimal digits from weight number
     *
     * @param mixed $value
     * @return string
     * @since 101.0.0
     */
    protected function formatWeight($value)
    {
        return (float)$value;
    }
}