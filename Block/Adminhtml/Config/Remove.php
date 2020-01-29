<?php
namespace Triple888\ProductAssign\Block\Adminhtml\Config;

use Magento\Backend\Block\Template\Context;

class Remove
    extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Path to block template
     */
    const BUTTON_TEMPLATE = 'system/config/remove.phtml';

    public function __construct(Context $context, $data = array())
    {
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }

        return $this;
    }

    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->addData(
            [
                'url'     => $this->getUrl(),
                'html_id' => $element->getHtmlId(),
                'on_click' => 'deleteConfirm(\'' . __(
                        'Are you sure you want to do this?'
                    ) . '\', \'' . $this->getUrl() . '\')',
            ]
        );

        return $this->_toHtml();
    }

    public function getUrl($route = '', $params = [])
    {
        return parent::getUrl('products/remove/run');
    }

}