<?php
public function fullFilterHighlightCollectionByTire($collection, $tireSessionData)
    {
        foreach ($this->fullTireParamMapping as $key => $value) {
            if (isset($tireSessionData[$key]) && $tireSessionData[$key] != null) {
                $collection->addFieldToFilter(self::OPTION_TEXT.$value, $tireSessionData[$key]);
            }
        }
    }
