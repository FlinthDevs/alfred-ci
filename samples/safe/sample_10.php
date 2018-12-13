<?php
        $filterParams = $this->urlFilteringHelper->getFilterArray();

        foreach ($filterParams as $index => $attrName) {
            //we dodge the first index as it will correspond to the keyword 'pneus'
            if (isset($filters[$index + 1])
                && $filters[$index + 1] != $this->urlFilteringHelper->getAllKeyword()
            ) {
                //special case to return the value eq or greater than the current speed index
                if ($attrName == 'indice_vitesse') {
                    $data = $this->tireHelper->getHigherSpeedIndex(
                        $filters[1],
                        $filters[2],
                        $filters[3],
                        $filters[4],
                        $filters[5]
                    );
                }
                // special case to return the value eq or greater than the current load index
                if ($attrName == 'indice_charge') {
                    $data = $this->tireHelper->getHigherLoadIndex(
                        $filters[1],
                        $filters[2],
                        $filters[3],
                        $filters[4],
                        $filters[5]
                    );

                } else if ($attrName == 'runflat' && $filters[$index + 1] == 'Runflat') {
                    // The new url pattern is "pneus-xx-xx-Runflat" instead of "-Yes" so we have to transform the value.
                    $data = 'Yes';
                } else {
                    $data = $filters[$index + 1];
                }

                if (is_string($data)) {
                    $data = urldecode($data);
                }

                $attributeEntity = $this->entityAttribute
                    ->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE, $attrName);
                $attributeType = $attributeEntity->getData('source_model');

                if ($attributeType == \Avatacar\Catalog\Model\Product\Attribute\Source\Boolean::class) {
                    $attributeLabel = '';
                    $attributeOptions = $attributeEntity->getOptions();

                    foreach ($attributeOptions as $option) {
                        if ($option->getData() && $option->getLabel()->getText() == $data) {
                            $attributeLabel = $option->getLabel()->getText();
                            break;
                        }
                    }
                    $data = $attributeLabel;
                }

                // combine with parameters from default request
                $paramValue = (array)$request->getParam($attrName);
                $data = array_unique(array_merge($paramValue, (array)$data));
                // Sort array to reorder the keys from 0 to x, to avoid an issue with ES query
                sort($data);

                //we put the unmandato`ry attribute in query params instead of restful param.
                if (!in_array($attrName, $this->filterHelper->getFiltersToSkipForTire())) {
                    $request->setQueryValue($attrName, $data);
                } else {
                    $request->setParam(
                        $attrName,
                        $data
                    );
                }
            }
        }
