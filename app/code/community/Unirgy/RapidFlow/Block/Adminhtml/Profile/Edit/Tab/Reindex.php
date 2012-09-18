<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Reindex extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('urapidflow/reindex.phtml');
    }
    
    public function getReindexColumnsFields()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');
        $profile = Mage::registry('profile_data');
        $newIndexer = $hlp->hasMageFeature('indexer_1.4');
        $reindexFields = array();
        
        if ($newIndexer) {

            $indexer = Mage::getSingleton('index/indexer');
            foreach ($indexer->getProcessesCollection() as $process) {
                $code = $process->getIndexerCode();
                $reindexFields[] = array(
                    'label'     => Mage::helper('core')->__($process->getIndexer()->getName()),
                    'value'     => $code,
                );
            }

        } else {

            foreach ($profile->getReindexTypeNames() as $code=>$label) {
                $reindexFields[] = array(
                    'label'     => Mage::helper('core')->__($label),
                    'value'     => $code,
                );
            }
        }
        
        $reindexFields[] = array(
            'label'     => Mage::helper('catalogrule')->__('Catalog Rules'),
            'value'     => 'catalog_rules',
        );
        
        return $reindexFields;
    }
    
    public function getRefreshColumnsFields()
    {
        $hlp = Mage::helper('urapidflow');
        $source = Mage::getSingleton('urapidflow/source');
        $profile = Mage::registry('profile_data');
        $newIndexer = $hlp->hasMageFeature('indexer_1.4');
        $cacheFields = array();
        
        if ($newIndexer) {

            $cacheTypes = Mage::app()->getCacheInstance()->getTypes();
            $cacheTypes['clean_media'] = new Varien_Object(array(
                'id' => 'clean_media',
                'cache_type' => Mage::helper('adminhtml')->__('Flush JavaScript/CSS Cache'),
            ));
            foreach ($cacheTypes as $type) {
                $code = $type->getId();
                $cacheFields[] = array(
                    'label'     => $type->getCacheType(),
                    'value'     => $code,
                    'note'      => $type->getDescription(),
                );
            }
        } else {
            foreach (Mage::helper('core')->getCacheTypes() as $code=>$label) {
                $cacheFields[] = array(
                    'label'     => Mage::helper('core')->__($label),
                    'value'     => $code,
                );
            }
        }
        return $cacheFields;
    }
    
    public function getReindexColumns()
    {
        $profile = Mage::registry('profile_data');
        return array_flip((array)$profile->getData('options/reindex'));
    }
    
    public function getRefreshColumns()
    {
        $profile = Mage::registry('profile_data');
        return array_flip((array)$profile->getData('options/refresh'));
    }
}
