<?php

namespace Triple888\ProductAssign\Controller\Adminhtml\Remove;

use Triple888\ProductAssign\Cron\Remove as Disassociator;
use Triple888\Reindexer\Model\Indexer;
use Magento\Backend\App\Action\Context as Context;

class Run
    extends \Magento\Backend\App\Action
{
    protected $_indexer;
    protected $_productDisassociator;
    protected $_messageManager;

    public function __construct(
        Disassociator $productDisassociator,
        Indexer $indexer,
        Context $context
    )
    {
        $this->_indexer = $indexer;
        $this->_productDisassociator = $productDisassociator;
        $this->_messageManager = $context->getMessageManager();
        parent::__construct($context);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererUrl();

        try {
            $this->_productDisassociator->execute();
        } catch (\Exception $e) {
            $this->_messageManager->addNoticeMessage(__('Product disassociator has finished with error'));
        }

        $this->showResults();

        if ($this->_indexer->reindexAll() == false ) {
            $this->_messageManager->addNoticeMessage(__('Indexer has not run successfully. Please run it manually.'));
        }

        return $resultRedirect;
    }

    protected function showResults()
    {
        $errors = $this->_productDisassociator->getErrorManager()->getErrors();
        $warnings = $this->_productDisassociator->getErrorManager()->getWarnings();
        $processed = $this->_productDisassociator->getErrorManager()->getProcessed();

        $this->_messageManager->addNoticeMessage(__('Product deletion has finished successfully'));

        if (count($errors) > 0) {
            $this->_messageManager->addErrorMessage(__('Some products has not configurable associated: ' . implode(',', $errors)));
        }
        if (count($warnings) > 0) {
            $this->_messageManager->addWarningMessage(__('Next products has not been deleted from the category: ' . implode(',', $warnings)));
        }
        if (count($processed) > 0) {
            $this->_messageManager->addSuccessMessage(count($processed) . __(' items have been removed successfully ') . PHP_EOL . implode(',', $processed));
        }
    }
}
