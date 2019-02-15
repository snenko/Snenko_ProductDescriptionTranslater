<?php

class Snenko_ProductDescriptionTranslater_Model_System_Config_Source_Typetoexport
{

    protected $_options;

    const TYPE_XML = "XML";
    const TYPE_EXCEL = "EXCEL";

    public function toOptionArray()
    {
        if (!$this->_options) {

            $result[] = array('value' => self::TYPE_XML,    'label'=>self::TYPE_XML);
            $result[] = array('value' => self::TYPE_EXCEL,  'label'=>self::TYPE_EXCEL);

            $this->_options = $result;
        }
        return $this->_options;
    }

}