<?php
class Ced_Amazonimporter_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {

        $this->_controller = 'adminhtml_product';
        $this->_blockGroup = 'ced_amazonimporter';
        $this->_headerText = Mage::helper('Ced_Amazonimporter')->__(' Product Manager');
        /*$this->_addButtonLabel = Mage::helper('Ced_Amazonimporter')->__('Import');*/
        parent::__construct();
        $this->_removeButton('add');
    }
    protected function _prepareLayout()
    {
       $this->_addButton('Import Product', array(
            'label'   => Mage::helper('Ced_Amazonimporter')->__('Import Product'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/product/import')}')",
            'class'   => 'add'
        ));

        $this->_addButton('Get Product Csv', array(
            'label'   => Mage::helper('Ced_Amazonimporter')->__('Get Product Csv'),
            'onclick' => "setLocation('{$this->getUrl('adminhtml/product/getAmazonItemCsv')}')",
            'class'   => 'add'
        ));

        $this->setChild('grid', $this->getLayout()->createBlock('adminhtml/catalog_product_grid', 'product.grid'));
        return parent::_prepareLayout();
    }
}