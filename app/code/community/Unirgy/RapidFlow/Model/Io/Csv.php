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

class Unirgy_RapidFlow_Model_Io_Csv extends Unirgy_RapidFlow_Model_Io_File
{
    public function open($filename, $mode)
    {
        ini_set('auto_detect_line_endings', 1);
        parent::open($filename, $mode);
        return $this;
    }

    public function read()
    {
        if (!$this->_fp) {
            return false;
        }
        // for PHP 5.3.0 only
        #$result = fgetcsv($this->_fp, 0, $this->getDelimiter(), $this->getEnclosure(), $this->getEscape());
        $result = fgetcsv($this->_fp, 0, $this->getDelimiter(), $this->getEnclosure());
        return $result;
    }

    public function write($data)
    {
        if (!$this->_fp) {
            throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('Resource is closed, unable to write to the file'));
        }
        #$result = $this->putcsv($this->_fp, $data, $this->getDelimiter(), $this->getEnclosure(), $this->getEscape());
        $result = fputcsv($this->_fp, $data, $this->getDelimiter(), $this->getEnclosure());
        if (!$result) {
            throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('Unable to write to the file'));
        }
        return $this;
    }

    public function getDelimiter()
    {
        $delimiter = $this->_getData('delimiter');
        if (!$delimiter) {
            $this->setData('delimiter', ',');
        } elseif ($delimiter==='\\t') {
            $this->setData('delimiter', "\t");
        }
        return $this->_getData('delimiter');
    }

    public function getEnclosure()
    {
        if (!$this->_getData('enclosure')) {
            $this->setData('enclosure', '"');
        }
        return $this->_getData('enclosure');
    }

    public function getEscape()
    {
        if (!$this->_getData('escape')) {
            $this->setData('escape', '\\');
        }
        return $this->_getData('escape');
    }

    /**
    * Implements custom escape char
    *
    * @param mixed $handle
    * @param mixed $fields
    * @param mixed $delimiter
    * @param mixed $enclosure
    * @param mixed $escape
    * @return int
    */
    public function putcsv(&$handle, array $fields, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $i = 0;
        $csvline = '';
        $fieldCnt = count($fields);
        $encIsQuote = in_array($enclosure, array('"',"'"));
        reset($fields);

        foreach ($fields as $field) {
            /* enclose a field that contains a delimiter, an enclosure character, or a newline */
            if (is_string($field) && (
                strpos($field, $delimiter)!==false ||
                strpos($field, $enclosure)!==false ||
                strpos($field, $escape)!==false ||
                strpos($field, "\n")!==false ||
                strpos($field, "\r")!==false ||
                strpos($field, "\t")!==false ||
                strpos($field, ' ')!==false
            )) {

                $fieldLen = strlen($field);
                $escaped = 0;

                $csvline .= $enclosure;
                for ($ch = 0; $ch < $fieldLen; $ch++)    {
                    if ($field[$ch] == $escape && $field[$ch+1] == $enclosure && $encIsQuote) {
                        continue;
                    } elseif ($field[$ch] == $escape) {
                        $escaped = 1;
                    } elseif (!$escaped && $field[$ch] == $enclosure) {
                        $csvline .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $csvline .= $field[$ch];
                }
                $csvline .= $enclosure;
            } else {
                $csvline .= $field;
            }

            if ($i++ != $fieldCnt) {
                $csvline .= $delimiter;
            }
        }

        $csvline .= "\n";

        return fwrite($handle, $csvline);
    }
}