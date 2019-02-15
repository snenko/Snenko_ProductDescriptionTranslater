<?php

class Snenko_ProductDescriptionTranslater_Model_Store {

    protected $_stores;

    public function getStores()
    {
        if(!$this->_stores){
            foreach (Mage::app()->getStores() as $store) {
                $_stores[ $store->getCode() ] = $store->getId();
            }
            $this->_stores = $_stores;
        }

        return $this->_stores;
    }

    public function getStoreIdByCode($code)
    {
        $stores = $this->getStores();
        return isset($stores[$code]) ? $stores[$code] : null;
    }

}