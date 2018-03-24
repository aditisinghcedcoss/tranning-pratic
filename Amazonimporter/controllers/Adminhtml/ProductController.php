<?php

class Ced_Amazonimporter_Adminhtml_ProductController extends Mage_Adminhtml_Controller_action
{

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getAmazonItemCsvAction()
    {
        Mage::helper('Ced_Amazonimporter')->newproduct();
        $this->_redirect('adminhtml/product/index');
    }

    public function massDeleteAction()
    {
        $entity_ids= $this->getRequest()->getParam('entity_id');      // $this->getMassactionBlock()->setFormFieldName('tax_id'); from Mage_Adminhtml_Block_Tax_Rate_Grid

        if(!is_array($entity_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Please select tax(es).'));
        } else {
            try {
                $rateModel = Mage::getModel('catalog/product');
                foreach ($entity_ids as $entity_id) {
                    $rateModel->load($entity_id)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Ced_Amazonimporter')->__(
                        'Total of %d record(s) were deleted.', count($entity_ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/product/index');
    }
    public function massSynAction()
    {
        $entity_ids= $this->getRequest()->getParam('entity_id');
        if(!is_array($entity_ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('tax')->__('Please select tax(es).'));
        } else {
            try {
                $product = Mage::getModel('catalog/product');
                foreach ($entity_ids as $entity_id) {

                   $products= $product->load($entity_id)->getData();
                    $awsitem = Mage::helper('Ced_Amazonimporter')->callOperation('search_asin', ['asin' =>$products['ced_asin']]);
                    foreach ($awsitem as $key => $data) {
                        if (@$data['Item']) {
                            $extractedData = Mage::helper('Ced_Amazonimporter')->extractInfo($data['Item']);
                            try {
                                if (empty($product->getIdByCedASIN($extractedData['asin'])) &&  isset($extractedData['asin']) && isset($extractedData['description']) ) {
                                    $product->setCedLargeImage($data['Item']['LargeImage']['URL']);
                                    $product->setCedSmallImage($data['Item']['SmallImage']['URL']);
                                    $product->setCedMediumImage($data['Item']['MediumImage']['URL']);
                                    $product->setCedAsin($extractedData['asin']);
                                    $product->setCedUrl($extractedData['amazon_product_url']);
                                    $product->setCedIFrameUrl($extractedData['reviews']);
                                    $product->setCedNodeId($extractedData['nodeId']);
                                    $product->setCedNodeName($extractedData['nodeName']);
                                    $product->setDescription($extractedData['description']);
                                    $product->setSortDescription($extractedData['short_description']);
                                    $product->setPrice($extractedData['price']);
                                    $product->setWeight($extractedData["weight"]);
                                    $product->setCedBrand($extractedData["brand"]);
                                    $product->setCedAmazon(1);
                                    $product->save();
                                }
                            } catch (\Exception $e) {
                                echo $e->getMessage();
                            }
                        }
                    }
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Ced_Amazonimporter')->__(
                        'Total of %d record(s) were Sync.', count($entity_ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('adminhtml/product/index');
    }
    public function importAction()
    {

        $csvdata = Mage::helper('Ced_Amazonimporter')->readCsv();
       if ($csvdata) {
            $productids = (array_chunk($csvdata, 10));
            Mage::getSingleton('adminhtml/session')->setProductChunkImport($productids);
            $this->loadLayout();
            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError('No Product Selected.');
            $this->_redirect('*/adminhtml_walmartrequest/uploadproduct');
        }
    }

    /**
     * Import product pne by one
     */
    public function startImportAction()
    {

        $message=array();
        $message['error']="";
        $message['success']="";
        $helper = Mage::helper('Ced_Amazonimporter');
        $key = $this->getRequest()->getParam('index');
        $chunk_data =array();
        $chunk_data = Mage::getSingleton( 'adminhtml/session' )->getProductChunkImport();
        $index = $key + 1;
        if(count($chunk_data) <= $index){
            Mage::getSingleton('adminhtml/session')->unsProductChunks();
        }

        if(isset($chunk_data[$key])){
            $product_ids= array();
            $product_ids= $chunk_data[$key];
            if($resultData = $helper->importAllAmazonItems($product_ids)){
                $message['success']=$message['success']."Batch $index products Upload Request Send Successfully Imported.";
                echo json_encode($message);
            }else{
                $message['error']=$message['error']."Batch $index included Product(s) data not found.";
                echo json_encode($message);
            }

        }
    }
}
