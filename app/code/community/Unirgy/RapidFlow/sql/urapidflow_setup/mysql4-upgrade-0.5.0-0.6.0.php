<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('urapidflow/profile'), 'base_dir', 'text not null after data_type');

$this->endSetup();