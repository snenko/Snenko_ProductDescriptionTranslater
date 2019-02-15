<?php

class Snenko_ProductDescriptionTranslater_Model_System_Config_Source_Visibility extends Mage_Catalog_Model_Product_Visibility
{
    public function toOptionArray()
    {
        return self::getAllOptions();
    }
}