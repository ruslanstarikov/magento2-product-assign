<?php

namespace Triple888\ProductAssign\Model;

class ErrorManager
{
    protected $_errors;
    protected $_warnings;
    protected $_successes;

    public function __construct()
    {
        $this->_errors = [];
        $this->_warnings = [];
        $this->_successes = [];
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
        $this->_successes[] = $processed;
    }

    public function getProcessed() : array
    {
        return $this->_successes;
    }

    public function addWarning(string $warning)
    {
        $this->_warnings[] = $warning;
    }

    public function getWarnings() : array
    {
        return $this->_warnings;
    }

}
