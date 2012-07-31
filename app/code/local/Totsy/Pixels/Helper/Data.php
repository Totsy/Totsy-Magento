<?php
class Totsy_Pixels_Helper_Data
{
	//$_tagList is a comme delimited string that can be made of "ages" and/or "departments"
	public function getProductTags($_product, $_tagList){ 
		$_tags = Array();
    	if($_product->getAttributeText($_tagList)) {    
    	    if(!is_array($_product->getAttributeText($_tagList))) {
    	        $_tags[] = $_product->getAttributeText($_tagList);
    	    } else { 
    	        foreach($_product->getAttributeText($_tagList) as $tag){
    	            $_tags[] = $tag;
    	        }        
    	    }
    	}
    	return $_tags;
    }
}