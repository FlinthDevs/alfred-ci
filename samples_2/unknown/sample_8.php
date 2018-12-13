<?php

        if ($item->getProductType() == 'dynamic_bundle') {
            foreach ($item->getChildren() as $child) {
                if ($rule->getActions()->validate($child)) {
                    return $child->getQty();
                }
            }
        }
        return $proceed($item, $rule);

