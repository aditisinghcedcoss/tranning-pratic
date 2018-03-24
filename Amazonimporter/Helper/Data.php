<?php

class Ced_Amazonimporter_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var
     */
    private $service = "AWSECommerceService";
    /**
     * @var
     */
    private $responseGroup = "Images,ItemAttributes,Reviews,BrowseNodes,Offers,OfferSummary";
    /**
     * @var
     */
    private $urlAppend = "/onca/xml";
    /**
     * @var
     */
    public $timestamp;
    /**
     * @var
     */
    public $serviceUrl;
    /**
     * @var
     */
    public $sellerId;
    /**
     * @var
     */
    public $marketplaceId;
    /**
     * @var
     */
    public $awsAccessKeyId;
    /**
     * @var
     */
    public $mwsAuthToken;
    /**
     * @var
     */
    public $secretId;
    /**
     * @var
     */
    public $keyId;
    /**
     * @var
     */
    public $associateTag;
    /**
     * @var
     */
    public $secretkey;
    /**
     * @var
     */
    public $endPoint;
    /**
     * @var mixed
     */
    public $secretKey;
    private $finalUrl = "";
    public $throttleLimit = 1;

    public function __construct()
    {
        $this->secretId = Mage::getStoreConfig('amazonimporter/general/secret_id');
        $this->keyId = Mage::getStoreConfig('amazonimporter/general/api_key');
        $this->associateTag = Mage::getStoreConfig('amazonimporter/general/tag_key');
        $this->secretKey = Mage::getStoreConfig('amazonimporter/general/secret_key');
        /* print_r($this->secretkey); die;*/
        $this->endPoint = Mage::getStoreConfig('amazonimporter/general/endpoint_url');
    }

    public function newproduct()
    {
        $this->timestamp = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $this->serviceUrl = Mage::getStoreConfig('amazonimporter/general/server_url');
        $this->sellerId = Mage::getStoreConfig('amazonimporter/general/seller_id');
        $this->marketplaceId = Mage::getStoreConfig('amazonimporter/general/marketplace_id');
        $this->awsAccessKeyId = Mage::getStoreConfig('amazonimporter/general/assess_keyid');
        $this->mwsAuthToken = Mage::getStoreConfig('amazonimporter/general/auth_id');

        $params = [
            'AWSAccessKeyId' => $this->awsAccessKeyId,
            'Action' => 'RequestReport',
            'Merchant' => $this->sellerId,
            'MWSAuthToken' => $this->mwsAuthToken,
            'SignatureVersion' => '2',
            'Timestamp' => $this->timestamp,
            'Version' => '2009-01-01',
            'SignatureMethod' => 'HmacSHA256',
            'ReportType' => '_GET_MERCHANT_LISTINGS_ALL_DATA_',
            'MarketplaceIdList.Id.1' => $this->marketplaceId,
            'StartDate' => '2016-11-30T19:00:00Z',
            'EndDate' => '2018-03-20T19:00:00Z'
        ];
        uksort($params, 'strcmp');
        $string_to_sign = $this->_calculateStringToSignV2($params);
        $signature = $this->_sign($string_to_sign, $this->secretId, 'HmacSHA256');
        $params['Signature'] = $signature;
        $RequestReport = $this->postRequest($this->serviceUrl, $params);
        sleep(10);
        $parser = Mage::helper('Ced_Amazonimporter/parser');
        $RequestReport = $parser->loadXML($RequestReport)->xmlToArray();
        $ReportRequestId='';
        if (isset($RequestReport['RequestReportResponse']['RequestReportResult']['ReportRequestInfo']['ReportRequestId'])) {
            $ReportRequestId = $RequestReport['RequestReportResponse']['RequestReportResult']['ReportRequestInfo']['ReportRequestId'];
        }

        echo $ReportRequestId;
        echo "<br>";

        if(Mage::getSingleton('adminhtml/session')->getReportRequestId())
        {
            $ReportRequestId = Mage::getSingleton('adminhtml/session')->getReportRequestId();
        }else{
            Mage::getSingleton('adminhtml/session')->setReportRequestId($ReportRequestId);
        }

        $params = [
            'AWSAccessKeyId' => $this->awsAccessKeyId,
            'Action' => 'GetReportList',
            'Merchant' => $this->sellerId,
            'MWSAuthToken' => $this->mwsAuthToken,
            'SignatureVersion' => '2',
            'Timestamp' => $this->timestamp,
            'Version' => '2009-01-01',
            'SignatureMethod' => 'HmacSHA256',
            'ReportType' => '_GET_MERCHANT_LISTINGS_ALL_DATA_',
            'MarketplaceIdList.Id.1' => $this->marketplaceId,
            'AvailableFromDate' => '2016-11-30T19:00:00Z',
            'AvailableToDate' => '2017-12-13T19:00:00Z',
            'ReportRequestIdList.Id.1' => $ReportRequestId
        ];
        uksort($params, 'strcmp');
        $string_to_sign = $this->_calculateStringToSignV2($params);
        $signature = $this->_sign($string_to_sign, $this->secretId, 'HmacSHA256');
        $params['Signature'] = $signature;
        sleep(10);
        $GetReportList = $this->postRequest($this->serviceUrl, $params);
        $GetReportList = $parser->loadXML($GetReportList)->xmlToArray();
        $ReportId = null;
        if (isset($GetReportList['GetReportListResponse']['GetReportListResult']['ReportInfo']['ReportId'])) {
            $ReportId = $GetReportList['GetReportListResponse']['GetReportListResult']['ReportInfo']['ReportId'];
        }
        if($ReportId==null)
        {
            $this->newproduct();
        }
        $params = [
            'AWSAccessKeyId' => $this->awsAccessKeyId,
            'Action' => 'GetReport',
            'Merchant' => $this->sellerId,
            'MWSAuthToken' => $this->mwsAuthToken,
            'SignatureVersion' => '2',
            'Timestamp' => $this->timestamp,
            'Version' => '2009-01-01',
            'SignatureMethod' => 'HmacSHA256',
            'ReportId' => $ReportId
        ];
        uksort($params, 'strcmp');
        $string_to_sign = $this->_calculateStringToSignV2($params);
        $signature = $this->_sign($string_to_sign, $this->secretId, 'HmacSHA256');
        $params['Signature'] = $signature;
        sleep(10);
        $FinalReport = $this->postRequest($this->serviceUrl, $params);
        $FinalReport = str_replace(",", '', $FinalReport);
        $FinalReport = str_replace("\t", ',', $FinalReport);
        file_put_contents(__DIR__ . '/ItemReportAmazon.csv', $FinalReport);
        Mage::getSingleton('adminhtml/session')->unsReportRequestId();
       return 1;
    }

    public function postRequest($url, $params = [])
    {
        try {
            $headers = array();
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url/*'https://mws.amazonservices.com/'*/);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            return $serverOutput;

        } catch (\Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * Computes RFC 2104-compliant HMAC signature.
     */
    private function _sign($data, $key, $algorithm)
    {
        if ($algorithm === 'HmacSHA1') {
            $hash = 'sha1';
        } else if ($algorithm === 'HmacSHA256') {
            $hash = 'sha256';
        } else {
            throw new Exception ("Non-supported signing method specified");
        }
        return base64_encode(
            hash_hmac($hash, $data, $key, true)
        );
    }


    private function _urlencode($value)
    {
        return str_replace('%7E', '~', rawurlencode($value));
    }

    /**
     * Convert paremeters to Url encoded query string
     */
    private function _getParametersAsString(array $parameters)
    {
        $queryParameters = array();
        foreach ($parameters as $key => $value) {
            $queryParameters[] = $key . '=' . $this->_urlencode($value);
        }
        return implode('&', $queryParameters);
    }

    private function _calculateStringToSignV2(array $parameters, $uri = NULL)
    {
        $data = 'POST';
        $data .= "\n";
        $endpoint = parse_url($this->serviceUrl);
        $data .= $endpoint['host'];
        $data .= "\n";
        if (!isset ($uri)) {
            $uri = "/";
        }
        $uriencoded = implode("/", array_map(array($this, "_urlencode"), explode("/", $uri)));
        $data .= $uriencoded;
        $data .= "\n";
        uksort($parameters, 'strcmp');
        $data .= $this->_getParametersAsString($parameters);
        return $data;
    }


    public function readCsv()
    {
        $csv = new Varien_File_Csv();
        $data = $csv->getData(__DIR__ . '/ItemReportAmazon.csv');
        unset($data[0]);
        return $data;
    }


    public function importAllAmazonItems($productData)
    {
        foreach ($productData as $key => $value) {
            $product = Mage::getModel('catalog/product');
            if (empty($product->getIdBySku($value[3]))) {
                $product = Mage::getModel('catalog/product');


                $awsitem = Mage::helper('Ced_Amazonimporter')->callOperation('search_asin', ['asin' => $value[16]]);
                foreach ($awsitem as $key => $data) {
                    if (@$data['Item']) {
                        $extractedData = $this->extractInfo($data['Item']);
                        try {
                            if ( isset($extractedData['asin']) && isset($extractedData['description'])) {
                                $product->setCedLargeImage(@$data['Item']['LargeImage']['URL']);
                                $product->setCedSmallImage(@$data['Item']['SmallImage']['URL']);
                                $product->setCedMediumImage(@$data['Item']['MediumImage']['URL']);
                                $product->setCedSwatchImage(@$data['Item']['ImageSets']['ImageSet'][0]['SwatchImage']['URL']);
                                $product->setCedAsin(@$extractedData['asin']);
                                $product->setCedUrl(@$extractedData['amazon_product_url']);
                                $product->setCedIFrameUrl(@$extractedData['reviews']);
                                $product->setCedNodeId(@$extractedData['nodeId']);
                                $product->setCedNodeName(@$extractedData['nodeName']);
                                $product->setDescription(@$extractedData['description']);
                                $product->setSortDescription(@$extractedData['short_description']);
                                $product->setPrice(@$extractedData['price']);
                                $product->setWeight(@$extractedData["weight"]);
                                $product->setCedBrand(@$extractedData["brand"]);
                            }
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
                $product->setName($value[0]);
                $product->setSku($value[3]);
                $product->setStatus(1);
                $product->setStockData(['qty' => $value[5], 'manage_stock' => 1, 'is_in_stock' => 1]);
                $product->setVisibility(4);
                $product->setWebsiteIds([1]);
                $product->setAttributeSetId(4);
                $product->setUrlKey($value[3]);
                $product->setTypeId('simple');
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $stockItem->setData('qty', @$value[5]);
                $stockItem->save();

                try {
                    $product->addImageToMediaGallery($extractedData['Imagepath_large'], array('image'), false, false);
                    $product->addImageToMediaGallery($extractedData['Imagepath_Small'], array('small_image'), false, false);
                    //$product->addImageToMediaGallery($extractedData['Imagepath_SwatchImage'], array('swatch_image'), false, false);
                    $product->addImageToMediaGallery($extractedData['Imagepath_Medium'], array( 'thumbnail'), false, false);
                    $product->save();
                } catch (\Exception $e) {
                    $e->getMessage();
                }
            } else {
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $value[3]);
                $awsitem = Mage::helper('Ced_Amazonimporter')->callOperation('search_asin', ['asin' => $value[16]]);
                foreach ($awsitem as $key => $data) {
                    if (@$data['Item']) {
                        $extractedData = $this->extractInfo($data['Item']);
                        try {
                            if (  isset($extractedData['asin']) && isset($extractedData['description'])) {
                                $product->setCedLargeImage(@$data['Item']['LargeImage']['URL']);
                                $product->setCedSmallImage(@$data['Item']['SmallImage']['URL']);
                                $product->setCedMediumImage(@$data['Item']['MediumImage']['URL']);
                                $product->setCedSwatchImage(@$data['Item']['ImageSets']['ImageSet'][0]['SwatchImage']['URL']);
                                $product->setCedAsin(@$extractedData['asin']);
                                $product->setCedUrl(@$extractedData['amazon_product_url']);
                                $product->setCedIFrameUrl(@$extractedData['reviews']);
                                $product->setCedNodeId(@$extractedData['nodeId']);
                                $product->setCedNodeName(@$extractedData['nodeName']);
                                $product->setDescription(@$extractedData['description']);
                                $product->setSortDescription(@$extractedData['short_description']);
                                $product->setPrice(@$extractedData['price']);
                                $product->setWeight(@$extractedData["weight"]);
                                $product->setCedBrand(@$extractedData["brand"]);
                                $product->setCedAmazon(1);

                            }
                        } catch (\Exception $e) {
                             $e->getMessage();
                        }
                    }
                }
                $product->setName(@$value[0]);
                $product->setSku('*'.@$value[3]);
                $product->setStatus(1);
                $product->setVisibility(4);
                $product->setWebsiteIds([1]);
                $product->setAttributeSetId(4);
                $product->setUrlKey(@$value[3]);
                $product->setTypeId('simple');
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
                $stockItem->setData('qty', @$value[5]);
                $stockItem->save();
                //print_r($product); die;
                try {
                    $product->addImageToMediaGallery(@$extractedData['Imagepath_large'], array('image'), false, false);
                    $product->addImageToMediaGallery(@$extractedData['Imagepath_Small'], array('small_image'), false, false);
                    //$product->addImageToMediaGallery(@$extractedData['Imagepath_SwatchImage'], array('swatch_image'), false, false);
                    $product->addImageToMediaGallery(@$extractedData['Imagepath_Medium'], array('thumbnail'), false, false);
                    $product->getResource()->save($product);
                } catch (\Exception $e) {
                    $e->getMessage();

                }
            }
        }
        return true;
    }

    public function send()
    {
        $url = $this->finalUrl;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $retValue = curl_exec($ch);
        curl_close($ch);
        $simpleXml = simplexml_load_string($retValue);
        $jsonResponse = json_encode($simpleXml);
        $response = json_decode($jsonResponse, true);
        sleep($this->throttleLimit);
        return $response;
    }

    public function searchAmazonProduct($aObj, $keyterms, $searchIndex = 'All', $itemPage, $brand)
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestData = [
            "AssociateTag" => $this->associateTag,
            "AWSAccessKeyId" => $this->keyId,
            "Service" => $this->service,
            "Operation" => "ItemSearch",
            "SearchIndex" => $searchIndex,
            "Keywords" => $keyterms,
            "Timestamp" => $timestamp,
            "Availability" => $this->availability,
            "ResponseGroup" => $this->responseGroup,
            "ItemPage" => $itemPage
        ];
        if ($brand != 'none') $requestData["Brand"] = $brand;
        ksort($requestData);
        $urlContent = array();
        foreach ($requestData as $key => $value) {
            array_push($urlContent, rawurlencode($key) . "=" . rawurlencode($value));
        }
        $urlPre = join("&", $urlContent);
        $urlPost = "GET\n" . $this->endPoint . "\n" . $this->urlAppend . "\n" . $urlPre;
        $signature = base64_encode(hash_hmac("sha256", $urlPost, $this->secretKey, true));
        $this->finalUrl = 'http://' . $this->endPoint . $this->urlAppend . '?' . $urlPre . '&Signature=' . rawurlencode($signature);
        return $this;
    }

    public function callOperation($operation, $searchData = [])
    {
        $searchObj = Mage::Helper('Ced_Amazonimporter');
        switch ($operation) {
            case 'search_product' :
                $itemPage = $searchData['pageNumber'];
                $brand = $searchData['brand'];
                $response = $searchObj->searchAmazonProduct($this, $searchData['search_data'],
                    $searchData['category'], $itemPage, $brand)->send();
                if (!$response) return ['error' => 'Amazon : Throttle Limit Exceeded. Please try after some time.'];
                return $response;
                break;

            case 'search_asin' :
                $asin = str_replace(' ', '', $searchData['asin']);
                $response = $searchObj->getProductbyBarcode($this, $asin)->send();
                if (!$response) return ['error' => $asin . ': is(are) not valid'];
                return $response;
                break;

            case 'search_node' :
                $itemPage = isset($searchData['item_page']) ? $searchData['item_page'] : 1;
                $response = $searchObj->categoryProducts($this, $searchData['nodeid'], $searchData['category'], $itemPage)->send();
                if (!$response) return ['error' => 'Amazon : Throttle Limit Exceeded. Please try after some time.'];
                return $response;
                break;

            default :
                return ['error' => 'Invalid amazon operation passed'];
                break;
        }
    }

    public function extractInfo($item)
    {
        if (isset($item['ASIN'])) {
            if (isset($item['Variations'])) {
                try {
                    $individualItem = [];
                    $individualItem['name'] = $item['ItemAttributes']['Title'];
                    $individualItem['asin'] = $item['ASIN'];
                    $individualItem['reviews'] = $item['CustomerReviews']['IFrameURL'];
                    $individualItem['amazon_product_url'] = $item['DetailPageURL'];
                    $fullDescription = $this->prepareDescription($item['ItemAttributes']);
                    if (!isset($item['BrowseNodes']['BrowseNode']['BrowseNodeId'])) {
                        end($item['BrowseNodes']['BrowseNode']);
                        $key = key($item['BrowseNodes']['BrowseNode']);
                        $individualItem['nodeId'] = isset($item['BrowseNodes']['BrowseNode'][$key]['BrowseNodeId']) ?
                            $item['BrowseNodes']['BrowseNode'][$key]['BrowseNodeId'] : '';
                        $individualItem['nodeName'] = isset($item['BrowseNodes']['BrowseNode'][$key]['Name']) ?
                            $item['BrowseNodes']['BrowseNode'][$key]['Name'] : '';
                    } else {
                        $individualItem['nodeId'] = isset($item['BrowseNodes']['BrowseNode']['BrowseNodeId']) ?
                            $item['BrowseNodes']['BrowseNode']['BrowseNodeId'] : '';
                        $individualItem['nodeName'] = isset($item['BrowseNodes']['BrowseNode']['Name']) ?
                            $item['BrowseNodes']['BrowseNode']['Name'] : '';
                    }
                    $individualItem['short_description'] = !empty($fullDescription['short_desc']) ?
                        $fullDescription['short_desc'] : '';
                    $individualItem['description'] = !empty($fullDescription['desc']) ? $fullDescription['desc'] : '';

                    if (isset($item['Offers']['Offer']['OfferListing']['Price']['Amount']) &&
                        is_numeric($item['Offers']['Offer']['OfferListing']['Price']['Amount'])
                    ) {
                        $price = number_format((float)($item['Offers']['Offer']['OfferListing']['Price']['Amount'] / 100), 2, '.', '');
                    } elseif (isset($item['ItemAttributes']['ListPrice']['Amount']) && is_numeric($item['ItemAttributes']['ListPrice']['Amount'])) {
                        $price = number_format((float)($item['ItemAttributes']['ListPrice']['Amount'] / 100), 2, '.', '');
                    } elseif (isset($item['OfferSummary']['LowestNewPrice']['Amount']) &&
                        is_numeric($item['OfferSummary']['LowestNewPrice']['Amount'])
                    ) {
                        $price = number_format((float)($item['OfferSummary']['LowestNewPrice']['Amount'] / 100), 2, '
                        .', '');
                    } else {
                        $price = 0.00;
                    }
                    $individualItem['price'] = (isset($item['OfferSummary']['TotalNew']) && ($item['OfferSummary']['TotalNew'] > 0) && ($price != 0.00)) ? $price : 0;

                    //check for variation , if found type = configurable
                    $individualItem['type'] = 'configurable';
                    if (isset($item['Variations']['Item'][0])) {
                        $individualItem['variation'] = $item['Variations']['Item'];
                    } else {
                        $individualItem['variation'][] = $item['Variations']['Item'];
                    }
                    if (isset($item['ImageSets']['ImageSet'])) {
                        $individualItem['LargeImage'] = $this->getImageUrl($item['ImageSets']['ImageSet']);
                    }
                    $individualItem['SmallImage'] = $item['SmallImage']['URL'];
                    $individualItem['MediumImage'] = $item['MediumImage']['URL'];
                    $individualItem['SwatchImage'] = $item['ImageSets']['ImageSet'][0]['SwatchImage']['URL'];
                    $individualItem['availability'] = isset($item['OfferSummary']['TotalNew']) &&
                    ($item['OfferSummary']['TotalNew'] > 0) ? $item['OfferSummary']['TotalNew'] : '';
                    // ItemDimension including Height Weight Length
                    $individualItem['weight'] = isset($item['ItemAttributes']['ItemDimensions']['Weight']) ? $item['ItemAttributes']['ItemDimensions']['Weight'] : '';
                    $individualItem['brand'] = isset($item['ItemAttributes']['Brand']) ? $item['ItemAttributes']['Brand'] : '';
                    $individualItem['dimension'] = isset($item['ItemAttributes']['ItemDimensions']) ? $item['ItemAttributes']['ItemDimensions'] : '';
                    if (isset($individualItem['LargeImage'])) {
                        foreach ($individualItem['LargeImage'] as $largeImage => $key) {
                            try {
                                $image_url = $key;
                                $importDir = Mage::getBaseDir('media') . DS . 'catalog/product/amazonimporter' . DS;
                                $file = new Varien_Io_File();
                                $file->mkdir($importDir, 0777, true);
                                $pathL = $importDir . $item['ASIN'] . '_L' . $largeImage . '.jpg';
                                if (!$file->write($pathL, $file->read(trim($image_url)))) {
                                    die("cannot write local file :/");
                                }
                                $file->close();
                            } catch (\Exception $e) {
                                $e->getMessage();
                            }
                        }
                    }
                    if (isset($individualItem['SmallImage'])) {

                        try {
                            $image_url = $individualItem['SmallImage'];
                            $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                            $file = new Varien_Io_File();
                            $file->mkdir($importDir, 0777, true);
                            $pathS = $importDir . $item['ASIN'] . '_S' . '.jpg';
                            if (!$file->write($pathS, $file->read(trim($image_url)))) {
                                die("cannot write local file :/");
                            }
                            $file->close();
                        } catch (\Exception $e) {
                            $e->getMessage();
                        }
                    }
                    if (isset($individualItem['MediumImage'])) {
                        try {
                            $image_url = $individualItem['MediumImage'];
                            $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                            $file = new Varien_Io_File();
                            $file->mkdir($importDir, 0777, true);
                            $pathM = $importDir . $item['ASIN'] . '_M' . '.jpg';
                            if (!$file->write($pathM, $file->read(trim($image_url)))) {
                                die("cannot write local file :/");
                            }
                            $file->close();
                        } catch (\Exception $e) {
                            $e->getMessage();
                        }
                    }
                    if (isset($individualItem['SwatchImage'])) {
                        try {
                            $image_url = $individualItem['SwatchImage'];
                            $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                            $file = new Varien_Io_File();
                            $file->mkdir($importDir, 0777, true);
                            $pathSa = $importDir . $item['ASIN'] . '_sa' . '.jpg';
                            if (!$file->write($pathM, $file->read(trim($image_url)))) {
                            }
                            $file->close();

                        } catch (\Exception $e) {
                            $e->getMessage();
                        }
                    }
                    $individualItem['Imagepath_large'] = $pathL;
                    $individualItem['Imagepath_Small'] = $pathS;
                    $individualItem['Imagepath_Medium'] = $pathM;
                    $individualItem['Imagepath_SwatchImage'] = $pathSa;

                    $individualItem['categories'] = $this->createCategory($item['BrowseNodes']);
                } catch (\Exception $e) {
                    $e->getMessage();
                }
            } else {

                try {
                    $individualItem = [];
                    $individualItem['name'] = $item['ItemAttributes']['Title'];
                    $individualItem['asin'] = $item['ASIN'];
                    $individualItem['amazon_product_url'] = $item['DetailPageURL'];
                    $individualItem['reviews'] = $item['CustomerReviews']['IFrameURL'];
                    if (!isset($item['BrowseNodes']['BrowseNode']['BrowseNodeId'])) {
                        end($item['BrowseNodes']['BrowseNode']);
                        $key = key($item['BrowseNodes']['BrowseNode']);
                        $individualItem['nodeId'] = isset($item['BrowseNodes']['BrowseNode'][$key]['BrowseNodeId']) ?
                            $item['BrowseNodes']['BrowseNode'][$key]['BrowseNodeId'] : '';
                        $individualItem['nodeName'] = isset($item['BrowseNodes']['BrowseNode'][$key]['Name']) ?
                            $item['BrowseNodes']['BrowseNode'][$key]['Name'] : '';
                    } else {
                        $individualItem['nodeId'] = isset($item['BrowseNodes']['BrowseNode']['BrowseNodeId']) ?
                            $item['BrowseNodes']['BrowseNode']['BrowseNodeId'] : '';
                        $individualItem['nodeName'] = isset($item['BrowseNodes']['BrowseNode']['Name']) ?
                            $item['BrowseNodes']['BrowseNode']['Name'] : '';
                    }
                    $fullDescription = $this->prepareDescription($item['ItemAttributes']);
                    $individualItem['description'] = !empty($fullDescription['desc']) ? $fullDescription['desc'] : '';
                    $individualItem['short_description'] = !empty($fullDescription['short_desc']) ?
                        $fullDescription['short_desc'] : '';

                    if (isset($item['Offers']['Offer']['OfferListing']['Price']['Amount']) &&
                        is_numeric($item['Offers']['Offer']['OfferListing']['Price']['Amount'])
                    ) {
                        $price = number_format((float)($item['Offers']['Offer']['OfferListing']['Price']['Amount'] / 100), 2, '.', '');
                    } elseif (isset($item['OfferSummary']['LowestNewPrice']['Amount']) &&
                        is_numeric($item['OfferSummary']['LowestNewPrice']['Amount'])
                    ) {
                        $price = number_format((float)($item['OfferSummary']['LowestNewPrice']['Amount'] / 100), 2, '
                        .', '');
                    } elseif (isset($item['ItemAttributes']['ListPrice']['Amount']) && is_numeric($item['ItemAttributes']['ListPrice']['Amount'])) {
                        $price = number_format((float)($item['ItemAttributes']['ListPrice']['Amount'] / 100), 2, '.', '');
                    } else {
                        $price = 0.00;
                    }
                    $individualItem['price'] = (isset($item['OfferSummary']['TotalNew']) && ($item['OfferSummary']['TotalNew'] > 0) && ($price != 0.00)) ? $price : 'N/A';
                    $individualItem['type'] = 'simple';
                    $item['ImageSets']['ImageSet'] = isset($item['ImageSets']['ImageSet']) ? $item['ImageSets']['ImageSet'] : [];

                    $individualItem['LargeImage'] = $this->getImageUrl($item['ImageSets']['ImageSet']);
                   $individualItem['SmallImage'] = $item['SmallImage']['URL'];
                   $individualItem['MediumImage'] = $item['MediumImage']['URL'];
                  $individualItem['SwatchImage'] = $item['ImageSets']['ImageSet'][0]['SwatchImage']['URL'];
                    $individualItem['availability'] = isset($item['OfferSummary']['TotalNew']) &&
                    ($item['OfferSummary']['TotalNew'] > 0) ? $item['OfferSummary']['TotalNew'] : '';

                    // ItemDimension including Height Weight Length
                    $individualItem['dimension'] = isset($item['ItemAttributes']['ItemDimensions']) ? $item['ItemAttributes']['ItemDimensions'] : '';
                    $individualItem['weight'] = isset($item['ItemAttributes']['ItemDimensions']['Weight']) ? $item['ItemAttributes']['ItemDimensions']['Weight'] : '';
                    $individualItem['brand'] = isset($item['ItemAttributes']['Brand']) ? $item['ItemAttributes']['Brand'] : '';
                    if (isset($individualItem['LargeImage'])) {
                        foreach ($individualItem['LargeImage'] as $largeImage => $key) {
                            try {
                                $image_url = $key;
                                $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                                $file = new Varien_Io_File();
                                $file->mkdir($importDir, 0777, true);
                                $pathL = $importDir . $item['ASIN'] . '_L' . $largeImage . '.jpg';
                                if (!$file->write($pathL, $file->read(trim($image_url)))) {
                                    die("cannot write local file :/");
                                }
                                $file->close();
                            } catch (\Exception $e) {
                                $e->getMessage();
                            }
                        }
                    }
                        if (isset($individualItem['SmallImage'])) {
                            try {
                                $image_url = $individualItem['SmallImage'];
                                $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                                $file = new Varien_Io_File();
                                $file->mkdir($importDir, 0777, true);
                                $pathS = $importDir . $item['ASIN'] . '_S' . '.jpg';
                                if (!$file->write($pathS, $file->read(trim($image_url)))) {
                                }
                                $file->close();
                            } catch (\Exception $e) {
                                 $e->getMessage();
                            }
                        }
                        if (isset($individualItem['MediumImage'])) {
                            try {
                                $image_url = $individualItem['MediumImage'];
                                $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                                $file = new Varien_Io_File();
                                $file->mkdir($importDir, 0777, true);
                                $pathM = $importDir . $item['ASIN'] . '_M' . '.jpg';
                                if (!$file->write($pathM, $file->read(trim($image_url)))) {
                                    die("cannot write local file :/");
                                }
                                $file->close();

                            } catch (\Exception $e) {
                                $e->getMessage();

                            }
                        }
                        if (isset($individualItem['SwatchImage'])) {
                            try {
                                $image_url = $individualItem['SwatchImage'];
                                $importDir = Mage::getBaseDir('media') . DS . 'amazonimporter' . DS;
                                $file = new Varien_Io_File();
                                $file->mkdir($importDir, 0777, true);
                                $pathSa = $importDir . $item['ASIN'] . '_sa' . '.jpg';
                                if (!$file->write($pathM, $file->read(trim($image_url)))) {
                                    die("cannot write local file :/");
                                }
                                $file->close();
                            } catch (\Exception $e) {
                                $e->getMessage();

                            }
                        }
                        $individualItem['Imagepath_large'] = $pathL;
                        $individualItem['Imagepath_Small'] = $pathS;
                        $individualItem['Imagepath_Medium'] = $pathM;
                        $individualItem['Imagepath_SwatchImage'] = $pathSa;
                        $individualItem['categories'] = $this->createCategory($item['BrowseNodes']);

                } catch (\Exception $e) {
                    $e->getMessage();
                }
            }
            return $individualItem;
        }
    }

    public function prepareDescription($dataArray)
    {
        $description = [];
        $description['desc'] = '';
        $description['short_desc'] = '<p><strong><span>Feature : </span></strong></p>';
        foreach ($dataArray as $attr => $value) {
            if (!is_array($value)) {
                $description['desc'] = $description['desc'] . "<p><strong><span>" . $attr . ": </span></strong>";
                $description['desc'] = $description['desc'] . "<span>" . $value . "</span></p>";
            }
            if ($attr == 'Feature') {
                $description['short_desc'] = $description['short_desc'] . "<ul>";
                if (is_array($value)) {
                    foreach ($value as $v) {
                        $description['short_desc'] = $description['short_desc'] . "<li>" . $v . "</li>";
                    }
                } elseif (is_string($value)) {
                    $description['short_desc'] = $description['short_desc'] . "<li>" . $value . "</li>";
                }
                $description['short_desc'] = $description['short_desc'] . "</ul>";
            }
        }
        return $description;
    }

    /**
     * @param array $imgArr
     * @return array
     */
    public function getImageUrl($imgArr = [])
    {
        $newImgArr = [];
        if (!empty($imgArr)) {
            if (isset($imgArr['LargeImage'])) {
                $imgArr = [0 => $imgArr];
            }
            foreach ($imgArr as $key => $path) {
                if (isset($path['LargeImage']['URL'])) { // Getting Large Image URL
                    $newImgArr[] = $path['LargeImage']['URL'];
                }
            }
        }
        unset($imgArr);
        return $newImgArr;
    }

    public function createCategory($name, $rootNodeId, $store)
    {
        $rootCat = Mage::getModel('catalog/category');
        $parentId = $rootCat->load($rootNodeId);
        $url = strtolower(str_replace(' ', '-', $name));
        $category = Mage::getModel('catalog/category');
        $category->setName($this->clearEntities($name))
            ->setIsActive(true)
            ->setUrlKey($url)
            ->setData('description', 'description')
            ->setParentId($parentId->getId())
            ->setStoreId($store->getId())
            ->setPath($parentId->getPath())
            ->save();
        return $category->getEntityId();
    }

    public function getProductbyBarcode($aObj, $itemId)
    {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestData = [
            "AssociateTag" => $this->associateTag,
            "AWSAccessKeyId" => $this->keyId,
            "Service" => $this->service,
            "Operation" => "ItemLookup",
            "ItemId" => $itemId,
            "IdType" => "ASIN",
            "Timestamp" => $timestamp,
            "ResponseGroup" => $this->responseGroup,
        ];
        ksort($requestData);
        $urlContent = array();
        foreach ($requestData as $key => $value) {
            array_push($urlContent, rawurlencode($key) . "=" . rawurlencode($value));
        }
        $urlPre = join("&", $urlContent);
        $urlPost = "GET\n" . $this->endPoint . "\n" . $this->urlAppend . "\n" . $urlPre;
        $signature = base64_encode(hash_hmac("sha256", $urlPost, $this->secretKey, true));
        $this->finalUrl = 'http://' . $this->endPoint . $this->urlAppend . '?' . $urlPre . '&Signature=' . rawurlencode($signature);
        return $this;
    }

}