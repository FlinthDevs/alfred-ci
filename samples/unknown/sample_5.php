<?php
        $offerInSession = $this->customerSession->getData('offer');
        if (isset($data['braking'])) {
            if (isset($offerInSession['braking'])) {
                $data['braking']['temps'] += $offerInSession['braking']['temps'];
            }
        }
        if (isset($data['revision'])) {
            if (isset($offerInSession['revision'])) {
                $data['revision']['temps'] += $offerInSession['revision']['temps'];
            }
        }
        return $data;
