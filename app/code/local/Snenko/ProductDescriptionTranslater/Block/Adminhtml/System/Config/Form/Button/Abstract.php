<?php

class Snenko_ProductDescriptionTranslater_Block_Adminhtml_System_Config_Form_Button_Abstract
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    protected $_url = 'prodestransl/adminhtml_controller/run';
    protected $_template = 'prodestransl/system/config/form/button.phtml';
    protected $_label = 'button';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate($this->_template);
    }

    public function getLabel()
    {
        return $this->_label;
    }

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_toHtml();
    }

    public function getAjaxUrl()
    {
        return Mage::helper('adminhtml')->getUrl($this->_url);
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'prodestransl_button_'.$this->getLabel(),
                'label'     => $this->helper('adminhtml')->__('Start ' . $this->getLabel()),
                'onclick'   => 'javascript:'. $this->getLabel() . 'ButtonRun(); return false;'
            ));

        return $button->toHtml();
    }
}