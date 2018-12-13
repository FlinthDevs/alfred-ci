<?php
public function getRevisionBudget()
    {
        $result = '';

        try {
            $block = $this->getLayout()->createBlock('Magento\Cms\Block\Block');
            $result = $block->setBlockId('revision_budget')->toHtml();
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }

        return $result;
    }
