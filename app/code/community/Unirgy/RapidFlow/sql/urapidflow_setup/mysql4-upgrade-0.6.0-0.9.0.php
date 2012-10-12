<?php

$this->startSetup();

$this->_conn->addColumn($this->getTable('urapidflow/profile'), 'current_activity', 'varchar(100)');

$this->endSetup();