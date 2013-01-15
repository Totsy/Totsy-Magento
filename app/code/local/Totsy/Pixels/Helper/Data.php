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
    
    //for strippping data presented in Sociable Labs posts on Facebook
    public function stripHTMLContent($_htmlString) {
    	$_dom = new DOMDocument();
    	
    	if($_htmlString) {
            $_dom->loadHTML(htmlentities($_htmlString));
            $_dom->preserveWhiteSpace = false;
            
            //more elements/tags may be stripped out using this type
            $_elements = $_dom->getElementsByTagName('ul');
            $_tags = array();
            
            foreach($_elements as $_tag) {
                $_tags[] = $_tag;
            }
            
            foreach($_tags as $_tag) {
                $_tag->parentNode->removeChild($_tag);
            }
            
            return $_dom->saveHTML();
        } else {
        	return "Please enter a value";
        }
    }
}
