<?php

namespace Triple888\ProductAssign\Model;

class ErrorManager
{
    protected $_errors;
    protected $_warnings;
    protected $_processed;
    protected $_disabled;
    protected $_nonexistent;
    protected $_noConfigurable;

    public function __construct()
    {
        $this->_errors = [];
        $this->_warnings = [];
        $this->_processed = [];
        $this->_disabled = [];
        $this->_nonexistent = [];
        $this->_noConfigurable = [];
    }

    public function addError(string $error)
    {
        $this->_errors[] = $error;
    }

    public function getErrors() : array
    {
        return $this->_errors;
    }

    public function addProcessed(string $processed)
    {
        $this->_processed[] = $processed;
    }
    public function setProcessed(array $processed)
    {
        $this->_processed = $processed;
    }

    public function getProcessed() : array
    {
        return $this->_processed;
    }

    public function addWarning(string $warning)
    {
        $this->_warnings[] = $warning;
    }

    public function getWarnings() : array
    {
        return $this->_warnings;
    }

    public function addDisabled(string $sku)
    {
        $this->_disabled[] = $sku;
    }

    public function setDisabled(array $SKUs)
    {
        $this->_disabled = $SKUs;
    }

    public function getDisables() : array
    {
        return $this->_disabled;
    }

    public function addNonExistent(string $sku)
    {
        $this->_nonexistent[] = $sku;
    }

    public function setNonExistent(array $SKUs)
    {
        $this->_nonexistent = $SKUs;
    }

    public function getNonExistent() : array
    {
        return $this->_nonexistent;
    }

    public function addNoConfigurable(string $sku)
    {
        $this->_noConfigurable[] = $sku;
    }

    public function setNoConfigurable(array $SKUs)
    {
        $this->_noConfigurable = $SKUs;
    }

    public function getNoConfigurable() : array
    {
        return $this->_noConfigurable;
    }

}
