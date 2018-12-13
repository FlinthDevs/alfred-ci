<?php

namespace Avatacar\DynamicBundle\Plugin\Model;

use \Magento\SalesRule\Model\Utility;

class DynamicBundleCouponPlugin
{
    /**
     * "Forge" item quantity when product is a dynamic bundle.
     * When adding a "wheelpack" to cart, discount uses "pack" quantity (1) and
     * not item quantity (4 tires for example ).
     *
     * @param \Magento\SalesRule\Model\Utility $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return int
     */
    public function aroundGetItemQty(Utility $subject, callable $proceed, $item, $rule)
    {
        if ($item->getProductType() == 'dynamic_bundle') {
            foreach ($item->getChildren() as $child) {
                if ($rule->getActions()->validate($child)) {
                    return $child->getQty();
                }
            }
        }
        return $proceed($item, $rule);
    }
}

