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

class Unirgy_RapidFlow_Model_Source extends Unirgy_RapidFlow_Model_Source_Abstract
{
    public function toOptionHash($selector=false)
    {
        $hlp = Mage::helper('urapidflow');

        $options = array();

        switch ($this->getPath()) {

        case 'yesno':
            $options = array(
                1 => $this->__('Yes'),
                0 => $this->__('No'),
            );
            break;

        case 'ftp_file_mode':
            $options = array(
                FTP_ASCII => $this->__('Ascii'),
                FTP_BINARY => $this->__('Binary'),
            );
            break;

        case 'profile_status':
            $options = array(
                'enabled' => $this->__('Enabled'),
                'disabled' => $this->__('Disabled'),
            );
            break;


        case 'profile_type':
            $options = array(
                'import' => $this->__('Import'),
                'export' => $this->__('Export'),
            );
            break;
            
        case 'urapidflow/import_options/date_processor':
            $options = array(
                'strtotime' => $this->__('strtotime'),
                'zend_date' => $this->__('Zend_Date'),
            );
            if (version_compare(phpversion(), '5.3.0', '>=')) {
            	$options['date_parse_from_format'] = $this->__('date_parse_from_format (PHP >= 5.3.0)');
            }
            break;

        case 'run_status':
            $options = array(
                'idle' => $this->__('Idle'),
                'pending' => $this->__('Pending'),
                'running' => $this->__('Running'),
                'paused' => $this->__('Paused'),
                'stopped' => $this->__('Stopped'),
                'finished' => $this->__('Finished'),
            );
            break;

        case 'invoke_status':
            $options = array(
                'none' => $this->__('None'),
                //'foreground' => $this->__('Foreground'),
                'ondemand' => $this->__('On Demand'),
                //'scheduled' => $this->__('Scheduled'),
            );
            break;

        case 'data_type':
            $dataTypes = Mage::getSingleton('urapidflow/config')->getDataTypes();
            foreach ($dataTypes as $k=>$c) {
                $options[$k] = $this->__((string)$c->title);
            }
            break;

        case 'row_type':
            $rowTypes = Mage::getSingleton('urapidflow/config')->getRowTypes($this->getDataType());
            foreach ($rowTypes as $k=>$c) {
                $label = (string)$c->title;
                if ($this->getStripFromLabel()) {
                    $label = preg_replace($this->getStripFromLabel(), '', $label);
                }
                $options[$k] = $k.': '.$this->__($label);
            }
            break;

        case 'stores':
            $options = $this->getStores();
            break;

        case 'schedule_hours':
            for ($i=0; $i<=23; $i++) {
                $options[$i] = $i;
            }
            break;

        case 'schedule_week_days':
            for ($i=0; $i<=6; $i++) {
                $options[$i] = $i;
            }
            break;

        case 'schedule_month_days':
            for ($i=1; $i<=31; $i++) {
                $options[$i] = $i;
            }
            break;

        case 'schedule_months':
            for ($i=1; $i<=12; $i++) {
                $options[$i] = $i;
            }
            break;

        case 'attribute_sets':
            $etId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getEntityTypeId();
            $collection = Mage::getModel('eav/entity_attribute_set')->getCollection()
                ->addFieldToFilter('entity_type_id', $etId);
            $options = array();
            foreach ($collection as $s) {
                $options[$s->getId()] = $s->getAttributeSetName();
            }
            break;

        case 'entity_types':
            $options = array();
            foreach (array('catalog_product', 'catalog_category') as $et) {
                $e = Mage::getSingleton('eav/config')->getEntityType($et);
                $options[$e->getId()] = $e->getEntityTypeCode();
            }
            break;

        case 'encoding_illegal_char':
            $options = array(
                '' => $this->__('Add warning and pass through as original'),
                'TRANSLIT' => $this->__('Attempt to transliterate'),
                'IGNORE' => $this->__('Remove illegal characters'),
            );
            break;

        case 'import_actions':
            $options = array(
                'any' => $this->__('Create or Update as neccessary'),
                'create' => $this->__('Only create new records'),
                'update' => $this->__('Only update existing records'),
            );
            break;

        case 'import_reindex_type':
            $options = array(
                'full' => $this->__('Full (automatically AFTER import)'),
                'realtime' => $this->__('Realtime (affected records DURING import)'),
                'manual' => $this->__('Manual (flag affected indexes)'),
            );
            break;
        case 'import_reindex_type_nort':
            $options = array(
                'full' => $this->__('Full (automatically AFTER import)'),
                'manual' => $this->__('Manual (flag affected indexes)'),
            );
            break;

        case 'import_image_remote_subfolder_level':
            $options = array(
                '' => $this->__('No subfolders (image.jpg)'),
                '1' => $this->__('1 subfolder (a/image.jpg)'),
                '2' => $this->__('2 subfolders (a/b/image.jpg)'),
                '3' => $this->__('3 subfolders (a/b/c/image.jpg)'),
            );
            break;

        case 'import_image_missing_file':
            $options = array(
                'warning_save' => $this->__('WARNING and update image field'),
                'warning_skip' => $this->__('WARNING and skip image field update'),
                'warning_empty' => $this->__('WARNING and set image field as empty'),
                'error' => $this->__('ERROR and skip the whole record update'),
            );
            break;

        case 'store_value_same_as_default':
            $options = array(
                'default' => $this->__('Use default values'),
                'duplicate' => $this->__('Create the values for store level'),
            );
            break;

        case 'category_display_mode':
            $options = array(
                Mage_Catalog_Model_Category::DM_PRODUCT => Mage::helper('catalog')->__('Products only'),
                Mage_Catalog_Model_Category::DM_PAGE => Mage::helper('catalog')->__('Static block only'),
                Mage_Catalog_Model_Category::DM_MIXED => Mage::helper('catalog')->__('Static block and products'),
            );
            break;

        case 'log_level':
            $options = array(
                'SUCCESS' => $this->__('Successful Updates'),
                'WARNING' => $this->__('Warnings'),
                'ERROR' => $this->__('Errors'),
            );
            break;

        case 'remote_type':
            $options = array(
                '' => $this->__('* None'),
                'ftp' => $this->__('FTP'),
            );
            /*
            if (function_exists('ftp_ssl_connect')) {
                $options['ftps'] = $this->__('FTPS');
            }
            if (function_exists('ssh2_sftp')) {
                $options['sftp'] = $this->__('SFTP');
            }
            $options['http'] = $this->__('HTTP');
            */
            break;

        case 'compress_type':
            $options = array(
                '' => $this->__('* None'),
                'gz' => $this->__('gz'),
                'bz2' => $this->__('bz2'),
                'zip' => $this->__('zip'),
            );
            break;

        case 'encoding':
            $options = array('' => $this->__('* No enconding conversion (UTF-8)'));
            /*
            if (function_exists('mb_detect_encoding')) {
                $options['auto'] = $this->__('* Automatic conversion (slowest)');
            }
                $encodings = mb_list_encodings();
                natsort($encodings);
                foreach ($encodings as $e) {
                    if ($e=='pass' || $e=='auto') {
                        continue;
                    }
                    $options[$e] = $e;
                }
            */
            $options += array(
                'ISO (Unix/Linux)' => array(
                    'iso-8859-1' => 'iso-8859-1',
                    'iso-8859-2' => 'iso-8859-2',
                    'iso-8859-3' => 'iso-8859-3',
                    'iso-8859-4' => 'iso-8859-4',
                    'iso-8859-5' => 'iso-8859-5',
                    'iso-8859-6' => 'iso-8859-6',
                    'iso-8859-7' => 'iso-8859-7',
                    'iso-8859-8' => 'iso-8859-8',
                    'iso-8859-9' => 'iso-8859-9',
                    'iso-8859-10' => 'iso-8859-10',
                    'iso-8859-11' => 'iso-8859-11',
                    'iso-8859-12' => 'iso-8859-12',
                    'iso-8859-13' => 'iso-8859-13',
                    'iso-8859-14' => 'iso-8859-14',
                    'iso-8859-15' => 'iso-8859-15',
                    'iso-8859-16' => 'iso-8859-16',
                 ),
                'WINDOWS' => array(
                    'windows-1250' => 'windows-1250 - Central Europe',
                    'windows-1251' => 'windows-1251 - Cyrillic',
                    'windows-1252' => 'windows-1252 - Latin I',
                    'windows-1253' => 'windows-1253 - Greek',
                    'windows-1254' => 'windows-1254 - Turkish',
                    'windows-1255' => 'windows-1255 - Hebrew',
                    'windows-1256' => 'windows-1256 - Arabic',
                    'windows-1257' => 'windows-1257 - Baltic',
                    'windows-1258' => 'windows-1258 - Viet Nam',
                ),
                'DOS' => array(
                    'cp437' => 'cp437 - Latin US',
                    'cp737' => 'cp737 - Greek',
                    'cp775' => 'cp775 - BaltRim',
                    'cp850' => 'cp850 - Latin1',
                    'cp852' => 'cp852 - Latin2',
                    'cp855' => 'cp855 - Cyrylic',
                    'cp857' => 'cp857 - Turkish',
                    'cp860' => 'cp860 - Portuguese',
                    'cp861' => 'cp861 - Iceland',
                    'cp862' => 'cp862 - Hebrew',
                    'cp863' => 'cp863 - Canada',
                    'cp864' => 'cp864 - Arabic',
                    'cp865' => 'cp865 - Nordic',
                    'cp866' => 'cp866 - Cyrylic Russian (used in IE "Cyrillic (DOS)" )',
                    'cp869' => 'cp869 - Greek2',
                 ),
                 'MAC (Apple)' => array(
                    'x-mac-cyrillic' => 'x-mac-cyrillic',
                    'x-mac-greek' => 'x-mac-greek',
                    'x-mac-icelandic' => 'x-mac-icelandic',
                    'x-mac-ce' => 'x-mac-ce',
                    'x-mac-roman' => 'x-mac-roman',
                ),
                'MISCELLANEOUS' => array(
                    'gsm0338' => 'gsm0338 (ETSI GSM 03.38)',
                    'cp037' => 'cp037',
                    'cp424' => 'cp424',
                    'cp500' => 'cp500',
                    'cp856' => 'cp856',
                    'cp875' => 'cp875',
                    'cp1006' => 'cp1006',
                    'cp1026' => 'cp1026',
                    'koi8-r' => 'koi8-r (Cyrillic)',
                    'koi8-u' => 'koi8-u (Cyrillic Ukrainian)',
                    'nextstep' => 'nextstep',
                    'us-ascii' => 'us-ascii',
                    'us-ascii-quotes' => 'us-ascii-quotes',
                ),
            );
            break;

        case 'save_attributes_method':
        case 'urapidflow/finetune/save_attributes_method':
            $options = array(
                '' => $this->__('Plain'),
                'PDOStatement' => $this->__('PDOStatement'),
            );
            break;

        default:
            Mage::throwException($this->__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = array(''=>$this->__('* Please select')) + $options;
        }

        return $options;
    }

    public function toOptionArray($selector=false)
    {
        switch ($this->getPath()) {

        }
        return parent::toOptionArray($selector);
    }

    protected $_withDefaultWebsite = true;
    public function withDefaultWebsite($flag)
    {
    	$oldFlag = $this->_withDefaultWebsite;
    	$this->_withDefaultWebsite = (bool)$flag;
    	return $oldFlag;
    }
    
    public function getStores()
    {
        $options = array();
        foreach (Mage::app()->getWebsites((bool)$this->_withDefaultWebsite) as $website) {
            foreach ($website->getStores() as $sId=>$store) {
                $options[$website->getName()][$sId] = '['.$store->getCode().'] '.$store->getName();
            }
        }
        return $options;
    }
}
