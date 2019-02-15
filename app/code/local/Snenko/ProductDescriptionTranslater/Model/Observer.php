<?php

class Snenko_ProductDescriptionTranslater_Model_Observer
{
    public function addMassAction($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'catalog_product')
        {
            $stores = Mage::getSingleton('adminhtml/system_config_source_store')->toOptionArray();
            $typetoexport = Mage::getSingleton('prodestransl/system_config_source_typetoexport')->toOptionArray();
            $attributes = Mage::getSingleton('prodestransl/system_config_source_attributes')->toOptionArray();
            $selectedFields = Mage::helper("prodestransl")->getExportFields();
//            array_unshift($stores, array('label'=>'', 'value'=>''));

            $block->addItem('prodestransl_export', array(
                'label' => Mage::helper("prodestransl")->__("Export Product's Descriptions for Translate"),
                'url' => Mage::helper('adminhtml')->getUrl('prodestransl/adminhtml_export/massrun'),
                'additional' => array(
                    'stores' => array(
                        'name' => 'stores',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Stores'),
                        'values' => $stores
                    ),
                    'type_file' => array(
                        'name' => 'type_file',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Type File'),
                        'values' => $typetoexport
                    ),
                    'fields' => array(
                        'name' => 'fields',
                        'type' => 'multiselect',
                        'class' => 'required-entry',
                        'label' => Mage::helper('catalog')->__('Fields'),
                        'values' => $attributes,
                        'value' => $selectedFields
                    )
                )
            ));
        }
    }

    /**
     *
     * TODO: добавити dropdown Stores, Excel|Xml
     * @param $observer
     * @return $this
     */
    public function addButtonTest($observer)
    {
//        $container = $observer->getBlock();
//        if(null !== $container && $container->getType() == 'adminhtml/catalog_product') {
//            /** @var Mage_Adminhtml_Block_Catalog_Product $container */
////            $container->add
//            $data = array(
//                'label'     => 'My button',
//                'class'     => 'some-class',
//                'onclick'   => 'setLocation(\' '  . Mage::getUrl('*/*', array('param' => 'value')) . '\')',
//            );
//            $container->addButton('my_button_identifier', $data);
//
//            'additional' => array(
//                'visibility' => array(
//                    'name' => 'status',
//                    'type' => 'select',
//                    'class' => 'required-entry',
//                    'label' => Mage::helper('catalog')->__('Status'),
//                    'values' => $statuses
//                )
//            )
//
//        }
//
//        return $this;
    }
}