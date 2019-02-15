<?php

class Snenko_ProductDescriptionTranslater_Model_Attribute
{
    protected $_attributes;

    public function getAttributes()
    {
        if(!$this->_attributes){
            $this->_attributes = Mage::getModel("catalog/product")->getAttributes();
        }
        return $this->_attributes;
    }

    public function isExist($attributeName)
    {


        $attributes = $this->getAttributes();

        return isset($attributes[$attributeName]);


    }
}