<?php
public function fullFilterHighlightCollectionByTire($collection, $tireSessionData, $exclude = [])
    {
        foreach ($this->fullTireParamMapping as $key => $value) {
            // Ignores some attributes if specified (used for highlights)
            if (in_array($key, $exclude)) {
                continue;
            }

            if (isset($tireSessionData[$key]) && $tireSessionData[$key] != null) {
                $collection->addFieldToFilter(self::OPTION_TEXT.$value, $tireSessionData[$key]);
            }
        }
    }
