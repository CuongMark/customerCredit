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

namespace Magestore\Customercredit\Model\Source;

class Customergroup
{
    public function __construct(
        \Magento\Customer\Model\Group $group
    )
    {
        $this->_group = $group;
    }

    public function toOptionArray()
    {
        $customergroup = $this->_group->getCollection();

        $array_list = array();
        $count = 0;
        foreach ($customergroup as $group) {
            if ($group->getCustomerGroupId()) {
                $array_list[$count] = array('value' => $group->getCustomerGroupId(), 'label' => $group->getCustomerGroupCode());
                $count++;
            }
        }
        return $array_list;
    }

}
