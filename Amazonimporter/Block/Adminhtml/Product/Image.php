<?php

Class Ced_Amazonimporter_Block_Adminhtml_Product_Image extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {

        $value = $row->getCedLargeImage();
        return '<img src="' . $value . '"  width="80" />';
    }
}