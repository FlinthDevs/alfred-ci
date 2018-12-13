<?php
public function getRevisionBudget()
    {
        return $this->getLayout()
                    ->createBlock('Magento\Cms\Block\Block')
                    ->setBlockId('revision_budget')->toHtml();
    }

