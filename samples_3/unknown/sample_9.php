<?php

        $requestPath = '';
        $paths = [];

        foreach (array_reverse($values, true) as $k => $value) {
            if (empty($value)) {
                break;
            }
            $requestPath = '/' . $value . $requestPath;
            $paths[$k] = $this->cleanupUrl(self::BASE_REQUEST_PATH . $requestPath);
        }

        return $paths;
