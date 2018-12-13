<?php

        $categoryId = $productSku = 0;
        if ($this->getRequest()->getParam('category_path')) {
            $categoryId = $this->catalogHelper->getCategoryIdByUrlKey($this->getRequest()->getParam('category_path'));
        }
        if ($this->getRequest()->getParam('target_category')) {
            $categoryId = $this->getRequest()->getParam('target_category');
        }
        if ($this->getRequest()->getParam('product_sku')) {
            $productSku = $this->getRequest()->getParam('product_sku');
        }
        if ($this->getRequest()->getParam('postCode')) {
            $this->identificationSession->setData(
                \Avatacar\Vehicle\Helper\Identification::POST_CODE_SESSION_KEY,
                $this->getRequest()->getParam('postCode')
            );
        }
        if ($this->getRequest()->getParam('maintenance-plan')) {
            $this->identificationSession->setData(
                \Avatacar\Vehicle\Helper\Identification::MAINTENANCE_PLAN_KEY,
                $this->getRequest()->getParam('maintenance-plan')
            );
        }

        $result = $this->resultJsonFactory->create();
        $registrationId = $this->getRequest()->getParam('registrationId');
        $return = [
            'valid' => false,
            'level' => \Avatacar\Vehicle\Helper\Identification::NONE,
            'force_step' => \Avatacar\Vehicle\Helper\Identification::KTYPE_TIRE,
        ];
        if ($registrationId) {
            $this->customerHelper->updateLicencePlate($registrationId);
            $vehicleData = $this->identificationHelper->addIdentificationFromRegistration($registrationId);

            if ($this->getRequest()->getParam('mobile_registration')) {
                $bodiesMobile = $this->identificationHelper->getMobileBodiesByRegistrationId($registrationId);
                if ($bodiesMobile) {
                    return $result->setData($bodiesMobile);
                }
            }

            $currentLevel = $this->identificationHelper->getCurrentLevel();
            if ($currentLevel || $vehicleData) {
                $return['level'] = $currentLevel;
                $identificationData = $this->identificationHelper->getSessionData();
                if ($identificationData
                    && !$vehicleData
                    && !empty($identificationData['version'])
                    && !empty($identificationData['ktype'])
                ) {
                    $this->identificationSession->setData(
                        \Avatacar\Vehicle\Helper\Identification::REGISTRATION_KEY,
                        $registrationId
                    );
                    $resultPage = $this->resultPageFactory->create();
                    $return['valid'] = true;
                    $currentModelId = $this->identificationHelper->getCurrentModelId();
                    $bodies = $this->vehicleHelper->getBodiesByModelId($currentModelId);
                    $return['body_html'] = $resultPage->getLayout()
                        ->createBlock('\Avatacar\Vehicle\Block\Identification\Steps')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/versions.phtml')
                        ->setBodies($bodies)
                        ->toHtml();
                    $version = $identificationData['version'];
                    $return['body_title'] = '<span class="select-auto__name">'.$version['model_range'].' ';
                    $return['body_title'] .= $version['body_generation'].' - ';
                    $return['body_title'] .= $version['model_serie']
                        ? $version['model_serie']
                        : $version['model_door_number'].' portes';
                    $return['body_title'] .= ' </span>';

                    $endDate = $startDate = __('Aujourd\'hui');
                    if (strtotime($version['body_end_date']) > \Avatacar\Vehicle\Helper\Data::REFERENCE_TIME) {
                        $endDate = $this->vehicleHelper->formatDate($version['body_end_date']);
                    }
                    if (strtotime($version['body_start_date']) > \Avatacar\Vehicle\Helper\Data::REFERENCE_TIME) {
                        $startDate = $this->vehicleHelper->formatDate($version['body_start_date']);
                    }

                    $return['body_title'] .= '<span class="select-auto__date">'.__(' de ').$startDate
                        .__(' à ').$endDate.'</span>';

                    $ktypes = $this->vehicleHelper->getKtypesByBodyId($this->identificationHelper->getCurrentBodyId());
                    $return['ktype_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/ktype.phtml')
                        ->setKtypes($ktypes)
                        ->toHtml();
                    $return['ktype_title'] = $identificationData['ktype']['label'];

                    $tires = $this->vehicleHelper->getTiresByKtypeId($this->identificationHelper->getCurrentKTypeId());
                    $return['tire_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/tires.phtml')
                        ->setTires($tires['dimensions'])
                        ->toHtml();

                    $return['model_title'] = $identificationData['version']['brand_name'].' '.
                        $identificationData['version']['model_name'];
                } elseif ($vehicleData) {
                    $return['model_title'] = __('Je sélectionne ma voiture');
                    $this->identificationSession->setData('registrationId', $registrationId);
                    $resultPage = $this->resultPageFactory->create();
                    $return['force_step'] = \Avatacar\Vehicle\Helper\Identification::BODY;
                    $return['valid'] = true;
                    $bodies = $vehicleData['body'];
                    $formatedBodies = [];
                    foreach ($bodies as $body) {
                        $formatedBodies[] = $this->vehicleHelper->formatBodyForIdentification($body);
                    }
                    $return['body_html'] = $resultPage->getLayout()
                        ->createBlock('Avatacar\Vehicle\Block\Identification\Steps')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/versions.phtml')
                        ->setBodies($formatedBodies)
                        ->setNoCheck(true)
                        ->toHtml();
                    $return['body_title'] = '<span class="select-auto__name">'.
                        __('Je sélectionne ma version').'</span>';

                    $ktype = $vehicleData['ktype'];
                    $return['ktype_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/ktype.phtml')
                        ->setKtypes([
                            $ktype->getEnergy() => [$this->vehicleHelper->formatKtypeForIdentification($ktype)],
                        ])->toHtml();
                    $return['ktype_title'] = $vehicleData['ktype']->getLabel();

                    $tires = $this->vehicleHelper->getTiresByKtypeId($vehicleData['ktype']->getKtypeId());
                    $return['tire_html'] = $resultPage->getLayout()
                        ->createBlock('Magento\Framework\View\Element\Template')
                        ->setTemplate('Avatacar_Vehicle::identification/ajax/tires.phtml')
                        ->setTires($tires['dimensions'])
                        ->toHtml();
                }
            }
        }

        $targetCategory = $this->getRequest()->getParam('target_category');
        if ($targetCategory && !$this->getRequest()->getParam('ajax')) {
            $resultRedirect = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getUrl('vehicle/identification'));
            $this->identificationHelper->addCategorySessionData($targetCategory);
            $targetLevel = $this->identificationHelper->getSessionData('target_identification_level');

            if ($this->identificationHelper->checkIdentificationLevel($targetLevel)) {
                $redirectUrl = $this->identificationHelper->getSessionData('return_url');
                $resultRedirect->setUrl($redirectUrl);
            }

            return $resultRedirect;
        }
        if ($categoryId) {
            $this->identificationHelper->addCategorySessionData($categoryId);
            $return['target_level'] = $this->identificationHelper->getSessionData('target_identification_level');
        }
        if ($productSku) {
            $this->identificationHelper->addProductSessionData($productSku);
            $return['target_level'] = $this->identificationHelper->getSessionData('target_identification_level');
        }

        return $result->setData($return);

