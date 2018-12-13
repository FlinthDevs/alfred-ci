<?php
pubpublic function getRevisionConstructor()
    {
        return $this->getLayout()
                    ->createBlock('Magento\Cms\Block\Block')
                    ->setBlockId('revision_constructeur')->toHtml();
    }
