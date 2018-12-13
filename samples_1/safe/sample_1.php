<?php
public function getSectionData()
    {
        $defaultBillingAddress = $this->customerHelper->getCustomer()->getDefaultBillingAddress();

        if ($defaultBillingAddress) {
            $customerPostCode = $defaultBillingAddress->getPostcode();

            if ($customerPostCode) {
                return [$customerPostCode];
            }
        }

        return [];
    }
