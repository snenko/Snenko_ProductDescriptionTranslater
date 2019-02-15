<?php

class Snenko_ProductDescriptionTranslater_Model_System_Config_Source_Attributes extends Mage_Catalog_Model_Product
{
    protected $_options;

    protected $_allowedBackendTypes = array("text", "varchar");
//    protected $_compulsoryAttributeCodes = array("name");

    public function toOptionArray()
    {
        if (!$this->_options) {

            $attributes = $this->getAttributes();

            $result[] = array('value' => "", 'label'=>" -none- ");
            foreach ($attributes as $code=>$attribute) {
                if(in_array($attribute->getBackendType(), $this->_allowedBackendTypes)){
                    $result[] = array('value' => $code, 'label'=>$code);
                }
            }
            $this->_options = $result;
        }
        return $this->_options;
    }
}