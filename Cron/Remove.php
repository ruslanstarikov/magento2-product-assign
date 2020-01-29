<?php

namespace Triple888\ProductAssign\Cron;

use Magento\Catalog\Model\CategoryLinkRepository as LinkManagement;
use Triple888\ProductAssign\Model\ErrorManager;
use Triple888\ProductAssign\Helper\Data;

class Remove
{
    protected $_linkManagement;
    protected $_errorManager;
    protected $_productAssignHelper;

    public function __construct(
        LinkManagement $linkManagement,
        ErrorManager $errorManager,
        Data $productAssignHelper
    )
    {
        $this->_linkManagement = $linkManagement;
        $this->_errorManager = $errorManager;
        $this->_productAssignHelper = $productAssignHelper;
    }

    public function execute()
    {
        $skus = $this->_productAssignHelper->getSkusToDelete();
        $categories = $this->_productAssignHelper->getCategoriesToDelete();

        foreach ($categories as $category) {
            $category = trim($category);
            foreach ($skus as $sku) {
                $sku = trim($sku);
                $category = trim($category);
                try {
                    $this->_linkManagement->deleteByIds($category, $sku);
                    $this->_errorManager->addProcessed($sku);
                } catch (\Exception $e) {
                    $this->_errorManager->addWarning($sku);
                    continue;
                }
                if ($this->_productAssignHelper->includeConfigurableParentToDelete()) {
                    $configurableParent = $this->_productAssignHelper->getConfigurable($sku);
                    try {
                        if (!empty($configurableParent)) {
                            $this->_linkManagement->deleteByIds($category, $configurableParent);
                        }
                    } catch (\Exception $e) {
                        $this->_errorManager->addError($sku);
                        continue;
                    }
                }
            }
        }
    }

    public function getErrorManager()
    {
        return $this->_errorManager;
    }

}