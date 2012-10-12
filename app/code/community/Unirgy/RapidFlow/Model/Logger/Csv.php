<?php

class Unirgy_RapidFlow_Model_Logger_Csv extends Unirgy_RapidFlow_Model_Logger_Abstract
{
    protected $_defaultIoModel = 'urapidflow/io_csv';

    public function start($mode)
    {
        $this->getIo()->open($this->getProfile()->getLogFilename(), $mode);
        $level = $this->getProfile()->getData('options/log/min_level');
        $this->setLevelSuccess($level=='SUCCESS');
        $this->setLevelWarning($level=='SUCCESS' || $level=='WARNING');
        $this->setLevelError($level=='SUCCESS' || $level=='WARNING' || $level=='ERROR');
        return $this;
    }

    public function stop()
    {
      $this->getIo()->close();
    }

    public function log($rowType, $data)
    {
        $data = (array)$data;
        if (!is_null($rowType)) {
            array_unshift($data, $rowType);
        }
        $this->getIo()->write($data);
        return $this;
    }

    public function success($message='')
    {
        if ($this->getLevelSuccess()) {
            $this->log('SUCCESS', array($this->getLine(), $this->getColumn(), $message));
        }
        return $this;
    }

    public function warning($message)
    {
        if ($this->getLevelWarning()) {
            $this->log('WARNING', array($this->getLine(), $this->getColumn(), $message));
        }
        return $this;
    }

    public function error($message)
    {
        if ($this->getLevelError()) {
            $this->log('ERROR', array($this->getLine(), $this->getColumn(), $message));
        }
        return $this;
    }

    public function setIo($io)
    {
        parent::setIo($io);
        $this->getIo()->setBaseDir($this->getProfile()->getLogBaseDir());
        return $this;
    }
}