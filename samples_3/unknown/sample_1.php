<?php
        $price = 0;
        $optionCollection = $this->catalogHelper->getProductOptionCollection();
        $optionCollection->addAttributeToFilter('option_incluse', 1);
        foreach ($optionCollection as $option) {
            $price += $option->getFinalPrice();
        }

        return $price;
