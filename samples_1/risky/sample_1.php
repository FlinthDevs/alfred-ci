<?php
public function getSectionData()
    {
        $customerPostCode = $this->customerHelper->getCustomer()->getDefaultBillingAddress()->getPostcode();
        if ($customerPostCode) {
            return [$customerPostCode];
        }
        return [];
    }

