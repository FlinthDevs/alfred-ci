<?php
if ($this->garages !== null) {
            try {
                $this->connection->beginTransaction();
                $this->saveGarages();
                $this->garages = null;
                $this->connection->commit();
            } catch (Exception $e) {
                $ids = [];
                foreach ($this->garages['flat'] as $flat) {
                    $ids[] = $flat['entity_id'];
                }
                $ids = implode(',', $ids);
                $this->logger->critical($e->getMessage());
                $this->logger->critical('We rollbacked the transaction related to theses Ids '.$ids);
                $this->setData('error_messages', $ids);
                $this->connection->rollBack();
            }

            // Handling invalid urlKey (garages already existing)
            if ($this->invalidUrlKeys !== []) {
                $invalidUrlKeysString = implode(",\n - ", $this->invalidUrlKeys);
                $this->logger->warning(
                    "The following URL keys were invalid and skipped during import: \n - ".$invalidUrlKeysString
                );
                $this->setData('urlkey_error_messages', 'Invalid URL keys for: '.$invalidUrlKeysString);
            }
        }
