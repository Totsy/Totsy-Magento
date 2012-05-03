<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_Speedtax_Model_Adminhtml_Usregions
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $regionCollection = Mage::getModel( 'directory/region' )->getCollection();
            $regionCollection->getSelect()->where( 'country_id = ?', 'US' );
            $regionOptions = array();
            foreach( $regionCollection as $region ) {
                $regionOptions[] = array( 'label' => $region->getDefaultName(), 'value' => $region->getId() ); 
            }
               $this->_options = $regionOptions;
        }
        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=>''));
        }

        return $options;
    }
}
