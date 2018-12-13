<?php
        if ($this->getRequest()->isAjax()) {
            $optionsSku = $this->getRequest()->getParam('optionsSku');
            $sessionPrices = $this->catalogHelper->getCatalogSession()->getWheelsPackPrices();

            $priceOptions = 0;
            if (count($optionsSku) > 0) {
                $productOptionCollection = $this->productFactory->create()
                    ->getCollection()
                    ->addAttributeToSelect(['price', 'special_price', 'conditionnement'])
                    ->addAttributeToFilter('sku', ['in' => $optionsSku]);

                foreach ($productOptionCollection as $productOption) {
                    $conditionnement = $productOption->getConditionnement() ? $productOption->getConditionnement() : 1;
                    $priceOptions += ($productOption->getFinalPrice() / $conditionnement);
                }
            }

            $productTire = $this->productFactory->create()
                ->getCollection()
                ->addAttributeToSelect(['price', 'special_price'])
                ->addAttributeToFilter('sku', $this->catalogHelper->getCatalogSession()->getWheelsPackTire())
                ->getFirstItem();

            return  $this->resultJsonFactory->create()->setData(
                [
                    'price' => $this->helper->currency(
                        $sessionPrices['price'] + $priceOptions + $productTire->getFinalPrice(),
                        true,
                        false
                    ),
                    'packPrice' => $this->helper->currency(
                        $sessionPrices['packPrice'] + ($priceOptions * 4) + ($productTire->getFinalPrice() * 4),
                        true,
                        false
                    ),
                ]
            );
        }
