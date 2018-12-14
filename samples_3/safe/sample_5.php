<?php
        $searchResults = $this->searchResultFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Avatacar\Customer\Model\ResourceModel\Vehicle\Collection $collection */
        $collection = $this->vehicleFactory->create()->getCollection()->addFieldToSelect('*');
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $filterField = [];
            $filterCondition = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filterField[] = $filter->getField();
                $filterCondition[] =  [$filter->getConditionType() ?: 'eq' => $filter->getValue()];
            }
            $collection->addFieldToFilter($filterField, $filterCondition);
        }

        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }

        $collection->getSelect();

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
