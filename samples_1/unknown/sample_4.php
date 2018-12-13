<?php

public function execute()
    {
        $toRedirect = $this->urlFilteringHelper->getTireCategoryUrl();
        $toRemove = IdentificationHelper::TIRE;
        if ($this->getRequest()->getParam('is_from_tire_category') != 1) {
            $toRedirect = self::ACTION_PATH;
            $this->identificationHelper->addCategorySessionData(
                $this->catalogHelper->getCategoryIdByUrlKey(self::WHEEL_PACK_URL_KEY)
            );
        } else {
            if ($this->session->getData(TireConfigurator::FORCE_NOT_IDENTIFIED_KEY)) {
                $this->session->setData('remove'.TireConfigurator::FORCE_NOT_IDENTIFIED_KEY, true);
            }
            $this->session->setData(TireConfigurator::FORCE_NOT_IDENTIFIED_KEY, true);
            $this->httpContext->setValue(TireConfigurator::FORCE_NOT_IDENTIFIED_KEY, true, false);
        }
        $this->identificationHelper->removeIdentificationLevel($toRemove);
        $this->_redirect($toRedirect);
    }
