<?php

namespace Triple888\ProductAssign\Controller\Adminhtml\Assign;

use Triple888\ProductAssign\Cron\Insert as ProductAssigner;
use Triple888\Reindexer\Model\Indexer;
use Magento\Backend\App\Action\Context as Context;

class Run
    extends \Magento\Backend\App\Action
{
    protected $_productAssigner;
    protected $_indexer;
    protected $_messageManager;

    public function __construct(
        ProductAssigner $productAssigner,
        Indexer $indexer,
        Context $context
    )
    {
        $this->_indexer = $indexer;
        $this->_productAssigner = $productAssigner;
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
        }

        $this->showResults();

        if ($this->_indexer->reindexAll() == false ) {
            $this->_messageManager->addNoticeMessage(__('Indexer has not run successfully. Please run it manually.'));
        }

        return $resultRedirect;
    }

    protected function showResults()
    {
        $errors = $this->_productAssigner->getErrorManager()->getErrors();
        $warnings = $this->_productAssigner->getErrorManager()->getWarnings();
        $processed = $this->_productAssigner->getErrorManager()->getProcessed();

        $this->_messageManager->addNoticeMessage(__('Product assign has finished successfully'));

        if (count($errors) > 0) {
            $this->_messageManager->addErrorMessage(__('Some products has not configurable assigned: ' . implode(',', $errors)));
        }
        if (count($warnings) > 0) {
            $this->_messageManager->addWarningMessage(__('Next products has not been assigned: ' . implode(',', $warnings)));
        }
        if (count($processed) > 0) {
            $this->_messageManager->addSuccessMessage(count($processed) . __(' items have assigned successfully ') . PHP_EOL . implode(',', $processed));
        }
    }
}
