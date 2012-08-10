<?php

class Totsy_Dataflow_Model_Batch_Import extends Mage_Dataflow_Model_Batch_Import
{
    public function getBatchDataSerialized()
    {
        $data = $this->_data['batch_data'];
        return $data;
    }
}
