<?php
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        /** @var \Avatacar\Garage\Model\ResourceModel\Garage\Collection $collection */
        $collection = $this->garageCollectionFactory->create()->addAttributeToSelect('*');
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $filterField = [];
            $filterCondition = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filterField[] = $filter->getField();
                $filterCondition[] =  [$filter->getConditionType() ?: 'eq' => $filter->getValue()];
            }
            $collection->addFieldToFilter($filterField, $filterCondition);
        }

        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());

        return $searchResults;
