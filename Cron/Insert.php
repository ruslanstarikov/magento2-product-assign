<?php

namespace Triple888\ProductAssign\Cron;

use Magento\Catalog\Api\CategoryLinkManagementInterface as LinkManagement;
use Triple888\ProductAssign\Model\ErrorManager;
use Triple888\ProductAssign\Helper\Data;

class Insert
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
        $skus = $this->_productAssignHelper->getSkus();
        $categories = $this->_productAssignHelper->getCategories();

        foreach ($skus as $sku) {
            $sku = trim($sku);
            try {
                $this->_linkManagement->assignProductToCategories($sku, $categories);
                $this->_errorManager->addProcessed($sku);
            } catch (\Exception $e) {
                $this->_errorManager->addWarning($sku);
                continue;
            }
            if ($this->_productAssignHelper->includeConfigurableParent()) {
                $configurableParent = $this->_productAssignHelper->getConfigurable($sku);
                try {
                    if (!empty($configurableParent)) {
                        $this->_linkManagement->assignProductToCategories($configurableParent, $categories);
                    }
                } catch (\Exception $e) {
                    $this->_errorManager->addError($sku);
                    continue;
                }
            }
        }
    }

    public function getErrorManager()
    {
        return $this->_errorManager;
    }

}