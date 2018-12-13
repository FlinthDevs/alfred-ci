<?php

public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $sku = $this->getRequest()->getParam('skuTire');
            $this->catalogHelper->getCatalogSession()->setWheelsPackTire($sku);
            $priceOptions = '';
            $productTire = $this->productFactory->create()
                ->getCollection()
                ->addAttributeToSelect(['price', 'special_price'])
                ->addAttributeToFilter('sku', $sku)
                ->getFirstItem();

            // Get options selected
            if ($this->getRequest()->getParam('optionsSku')) {
                $productOptions = $this->productFactory->create()
                    ->getCollection()
                    ->addAttributeToSelect(['price', 'special_price'])
                    ->addAttributeToFilter('sku', ['in' => $this->getRequest()->getParam('optionsSku')]);

                foreach ($productOptions as $productOption) {
                    $priceOptions += $productOption->getFinalPrice();
                }
            }

            $sessionPrices = $this->catalogHelper->getCatalogSession()->getWheelsPackPrices();

            return  $this->resultJsonFactory->create()->setData(
                [
                    'price' => $this->helper->currency(
                        $productTire->getFinalPrice() + $sessionPrices['price'] + $priceOptions,
                        true,
                        false
                    ),
                    'packPrice' => $this->helper->currency(
                        ($productTire->getFinalPrice() * 4) + ($priceOptions * 4) + $sessionPrices['packPrice'],
                        true,
                        false
                    ),
                ]
            );
        }
    }
