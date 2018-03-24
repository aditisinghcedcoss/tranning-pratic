<?php

class Ced_Amazonimporter_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
       /* $this->_removeButton('add');*/
        $this->setId('productGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
            /*->addAttributeToFilter("ced_amazon", 1);*/
       /* print_r($collection); die;
   */     if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')){ $collection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', '{{table}}.stock_id=1', 'left'); }
        $this->setCollection($collection);


        return parent::_prepareCollection();
    }
    protected function _prepareMassaction()
    {
       $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_id');
        $this->getMassactionBlock()->addItem('Syn', array(
            'label'=> Mage::helper('Ced_Amazonimporter')->__('Sync'),
            'url'  => $this->getUrl('adminhtml/product/massSyn', array('' => '')),        // public function massDeleteAction() in Mage_Adminhtml_Tax_RateController
            'confirm' => Mage::helper('tax')->__('Are you sure?')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
            'label'=> Mage::helper('Ced_Amazonimporter')->__('Delete'),
            'url'  => $this->getUrl('adminhtml/product/massDelete', array('' => '')),        // public function massDeleteAction() in Mage_Adminhtml_Tax_RateController
            'confirm' => Mage::helper('tax')->__('Are you sure?')
        ));


        return $this;
    }

    protected function _prepareColumns()
    {
      $this->addColumn('entity_id', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('ID'),
            'align'     =>'right',
            'width'     => '10px',
            'index'     => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Name'),
            'align'     =>'left',
            'index'     => 'name',
            'width'     => '50px',
        ));
        $this->addColumn('qty', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Qty'),
            'align'     =>'left',
            'index'     => 'qty',
            'width'     => '50px',
        ));
        $this->addColumn('ced_large_image', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Image'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     => 'ced_large_image',
            'renderer' =>   'Ced_Amazonimporter_Block_Adminhtml_Product_Image'
        ));

        $this->addColumn('ced_asin', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('ASIN'),
            'align'     =>'left',
            'index'     => 'ced_asin',
            'width'     => '50px',
        ));
        $this->addColumn('price', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Price'),
            'align'     =>'left',
            'index'     => 'price',
            'width'     => '50px',
        ));
        $this->addColumn('ced_node_id', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Node Id'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     =>'ced_node_id'
        ));
        $this->addColumn('ced_node_name', array(
            'header'    => Mage::helper('Ced_Amazonimporter')->__('Node Name'),
            'align'     =>'left',
            'width'     => '50px',
            'index'     =>'ced_node_name'
        ));
        $this->addColumn('ced_iframe_url', array(
            'header'           => Mage::helper('Ced_Amazonimporter')->__('Review'),
            'align'            => 'center',
            'renderer'         => 'Ced_Amazonimporter_Block_Adminhtml_Product_Review',
            'index'            => 'ced_iframe_url',
            'width'     => '50px',
        ));
        return parent::_prepareColumns();



    }
}