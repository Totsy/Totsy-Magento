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

class Unirgy_RapidFlow_Model_Io_File extends Unirgy_RapidFlow_Model_Io_Abstract
{
    protected $_openMode;
    protected $_filename;
    protected $_fp;

    public function open($filename, $mode)
    {
        $filename = $this->getFilepath($filename);
        if ($this->_fp) {
            if ($this->_filename==$filename && $this->_openMode==$mode) {
                return;
            } else {
                $this->close();
            }
        }

        $this->_fp = @fopen($filename, $mode);
        if ($this->_fp===false) {
            $e = error_get_last();
            Mage::throwException(Mage::helper('urapidflow')->__("Unable to open the file %s with mode '%s' (%s)", $filename, $mode, $e['message']));
        }

        $this->_openMode = $mode;
        $this->_filename = $filename;

        return $this;
    }

    public function isOpen()
    {
        return (bool)$this->_fp;
    }

    /**
    * Close file and reset file pointer
    */
    public function close()
    {
        if (!$this->_fp) {
            return;
        }
        @fclose($this->_fp);

        $this->_fp = null;
        $this->_filename = null;
    }

    public function seek($offset, $whence=SEEK_SET)
    {
        @fseek($this->_fp, $offset, $whence);
        return $this;
    }

    public function tell()
    {
        return ftell($this->_fp);
    }

    public function read()
    {
        $length = $this->getReadLength();
        if ($length) {
            $data = fread($this->_fp, $length);
        } else {
            $data = fread($this->_fp);
        }
        return $data;
    }

    public function write($data)
    {
        if ($this->getWriteLength()) {
            fwrite($this->_fp, $data, $this->getWriteLength());
        } else {
            fwrite($this->_fp, $data);
        }
        return $this;
    }

    public function getFilepath($filename)
    {
        if (!$this->getBaseDir()) {
            $this->setBaseDir(Mage::getConfig()->getVarDir('urapidflow'));
        }
        $dir = $this->getBaseDir();
        if ($dir) {
            Mage::app()->getConfig()->createDirIfNotExists($dir);
        }
        $filepath = rtrim($dir, '/').'/'.ltrim($filename, '/');
        return $filepath;
    }

    public function reset()
    {
        $filename = $this->_filename;
        $openMode = $this->_openMode;
        $this->close();
        @unlink($filename);
        $this->open($filename, $openMode);
        return $this;
    }

    /**
    * Close file on object destruct
    */
    public function __destruct()
    {
        $this->close();
    }
}