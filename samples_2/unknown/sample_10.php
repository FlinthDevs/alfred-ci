<?php
        $collection = $this->getRetailerCollection();
        $cacheKey = sprintf("%s_%s_%s", 'smile_storelocator_search', $collection->getStoreId(), serialize($this->getCity()));
        $markers = $this->cacheInterface->load($cacheKey);

        if (!$markers) {
            \Magento\Framework\Profiler::start('SmileStoreLocator:STORES');
            /** @var RetailerInterface $retailer */
            foreach ($collection as $retailer) {
                $address = $retailer->getExtensionAttributes()->getAddress();
                \Magento\Framework\Profiler::start('SmileStoreLocator:STORES_DATA');
                $markerData = [
                    'id' => $retailer->getId(),
                    'latitude' => $address->getCoordinates()->getLatitude(),
                    'longitude' => $address->getCoordinates()->getLongitude(),
                    'name' => $retailer->getName(),
                    'address' => $this->addressFormatter->formatAddress($address, AddressFormatter::FORMAT_ONELINE),
                    'url' => $this->storeLocatorHelper->getRetailerUrl($retailer),
                    'directionUrl' => $this->map->getDirectionUrl($address->getCoordinates()),
                    'setStoreData' => $this->getSetStorePostData($retailer),
                ];
                \Magento\Framework\Profiler::stop('SmileStoreLocator:STORES_DATA');
                foreach (['contact_mail', 'contact_phone', 'contact_mail'] as $contactAttribute) {
                    $markerData[$contactAttribute] = $retailer->getData($contactAttribute) ? $retailer->getData($contactAttribute) : '';
                }
                \Magento\Framework\Profiler::start('SmileStoreLocator:STORES_SCHEDULE');
                $markerData['schedule'] = array_merge(
                    $this->scheduleHelper->getConfig(),
                    [
                        'calendar' => $this->scheduleManager->getCalendar($retailer),
                        'openingHours' => $this->scheduleManager->getWeekOpeningHours($retailer),
                        'specialOpeningHours' => $retailer->getExtensionAttributes()->getSpecialOpeningHours(),
                    ]
                );
                \Magento\Framework\Profiler::stop('SmileStoreLocator:STORES_SCHEDULE');
                $markers[] = $markerData;
            }
            \Magento\Framework\Profiler::stop('SmileStoreLocator:STORES');

            $markers = $this->serializer->serialize($markers);
            $this->cacheInterface->save(
                $markers,
                $cacheKey,
                $this->getIdentities()
            );
        }

        return $this->serializer->unserialize($markers);
