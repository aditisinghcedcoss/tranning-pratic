<?php
// Installer file to create an attribute name "approved" inside Default attribute set
$installer = $this;
$installer->startSetup();

$eavSetup = new Mage_Eav_Model_Entity_Setup('core_setup');
$sNewSetName = 'Amazon Inporter';
$catalogEntityTypeId = (int)$eavSetup->getEntityTypeId('catalog_product');
$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection');
$attributeSetCollection->setEntityTypeFilter($catalogEntityTypeId); // 4 is Catalog Product Entity Type ID
$attributeSetnames = array();
foreach ($attributeSetCollection as $id => $attributeSet) {
    $eavSetup->addAttributeGroup('catalog_product', $attributeSet->getAttributeSetName(), $sNewSetName, 2000);
}
$json2 = file_get_contents(__DIR__.'/amazon2.json');
$jsondata1=json_decode($json2,1);
$attribute=array_unique($jsondata1);

foreach($attribute as $key){
    if( $key=='ItemLinks' || $key =='Name' ||$key=='Languages' || $key=='Language' || $key =='ASIN'
        || $key=='@attributes' || $key =='Binding' || $key=='BindingBinding' || $key=='HardwarePlatform' || $key=='IsAdultProduct' || $key =='Languages' ||$key=='Manufacturer' || $key=='BrowseNodeId'
        || $key=='IsCategoryRoottecxt' || $key =='Ancestors' || $key=='BrowseNodes'||  $key=='Price'|| $key=='IsEligibleForSuperSaverShipping' || $key=='BrowseNodesBrowseNodes' || $key=='BrowseNodeIdBrowseNodeId'
        || $key=='AncestorsAncestors' || $key=='IsCategoryRoottecxt' || $key=='IFrameURL' || $key=='SmallImage'|| $key=='MediumImage' || $key=='LargeImage'){
    }
    else {
        $attr = Mage::getResourceModel('catalog/eav_attribute');
        if (!$attr->loadByCode('catalog_product', 'amazon_product_url')->getId()) {
            $eavSetup->addAttribute('catalog_product', 'ced_'.trim($key), array(
                'group' => 'Amazon Inporter',
                'label' => trim($key),
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_'.trim($key)
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_amazon', array(
                'group'    => 'Amazon Inporter',
                'label'    => 'ced_amazon',
                'type' => 'int',
                'input'    => 'boolean',
                'visible'  => false,
                'required' => false,
                'global'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note'     => "ced_amazon"
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_asin', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_asin',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_asin'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_small_image', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_small_image',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_small_image'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_medium_image', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_medium_image',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_medium_image'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_node_name', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_node_name',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_node_name'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_large_image', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_large_image',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_large_image'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_asin', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_asin',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_asin'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_iframe_url', array(
                'group' => 'Amazon Inporter',
                'label' => 'ced_iframe_url',
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_iframe_url'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_item_note', array(
                'group' => 'Amazon Inporter',
                'label' => "ced_item_note",
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_item_note'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_node_id', array(
                'group' => 'Amazon Inporter',
                'label' => "ced_node_id",
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_node_id'
            ));
            $eavSetup->addAttribute('catalog_product', 'ced_swatch_image', array(
                'group' => 'Amazon Inporter',
                'label' => "ced_swatch_image",
                'type' => 'text',
                'input' => 'text',
                'visible' => false,
                'required' => false,
                'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                'note' => 'ced_swatch_image'
            ));
        }
    }
}
$installer->endSetup();
?>