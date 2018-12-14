<?php
        /* @var Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getData('quote');

        foreach ($attributes as $attribute) {
            if ($quoteObject->hasData($attribute)) {
                $orderObject->setData($attribute, $quoteObject->getData($attribute));
            }
        }
        foreach ($order->getItems() as $orderItem) {
            $quoteItem = $quote->getItemById($orderItem->getQuoteItemId());
            $this->setOrderData($orderItem, $quoteItem, $this->orderItemAttributes);
        }

        return $this;


