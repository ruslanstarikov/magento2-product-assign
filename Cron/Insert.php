<?php

namespace Triple888\ProductAssign\Cron;

use Triple888\ProductAssign\Model\ErrorManager;
use Triple888\ProductAssign\Helper\Data;
use Triple888\Reindexer\Model\Indexer;
use Triple888\ProductAssign\Logger\Logger;

class Insert
{
    protected $_errorManager;
    protected $_productAssignHelper;
    protected $_indexer;
    protected $_logger;

    public function __construct(ErrorManager $errorManager, Data $productAssignHelper, Indexer $indexer, Logger $logger)
    {
        $this->_indexer = $indexer;
        $this->_errorManager = $errorManager;
        $this->_productAssignHelper = $productAssignHelper;
        $this->_logger = $logger;
    }

    public function execute()
    {
        $skus = $this->_productAssignHelper->getSkus();
        $category = $this->_productAssignHelper->getCategory();
        $existentSKUs = $this->_productAssignHelper->getExistentItems($skus);
        $nonExistentSKUs = array_diff($skus, $existentSKUs);
        $disabledProducts = array_keys($this->_productAssignHelper->getDisabledProducts($skus));
        $noConfigurableProduct = [];
        $successes = 0;
        $skuToInserts = [];

        if ($this->_productAssignHelper->includeConfigurableParent()) {
            foreach ($existentSKUs as $sku) {
                $configurableParent = $this->_productAssignHelper->getConfigurable($sku);
                if (!empty($configurableParent)) {
                    $skuToInserts[] = $sku;
                    $skuToInserts[] = $configurableParent;
                } else {
                    $this->_errorManager->addNoConfigurable($sku);
                    $noConfigurableProduct[] = $sku;
                }
            }
        }

        try {
            $successes = $this->_productAssignHelper->assignProductToCategories($skuToInserts, $category);

            if ($this->_indexer->reindexAll() == false ) {
                $this->_errorManager->addWarning('Indexer has not run successfully. Please run it manually.');
            }

        } catch (\Exception $exception) {
            $this->_logger->addError($exception->getMessage());
            $this->_errorManager->addError($exception->getMessage());
        }

        $this->_errorManager->setProcessed($skuToInserts);
        $this->_errorManager->setNonExistent($nonExistentSKUs);
        $this->_errorManager->setDisabled($disabledProducts);

        $this->_logger->addInfo($successes . ' products have been assigned into ' . $category . ' category ID');
        $this->_logger->addInfo('SKUs: ' . implode(',', $skuToInserts));
        $this->_logger->addWarning('non-existent SKUs: ' . implode(',',$nonExistentSKUs));
        $this->_logger->addWarning('The next products have not configurable associated ' .implode(',', $noConfigurableProduct));
        $this->_logger->addWarning('Disabled products: ' . implode(',', $disabledProducts));
    }

    public function getErrorManager()
    {
        return $this->_errorManager;
    }

}