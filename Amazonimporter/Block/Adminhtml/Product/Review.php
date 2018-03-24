<?php


class Ced_Amazonimporter_Block_Adminhtml_Product_Review extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $id = $row->getced_asin();
        $iframe = $row->getced_iframe_url();

        $html = '';
        if (!Mage::registry('amazon_importer')) {
            Mage::register('amazon_importer', true);
            $html = '<script>
                               
                                function bindClick(){
                                    $elements = document.getElementsByClassName("amazon_iframe");
                                   
                                    for($index in $elements){
                                        if(typeof $elements[$index]!="function" && typeof $elements[$index]!="undefined"){
                                            $elements[$index].onclick=function(){
                                       
                                                e=this;
                                                var id = e.readAttribute("data-id");
                                                $("amazon_popup"+id).setStyle({display:"block"});
                                                oPopup = new Window({
                                                    id:"browser_window",
                                                    className: "magento",
                                                    windowClassName: "popup-window",
                                                    title: "Item Errors",
                                                    width: 750,
                                                    height: 350,
                                                    minimizable: false,
                                                    maximizable: false,
                                                    showEffectOptions: {
                                                    duration: 0.4
                                                    },
                                                    hideEffectOptions:{
                                                    duration: 0.4
                                                    },
                                                    destroyOnClose: true
                                                });
                                                oPopup.setZIndex(100);
                                                oPopup.showCenter(true);                    
                                                oPopup.setContent("amazon_popup"+id,false,false);
                                                $("browser_window_close").onclick = function(){
                                                    $("amazon_popup"+id).setStyle({display:"none"});
                                                    Windows.close("browser_window");
                                                }
                                            }
                                        }
                                    }
                                }
                                
                        </script>';


        }

        $html .= "<div id='amazon_popup" . $id . "' style='padding:20px;display:none'>
            <iframe src='$iframe'  height='330' width='700'>
            </iframe>
            </div>
        ";

        $html .= '<a title="" class="amazon_iframe" data-id="' . $id . '" href="#"><span class="grid-severity-notice"><span>View Reviews</span></span></a>';


        return $html . '<script>bindClick();</script>';

    }
}