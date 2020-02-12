<?php

namespace Triple888\ProductAssign\Controller\Adminhtml\Assign;

use Triple888\ProductAssign\Cron\Insert as ProductAssigner;
use Triple888\ProductAssign\Logger\Logger;
use Magento\Backend\App\Action\Context as Context;

class Run
    extends \Magento\Backend\App\Action
{
    protected $_productAssigner;
    protected $_logger;
    protected $_messageManager;

    public function __construct(ProductAssigner $productAssigner, Logger $logger, Context $context)
    {
        $this->_productAssigner = $productAssigner;
        $this->_logger = $logger;
        $this->_messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        try {
            $this->_productAssigner->execute();
        } catch (\Exception $e) {
            $this->_messageManager->addNoticeMessage(__('Product assigner has finished with error'));
            $this->_logger->addError($e->getMessage());
        }

        $this->showResults();

        return $resultRedirect;
    }

    protected function showResults()
    {
        $errors = $this->_productAssigner->getErrorManager()->getErrors();
        $warnings = $this->_productAssigner->getErrorManager()->getWarnings();
        $processed = $this->_productAssigner->getErrorManager()->getProcessed();
        $nonExistent = $this->_productAssigner->getErrorManager()->getNonExistent();
        $noConfigurable = $this->_productAssigner->getErrorManager()->getNoConfigurable();
        $disabled = $this->_productAssigner->getErrorManager()->getDisables();

        $this->_messageManager->addNoticeMessage(__('Product assign has finished successfully'));

        if (count($errors) > 0) {
            $this->_messageManager->addErrorMessage(__('The process has not run successfully: ' . implode(',', $errors)));
        }
        if (count($warnings) > 0) {
            $this->_messageManager->addWarningMessage(__('Next products has not been assigned: ' . implode(',', $warnings)));
        }
        if (count($nonExistent) > 0) {
            $this->_messageManager->addWarningMessage(__('Next products do not exist: ' . implode(',', $nonExistent)));
        }
        if (count($noConfigurable) > 0) {
            $this->_messageManager->addWarningMessage(__('Next products has not configurable assigned: ' . implode(',', $noConfigurable)));
        }
        if (count($disabled) > 0) {
            $this->_messageManager->addWarningMessage(__('Some products are disabled: ' . implode(',', $disabled)));
        }

        if (count($processed) > 0) {
            $this->_messageManager->addSuccessMessage(count($processed) . __(' items have assigned successfully ') . PHP_EOL . implode(',', $processed));
        }
    }
}
