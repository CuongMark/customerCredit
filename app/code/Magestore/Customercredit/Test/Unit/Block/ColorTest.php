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

namespace Magestore\Customercredit\Test\Unit\Block;
class ColorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magestore\Customercredit\Block\Color
     */
    protected $block;
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject('Magestore\Customercredit\Block\Color');
    }
    public function testGreeting()
    {
        $name = 'Foggyline';
        $this->assertEquals(
            'Hello '.$this->block->escapeHtml($name),
            $this->block->greeting($name)
        );

        #vendor/phpunit/phpunit/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/Magestore/Customercredit/Test/Unit/Block/ColorTest

    }
}