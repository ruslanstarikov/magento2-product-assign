<?php

namespace Triple888\ProductAssign\Logger\Handler;

use Monolog\Logger;

class System
    extends \Magento\Framework\Logger\Handler\Base
{
    protected $fileName = '/var/log/productAssign.log';
    protected $loggerType = Logger::INFO;
}