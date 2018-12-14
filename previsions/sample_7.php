<?php
        $options = [];
        $bundleDynamicOpt = $item->getOptionByCode('bundle_dynamic_selection');
        if ($bundleDynamicOpt) {
            foreach (explode(',', $bundleDynamicOpt->getValue()) as $dynamicOpt) {
                $key = preg_replace('/[0-9]+/', '', $dynamicOpt);
                $opt = $item->getOptionByCode('bundle_dynamic_selection_'.$dynamicOpt);
                $data = json_decode($opt->getValue());
                if ($data !== null) {
                    $alreadyExist = -1;
                    foreach ($options as $index => $option) {
                        if ($option['label'] == $key) {
                            $alreadyExist = $index;
                            $a = 15;
                        }
                    }
                    if ($alreadyExist >= 0) {
                        $options[$alreadyExist]['value'][] = $data['qty'].' x '.$data['product']
                            .' '
                            .$this->pricingHelper->currency($data['price']);
                    } else {
                        $options[] = [
                            'label' => $key,
                            'value' => [
                                $data['qty'].' x '.$data['product']
                                .' '
                                .$this->pricingHelper->currency($data['price']),
                                ],
                        ];
                    }
                }
            }
        }

        return $options;
