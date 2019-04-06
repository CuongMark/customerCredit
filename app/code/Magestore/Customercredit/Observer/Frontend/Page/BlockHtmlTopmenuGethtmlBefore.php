<?php


namespace Magestore\Customercredit\Observer\Frontend\Page;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\UrlInterface;

class BlockHtmlTopmenuGethtmlBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        UrlInterface $urlBuilder
    ){
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $menu = $observer->getMenu();
        $tree = $menu->getTree();
        $data = [
            'name'      => __('Store Credit'),
            'id'        => 'credit_menu_item',
            'url'       => $this->urlBuilder->getUrl('customercredit/index/listproduct'),
            'is_active' => false
        ];
        $credit = new Node($data, 'id', $tree, $menu);
        $menu->addChild($credit);
    }
}
