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

class Unirgy_RapidFlow_Model_Profile extends Mage_Core_Model_Abstract
{
    protected $_resourceModel;

    protected $_defaultIoModel = 'urapidflow/io_csv';
    protected $_defaultLoggerModel = 'urapidflow/logger_csv';

    protected $_saveFields = array(
        'snapshot_at',
        'rows_found',
        'rows_processed',
        'rows_success',
        'rows_nochange',
        'rows_empty',
        'rows_depends',
        'rows_errors',
        'num_errors',
        'num_warnings',
        'memory_usage',
        'memory_peak_usage'
    );
    protected $_loadFields = array('run_status');
    protected $_jsonFields = array(
        'columns' => 'columns_json',
        'options' => 'options_json',
        'conditions' => 'conditions_json',
        'profile_state' => 'profile_state_json',
    );
    protected $_jsonImportFields = array(
        "columns",
        "options",
        "conditions",
    );
    protected $_defaults = array(
        'options' => array(
            'csv' => array(
                'delimiter'=>',',
                'enclosure'=>'"',
                'escape'=>'\\',
                'multivalue_separator'=>';',
            ),
            'encoding' => array(
                'from' => 'UTF-8',
                'to' => 'UTF-8',
            ),
        ),
    );
    protected $_gt14;

    protected $_lastSync;

    protected function _construct()
    {
        $this->_init('urapidflow/profile');
    }

    public function factory()
    {
        $dataTypes = Mage::getSingleton('urapidflow/config')->getDataTypes();
        $type = $this->getDataType();
        if (!$type) {
            return $this;
            //Mage::throwException('Data type is not set');
        }
        $model = $dataTypes->descend("$type/profile/model");
        if (!$model) {
            return $this;
        }
        $object = Mage::getModel($model);
        if (!$object) {
            Mage::throwException(Mage::helper('urapidflow')->__('Invalid profile model: %s', $model));
        }
        $object->setData($this->getData());
        return $object;
    }

    public function addValue($k, $v=1)
    {
        $this->setData($k, $this->_getData($k)+$v);
        return $this;
    }

    public function getDataTypeModel()
    {
        $root = Mage::getSingleton('urapidflow/config')->getDataTypes();
        $dataType = $this->getDataType();
        if (!isset($root->$dataType) || !isset($root->$dataType->model)) {
            Mage::throwException(Mage::helper('urapidflow')->__('Invalid data type model'));
        }
        return Mage::getSingleton((string)$root->$dataType->model);
    }

    public function getLogTail($length=1000)
    {
        try {
            $io = $this->getLogger()->start('r')->seek(-$length, SEEK_END)->getIo();
        } catch (Exception $e) {
            return array();
        }
        $tail = array();
        while ($t = $io->read()) {
            if (sizeof($t)!==4 || !in_array($t[0], array('ERROR', 'WARNING', 'SUCCESS'))) {
                continue;
            }
            $tail[] = array('type'=>$t[0], 'line'=>$t[1], 'col'=>$t[2], 'msg'=>$t[3]);
        }
        return $tail;
    }

    protected function _run()
    {
        $res = $this->getDataTypeModel();
        $res->setProfile($this);

        if (Mage::helper('urapidflow')->hasEeGwsFilter()
            && !Mage::getSingleton('enterprise_admingws/role')->hasStoreAccess($this->getStoreId())
        ) {
            Mage::throwException(Mage::helper('urapidflow')->__('You are not allowed to run this profile'));
        }

        if ($this->getProfileType()=='import') {
            $res->import();
        } else {
            $res->export();
        }
    }

    public function run()
    {
        if ($this->isLocked()) {
            return;//TODO: (?) notify that is already running
        }

        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);
        ob_implicit_flush();

        $this->getLogger()->start('w');

        try {
            $this->lock();
            $this->_run();
        } catch (Unirgy_RapidFlow_Exception_Stop $e) {
            $this->setCurrentActivity(Mage::helper('urapidflow')->__('Stopped'));
            $this->stop()->save();
        } catch (Exception $e) {
            $this->setCurrentActivity(Mage::helper('urapidflow')->__('Error'));
            $this->addValue('num_errors');
            $this->getLogger()->error($e->getMessage());
            $this->stop()->save();
            throw $e;
        }
        if ($this->getRunStatus()=='running') {
            $this->finish()->save();

            $this->doReindexActions();

            $this->activity(Mage::helper('urapidflow')->__('Done'));
        }

        return $this;
    }

    public function getLockFilename()
    {
        if (!$this->hasData('lock_filename')) {
            $dir = Mage::getConfig()->getVarDir('urapidflow/lock');
            Mage::getConfig()->createDirIfNotExists($dir);
            $filename = $dir.'/profile-'.$this->getId().'.lck';
            $this->setData('lock_filename', $filename);
        }
        return $this->_getData('lock_filename');
    }

    public function lock()
    {
        // touch() didn't work for some reason...
        $result = @file_put_contents($this->getLockFilename(), '');
        return $this;
    }

    public function unlock()
    {
        @unlink($this->getLockFilename());
        return $this;
    }

    public function isLocked()
    {
        $result = file_exists($this->getLockFilename());
        return $result;
    }

    public function activity($activity)
    {
        if ($this->getLogger()->getIo()->isOpen()) {
            $this->getLogger()->log('ACTIVITY', array('', '', $activity));
        }
        $this->setCurrentActivity($activity)->sync(true, array('current_activity'), false);
        return $this;
    }

    protected function _beforeRun()
    {

    }

    protected function _afterRun()
    {

    }

    public function sync($force=false, $saveFields=null, $loadFields=null)
    {
        if (!$force && $this->_lastSync && $this->_lastSync >= time()-2) {
            return false;
        }
        if (!($this->_getData('title') && $this->_getData('profile_type'))) {
            return false;
        }
        $saveFields = !is_null($saveFields) ? $saveFields : $this->_saveFields;
        $loadFields = !is_null($loadFields) ? $loadFields : $this->_loadFields;
        $this->_getResource()->sync($this, $saveFields, $loadFields);
        $this->_lastSync = time();
        return true;
    }

    public function pending($invokeStatus)
    {
        if ($this->getProfileStatus()!=='enabled') {
            return $this;;
        }

        if ($this->getProfileType()=='import') {
            $sameType = $this->getCollection()
                ->addFieldToFilter('profile_id', array('neq'=>$this->getId()))
                ->addFieldToFilter('profile_type', 'import')
                ->addFieldToFilter('data_type', $this->getDataType())
                ->addFieldToFilter('run_status', array('in'=>array('pending', 'running', 'paused')));
            if ($sameType->count()) {
                throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('A profile of the same type is currently running or paused'));
            }
        }

        if (in_array($this->getRunStatus(), array('pending', 'running', 'paused'))) {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is currently running or paused'));
        }

        $this->setInvokeStatus($invokeStatus);
        $this->setRunStatus('pending')->setCurrentActivity(Mage::helper('urapidflow')->__('Pending'));
        $this->getLogger()->pendingProfile();

        $this->reset();

        $this->loggerStart();

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'pending', 'profile'=>$this));

        return $this;
    }

    public function loggerStart()
    {
        $this->getLogger()->start('w');
        return $this;
    }

    public function loggerStartProfile()
    {
        $this->getLogger()->startProfile();
        return $this;
    }

    public function start()
    {
        if ($this->getProfileStatus()!=='enabled') {
            return $this;
        }

        if (in_array($this->getRunStatus(), array('running', 'paused'))) {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is currently running or paused'));
        }

        if ($this->getRunStatus()!=='pending') {
            $this->reset();
        }

        $this->setRunStatus('running');
        $this->loggerStartProfile();

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'start', 'profile'=>$this));

        return $this;
    }

    public function pause()
    {
        if ($this->getRunStatus()!='running') {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is not currently running'));
        }

        $this->unlock();

        $this->setRunStatus('paused');
        $this->getLogger()->pauseProfile();

        $this->setPausedAt(now());

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'pause', 'profile'=>$this));

        return $this;
    }

    public function resume()
    {
        if ($this->getRunStatus()!='paused') {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is not currently paused'));
        }

        $this->setRunStatus('pending');
        $this->getLogger()->resumeProfile();

        $this->unsPausedAt();

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'resume', 'profile'=>$this));

        return $this;
    }

    public function stop()
    {
        if (!in_array($this->getRunStatus(), array('pending', 'running', 'paused'))) {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is not currently running or paused'));
        }

        $this->unlock();

        $this->setRunStatus('stopped');
        $this->getLogger()->stopProfile();

        $this->setStoppedAt(now());

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'stop', 'profile'=>$this));

        return $this;
    }

    public function finish()
    {
        if ($this->getRunStatus()!='running') {
            return $this;
            #throw new Unirgy_RapidFlow_Exception(Mage::helper('urapidflow')->__('The profile is not currently running'));
        }

        $this->unlock();

        $this->setRunStatus('finished');
        $this->getLogger()->finishProfile();

        $this->setFinishedAt(now());

        Mage::dispatchEvent('urapidflow_profile_action', array('action'=>'finish', 'profile'=>$this));

        return $this;
    }

    public function reset()
    {
        foreach ($this->_saveFields as $f) {
            $this->setData($f, 0);
        }

        $this->setStartedAt(null)->setPausedAt(null)->setStoppedAt(null)->setFinishedAt(null);

        //$this->getLogger()->reset();

        return $this;
    }

    public function getIo()
    {
        if (!$this->hasData('io')) {
            $this->setIo($this->_defaultIoModel);
        }
        return $this->getData('io');
    }

    public function setIo($io)
    {
        if (is_string($io)) {
            $io = Mage::getModel($io);
        }
        $io->setBaseDir($this->getFileBaseDir());
        $io->addData((array)$this->getData('options/csv'));
        $this->setData('io', $io);
        return $this;
    }

    public function ioOpenRead($doActionsBefore=true)
    {
        if ($doActionsBefore) {
            $this->doFileActions('before');
        }
        $this->getIo()->open($this->getFilename(), 'r');
        return $this;
    }

    public function ioSeekReset($line=0)
    {
        $this->getIo()->seek($line);
        return $this;
    }

    public function ioTell()
    {
        return $this->getIo()->tell();
    }

    public function ioOpenWrite()
    {
        $this->getIo()->open($this->getFilename(), 'w');
        return $this;
    }

    public function ioWriteHeader($data)
    {
        $this->ioWrite($data);
        return $this;
    }

    public function ioWrite($data)
    {
        $this->getIo()->write($data);
        return $this;
    }

    public function ioRead()
    {
        return $this->getIo()->read();
    }

    public function ioClose()
    {
        $this->getIo()->close();
        $this->doFileActions('after');
    }

    public function getLogger()
    {
        if (!$this->hasData('logger')) {
            $this->setLogger($this->_defaultLoggerModel);
        }
        return $this->getData('logger');
    }

    public function setLogger($logger)
    {
        if (is_string($logger)) {
            $logger = Mage::getModel($logger);
        }
        $logger->setProfile($this);
        $this->setData('logger', $logger);
        return $this;
    }

    public function getConditionsRule()
    {
        if (!$this->hasData('conditions_rule')) {
            $rule = Mage::getModel('urapidflow/rule');
            $rule->getConditions()->setConditions(array())->loadArray($this->getConditions());
            $this->setData('conditions_rule', $rule);
        }
        return $this->getData('conditions_rule');
    }

    public function getConditionsProductIds()
    {
        return $this->getConditionsRule()->getProductIds($this);
    }

    public function realtimeReindex($productIds)
    {
        return $this; // disabled until done correctly

        if (!$this->getData('options/reindex_realtime/all') || !$productIds) {
            return $this;
        }

        if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
            $action = Mage::getSingleton('catalog/product_action')->setData(array(
                'product_ids'       => array_unique($productIds),
                //'attributes_data'   => $attrData,
                'store_id'          => $this->getStoreId(),
            ));
            Mage::getSingleton('index/indexer')->processEntityAction(
                $action, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
            );
        }

        return $this;
    }

    /**
    * For Magento 1.3.x only
    *
    */
    public function getReindexTypeNames()
    {
        return array(
            'catalog_index' => Mage::helper('urapidflow')->__('Catalog Index'),
            'layered_navigation' => Mage::helper('urapidflow')->__('Layered Navigation'),
            'images_cache' => Mage::helper('urapidflow')->__('Images Cache'),
            'catalog_url' => Mage::helper('urapidflow')->__('Catalog Url Rewrites'),
            'catalog_product_flat' => Mage::helper('urapidflow')->__('Product Flat Data'),
            'catalog_category_flat' => Mage::helper('urapidflow')->__('Category Flat Data'),
            'catalogsearch_fulltext' => Mage::helper('urapidflow')->__('Catalog Search Index'),
            'cataloginventory_stock' => Mage::helper('urapidflow')->__('Stock status'),
        	'catalog_rules' => Mage::helper('urapidflow')->__('Catalog Rules'),
        );
    }

    public function doReindexActions()
    {
        Mage::dispatchEvent('urapidflow_profile_reindex_before', array('profile'=>$this));

        if ($this->getProfileType()!='import' || $this->getSkipReindex() || $this->getData('options/import/dryrun')) {
            return $this;
        }
        if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
            if ($this->getData('options/import/reindex_type') != 'realtime') {
                $indexer = Mage::getSingleton('index/indexer');
                $processes = (array)$this->getData('options/reindex');
                $pricesReindexed = $catalogRulesApplied = false;
                if (array_key_exists('catalog_rules', $processes)) {
                	$priceProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
                	if ($this->getData('options/import/reindex_type') == 'full') {
                		$this->activity(Mage::helper('urapidflow')->__('Reindexing: %s', Mage::helper('catalogrule')->__('Catalog Rules')));
                		Mage::getModel('urapidflow/catalogRule')->applyAllNoIndex();
            			Mage::app()->removeCache('catalog_rules_dirty');
	                	if ($priceProcess) {
	                		$this->activity(Mage::helper('urapidflow')->__('Reindexing: %s', $priceProcess->getIndexer()->getName()));
						    $priceProcess->reindexEverything();
						}
                	} else {
                		Mage::app()->saveCache(1, 'catalog_rules_dirty');
                		$this->activity(Mage::helper('urapidflow')->__('Invalidating index: %s', Mage::helper('catalogrule')->__('Catalog Rules')));
	                	if ($priceProcess) {
	                		$this->activity(Mage::helper('urapidflow')->__('Invalidating index: %s', $priceProcess->getIndexer()->getName()));
						    $priceProcess->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
						}
                	}
                }
                foreach ($processes as $code=>$sortOrder) {
                    $process = $indexer->getProcessByCode($code);
                    if (!$process) continue;
                    if ($this->getData('options/import/reindex_type') == 'full') {
                        $this->activity(Mage::helper('urapidflow')->__('Reindexing: %s', $process->getIndexer()->getName()));
                    } elseif ($this->getData('options/import/reindex_type') == 'manual') {
                        $this->activity(Mage::helper('urapidflow')->__('Invalidating index: %s', $process->getIndexer()->getName()));
                    }
                    try {
                        if ($this->getData('options/import/reindex_type') == 'full') {
                            $process->reindexEverything();
                        } elseif ($this->getData('options/import/reindex_type') == 'manual') {
                            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                        }
                    } catch (Exception $e) {
                        $this->getLogger()->unsLine()->unsColumn()->error($e->getMessage());
                    }
                }
            }

        } else {
            $processes = (array)$this->getData('options/reindex');
            $labels = $this->getReindexTypeNames();
            foreach ($processes as $code=>$sortOrder) {
                $this->activity(Mage::helper('urapidflow')->__('Reindexing: %s', !empty($labels[$code]) ? $labels[$code] : $code));
                try {
                    switch ($code) {
                    case 'catalog_index':
                        Mage::getSingleton('catalog/index')->rebuild();
                        break;

                    case 'layered_navigation':
                        $flag = Mage::getModel('catalogindex/catalog_index_flag')->loadSelf();
                        if ($flag->getState() == Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_RUNNING) {
                            $kill = Mage::getModel('catalogindex/catalog_index_kill_flag')->loadSelf();
                            $kill->setFlagData($flag->getFlagData())->save();
                        }

                        $flag->setState(Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_QUEUED)->save();
                        Mage::getSingleton('catalogindex/indexer')->plainReindex();
                        break;

                    case 'images_cache':
                        Mage::getModel('catalog/product_image')->clearCache();
                        break;

                    case 'catalog_url':
                        Mage::getSingleton('catalog/url')->refreshRewrites();
                        break;

                    case 'catalog_product_flat':
                        Mage::getResourceModel('catalog/product_flat_indexer')->rebuild();
                        break;

                    case 'catalog_category_flat':
                        Mage::getResourceModel('catalog/category_flat')->rebuild();
                        break;

                    case 'catalogsearch_fulltext':
                        Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex();
                        break;

                    case 'cataloginventory_stock':
                        Mage::getSingleton('cataloginventory/stock_status')->rebuild();
                        break;
                    case 'catalog_rules':
			            Mage::getResourceSingleton('catalogrule/rule')->applyAllRulesForDateRange();
			            Mage::app()->removeCache('catalog_rules_dirty');
                        break;
                    }
                } catch (Exception $e) {
                    $this->getLogger()->unsLine()->unsColumn()->error($e->getMessage());
                }
            }
        }

        if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
            $labels = array();
            foreach (Mage::app()->getCacheInstance()->getTypes() as $type) {
                $labels[$type->getId()] = $type->getDescription();
            }
        } else {
            $labels = Mage::helper('core')->getCacheTypes();
        }
        $refresh = (array)$this->getData('options/refresh');
        foreach ($refresh as $code=>$sortOrder) {
            switch ($code) {
            case 'clean_media':
                $this->activity(Mage::helper('urapidflow')->__('Refreshing: %s', $code));
                Mage::getModel('catalog/product_image')->clearCache();
                Mage::dispatchEvent('clean_catalog_images_cache_after');
                Mage::getModel('core/design_package')->cleanMergedJsCss();
                Mage::dispatchEvent('clean_media_cache_after');
                break;

            default:
                $this->activity(Mage::helper('urapidflow')->__('Refreshing: %s', !empty($labels[$code]) ? $labels[$code] : $code));
                Mage::app()->cleanCache(array($code));
            }
        }

        return $this;
    }

    public function doFileActions($when)
    {
        $remoteType = $this->getData('options/remote/type');
        $compressType = $this->getData('options/compress/type');
        if ($when=='before' && $this->getProfileType()=='import') {
            switch ($remoteType) {
            case 'ftp': case 'ftps':
                $this->_ftpDownload();
                break;
            }
            switch ($compressType) {
            case 'zip':
                $this->_zipExtract();
                break;
            }
        } elseif ($when=='after' && $this->getProfileType()=='export') {
            switch ($compressType) {
            case 'zip':
                $this->_zipArchive();
                break;
            }
            switch ($remoteType) {
            case 'ftp': case 'ftps':
                $this->_ftpUpload();
                break;
            }
        }
        return $this;
    }

    protected function _ftpOpen()
    {
        $remote = (array)$this->getData('options/remote');
        if (empty($remote['host'])) {
            Mage::throwException(Mage::helper('urapidflow')->__('Empty or invalid remote host name'));
        }
        $host = $remote['host'];
        $port = !empty($remote['port']) ? (int)$remote['port'] : 21;
        if ($remote['type']=='ftp') {
            $conn = @ftp_connect($host, $port);
        } else {
            $conn = @ftp_ssl_connect($host, $port);
        }
        if (!$conn) {
            $e = error_get_last();
            Mage::throwException(Mage::helper('urapidflow')->__("Error connecting to remote host '%s': %s", $remote['host'], $e['message']));
        }
        if (empty($remote['username']) && empty($remote['password'])) {
            $username = 'anonymous';
            $password = 'a@b.com';
        } elseif (empty($remote['username'])) {
            Mage::throwException(Mage::helper('urapidflow')->__("Empty or invalid remote user name"));
        } else {
            $username = $remote['username'];
            $password = !empty($remote['password']) ? $remote['password'] : '';
        }
        $result = @ftp_login($conn, $remote['username'], $remote['password']);
        if (!$result) {
            $e = error_get_last();
            Mage::throwException(Mage::helper('urapidflow')->__("Error logging in to remote host '%s': %s", $remote['host'], $e['message']));
        }

        if (!isset($remote['ftp_passive']) || $remote['ftp_passive']) {
            @ftp_pasv($conn, true);
        }

        if (!empty($remote['path'])) {
            $result = @ftp_chdir($conn, $remote['path']);
            if (!$result) {
                $e = error_get_last();
                Mage::throwException(Mage::helper('urapidflow')->__("Error changing remote path '%s': %s", $remote['path'], $e['message']));
            }
        }

        return $conn;
    }

    protected function _ftpDownload()
    {
        $remote = (array)$this->getData('options/remote');
        $this->activity(Mage::helper('urapidflow')->__('Downloading file from FTP'));

        $conn = $this->_ftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        @unlink($localFile);
        $fileMode = isset($remote['ftp_file_mode']) ? $remote['ftp_file_mode'] : FTP_BINARY;
        $result = @ftp_get($conn, $localFile, $this->getFilename(), $fileMode);
        if (!$result) {
            $e = error_get_last();
            Mage::throwException(Mage::helper('urapidflow')->__("Error transferring remote file: %s", $e['message']));
        }
        @ftp_close($conn);
    }

    protected function _ftpUpload()
    {
        $remote = (array)$this->getData('options/remote');
        $this->activity(Mage::helper('urapidflow')->__('Uploading file to FTP'));

        $conn = $this->_ftpOpen();
        $localFile = $this->getIo()->getFilepath($this->getFilename());
        $fileMode = isset($remote['ftp_file_mode']) ? $remote['ftp_file_mode'] : FTP_BINARY;
        $result = @ftp_put($conn, $this->getFilename(), $localFile, $fileMode);
        if (!$result) {
            $e = error_get_last();
            Mage::throwException(Mage::helper('urapidflow')->__("Error transferring remote file: ", $e['message']));
        }
        @ftp_close($conn);
    }

    protected function _zipArchive()
    {

    }

    protected function _zipExtract()
    {

    }

    protected function _processDir($dir, $default=true)
    {
        $dir = str_replace(
            array('{magento}', '{var}', '{media}'),
            array(Mage::getBaseDir(), Mage::getConfig()->getVarDir(), Mage::getBaseDir('media')),
            $dir
        );
        return $dir;
    }

    public function getFileBaseDir()
    {
        $dir = $this->getBaseDir();
        if (!$dir) {
            $dir = Mage::getStoreConfig('urapidflow/dirs/'.$this->getProfileType().'_dir', $this->getStoreId());
        }
        return $this->_processDir($dir);
    }

    public function getImagesBaseDir($autoCreate = false)
    {
        $dir = $this->getData('options/dir/images');
        if (!$dir) {
            $dir = Mage::getStoreConfig('urapidflow/dirs/images_dir', $this->getStoreId());
        }
        $dir = $this->_processDir($dir);
        if (!$dir) {
            $dir = Mage::getBaseDir('media').DS.'import';
        } elseif ($dir[0]!=='/' && $dir[1]!==':') {
            $dir = rtrim($this->getFileBaseDir(), '/').'/'.$dir;
        }
        if ($autoCreate) {
            Mage::getConfig()->createDirIfNotExists($dir);
        }
        return $dir;
    }

    public function getLogBaseDir()
    {
        $dir = Mage::getStoreConfig('urapidflow/dirs/log_dir', $this->getStoreId());
        return $this->_processDir($dir);
    }

    public function getLogFilename()
    {
        return $this->getFilename().'-'.$this->getProfileType().'.log';
    }

    public function getExcelReportBaseDir()
    {
        $dir = Mage::getStoreConfig('urapidflow/dirs/report_dir', $this->getStoreId());
        return $this->_processDir($dir);
    }

    public function getExcelReportFilename()
    {
        return $this->getFilename().'.xls';
    }

    public function exportExcelReport()
    {
        // open import file
        $this->ioOpenRead(false);
        // open log file
        $log = $this->getLogger()->start('r')->getIo();
        // start excel out file
        $out = Mage::getModel('urapidflow/io_file')
            ->setBaseDir($this->getExcelReportBaseDir())
            ->open($this->getExcelReportFilename(), 'w');

        // excel report header
        $out->write('<?xml version="1.0"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
xmlns:html="http://www.w3.org/TR/REC-html40">
<Styles>
<Style ss:ID="Default" ss:Name="Normal"></Style>
<Style ss:ID="SUCCESS"><Interior ss:Color="#CEEAB0" ss:Pattern="Solid"/></Style>
<Style ss:ID="WARNING"><Interior ss:Color="#FDE9D9" ss:Pattern="Solid"/></Style>
<Style ss:ID="ERROR"><Interior ss:Color="#FAC090" ss:Pattern="Solid"/></Style>
</Styles>
<Worksheet ss:Name="Sheet1">');

        if (in_array($this->getDataType(), array('product', 'category'))) {
            $out->write('
<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
<Selected/><FreezePanes/><FrozenNoSplit/><SplitHorizontal>1</SplitHorizontal>
<TopRowBottomPane>1</TopRowBottomPane><ActivePane>2</ActivePane>
<Panes><Pane><Number>3</Number></Pane><Pane><Number>2</Number><ActiveRow>0</ActiveRow></Pane></Panes>
</WorksheetOptions>');
        }

        $out->write('<Table>');

        $rowNum = 1;
        $row = true;
        $logRows = array();
        while (true) {
            if ($logRows) {
                $oldLogRows = $logRows;
                if ($lastLogRowNum) {
                    $logRows = array($lastLogRowNum=>$oldLogRows[$lastLogRowNum]);
                } else {
                    $logRows = array();
                }
            } else {
                $logRows = array();
            }
            if ($log) {
                $lastLogRowNum = 0;
                for ($i=0; $i<1000; $i++) {
                    $l = $log->read();
                    if (!$l) {
                        $lastLogRowNum = false;
                        break;
                    }
                    if (sizeof($l)!=4) {
                        continue;
                    }
                    $logRows[$l[1]][$l[2]-1][0] = $l[0];
                    $logRows[$l[1]][$l[2]-1][1][] = $l[3];
                    $lastLogRowNum = $l[1];
                }
            }
            while (!empty($row) && (!$lastLogRowNum || $rowNum<$lastLogRowNum)) {
                $row = $this->ioRead();
                if (empty($row)) break 2;
                $logData = $rowNum==1 ? true : (!empty($logRows[$rowNum]) ? $logRows[$rowNum] : array());
                $out->write($this->_getExcelRow($row, $logData));
                $rowNum++;
            }
        }
        $out->write('</Table></Worksheet></Workbook>');
        $out->close();

        return $this;
    }

    protected function _getExcelRow($r, $l=array())
    {
        $hlp = Mage::helper('urapidflow');
        if ($l===true) {
            $l = array(
                0=>array('SUCCESS', Mage::helper('urapidflow')->__('Sample for searching')),
                1=>array('WARNING', Mage::helper('urapidflow')->__('Sample for searching')),
                2=>array('ERROR', Mage::helper('urapidflow')->__('Sample for searching')),
            );
        }
        $out = '<Row>';
        foreach ($r as $k=>$v) {
            $type = false;
            if (!empty($l[$k]) || !empty($l[-1])) {
                $type = !empty($l[$k][0]) ? $l[$k][0] : $l[-1][0];
                $comment = join("<br/>", !empty($l[$k][1]) ? (array)$l[$k][1] : (!empty($l[-1][1]) && $k==0 ? (array)$l[-1][1] : array()));
                $out .= '<Cell ss:StyleID="'.$type.'">';
                if ($comment) {
                    $out .= '<Comment><ss:Data>'.$hlp->__($type).': '.htmlspecialchars($comment).'</ss:Data></Comment>';
                }
            } elseif ($v!=='') {
                $out .= '<Cell>';
            } else {
                $out .= '<Cell/>';
            }
            if ($v!=='') {
                #$out .= '<Data ss:Type="String">'.($v!==''?'<![CDATA['.$v.']]>':'').'</Data></Cell>'."\n";
                $out .= '<Data ss:Type="String">'.htmlentities($v, ENT_QUOTES).'</Data></Cell>';
            } elseif ($type) {
                $out .= '</Cell>';
            }
        }
        $out .= '</Row>'."\n";
        return $out;
    }

    protected function _processColumnsPost()
    {
    	if ($this->hasColumnsPost()) {
            $columns = array();
            foreach ($this->getColumnsPost() as $k=>$a) {
                foreach ($a as $i=>$v) {
                    if ($v!=='') {
                        $columns[$i][$k] = $v;
                    }
                }
            }
            $this->setColumns($columns);
        }
    }
    protected function _processPostData()
    {
        $this->_processColumnsPost();
        if ($this->hasRule()) {
            $this->getConditionsRule()->parseConditionsPost($this, $this->getRule());
        }
        if ($this->getJsonImport()) {
            $this->importFromJson($this->getJsonImport());
        }
    }

    protected function _serializeData()
    {
        foreach ($this->_jsonFields as $k=>$f) {
            if (!is_null($this->getData($k))) {
                $this->setData($f, Zend_Json::encode($this->getData($k)));
            }
        }
    }

    protected function _unserializeData()
    {
        foreach ($this->_jsonFields as $k=>$f) {
            if (!is_null($this->getData($f))) {
                $this->setData($k, Zend_Json::decode($this->getData($f)));
            }
        }
    }

    protected function _applyDefaults()
    {
        foreach ($this->_defaults as $k=>$d) {
            $this->setData($k, $this->_arrayMergeRecursive($d, (array)$this->getData($k)));
        }
    }

    public function importFromJson($json)
    {
        $data = Zend_Json::decode($json);
        if (!$data) {
            return $this;
        }
        foreach ($this->_jsonImportFields as $k) {
            if (empty($data[$k])) {
                continue;
            }
            $cur = $this->getData($k);
            $new = $data[$k];
            if (empty($cur) && is_array($new)) {
                $this->setData($k, $new);
            } elseif (is_array($cur) && is_array($new)) {
                $this->setData($k, $this->_arrayMergeRecursive($cur, $new));
            }
        }
        return $this;
    }

    public function exportToJson()
    {
        $data = $this->toArray($this->_jsonImportFields);
        foreach ($data as $k=>$v) {
            if (!$v) {
                unset($data[$k]);
            }
        }

        $json = Zend_Json::encode($data);
        /*
        $result    = '';
        $pos       = 0;
        $strLen    = strlen($json);
        $indentStr = '  ';
        $newLine   = "\n";
        for ($i = 0; $i <= $strLen; $i++) {
            // Grab the next character in the string
            $char = substr($json, $i, 1);
            // If this character is the end of an element,
            // output a new line and indent the next line
            if ($char == '}' || $char == ']') {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }
            // Add the character to the result string
            $result .= $char;
            // If the last character was the beginning of an element,
            // output a new line and indent the next line
            if ($char == ',' || $char == '{' || $char == '[') {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }
                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }
        }
        */
        return $json;
    }

    public function _arrayMergeRecursive()
    {
        $params = func_get_args();
        $return = array_shift($params);
        foreach ($params as $array) {
            foreach ($array as $key=>$value) {
                if (is_numeric($key) && (!in_array($value, $return))) {
                    if (is_array ( $value ) && isset($return[$key])) {
                        $return[] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[] = $value;
                    }
                } else {
                    if (isset($return[$key]) && is_array($value) && is_array($return[$key])) {
                        $return[$key] = $this->_arrayMergeRecursive($return[$key], $value);
                    } else {
                        $return[$key] = $value;
                    }
                }
            }
        }

        return $return;
    }

    protected function _beforeSave()
    {
        $this->_processPostData();
        $this->_serializeData();
        parent::_beforeSave();
        $this->_dataSaveAllowed = $this->_getData('title') && $this->_getData('profile_type');
    }

    protected function _afterLoad()
    {
        try {
            Mage::getStoreConfig('urapidflow/dirs/log_dir', $this->getStoreId());
        } catch (Exception $e) {
            $this->setStoreId(0);
        }
        $this->_unserializeData();
        $this->_applyDefaults();
        parent::_afterLoad();
    }

    protected $_defaultDatetimeFormat;
    public function getDefaultDatetimeFormat()
    {
    	if (null === $this->_defaultDatetimeFormat) {
	    	Mage::app()->getLocale()->emulate($this->getStoreId());
	    	$this->_defaultDatetimeFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
	    	Mage::app()->getLocale()->revert();
	    	$this->_defaultDatetimeFormat = Mage::helper('urapidflow')->convertIsoToPhpDateFormat($this->_defaultDatetimeFormat);
    	}
    	return $this->_defaultDatetimeFormat;
    }

    protected $_profileLocale;
    public function getProfileLocale()
    {
    	if (null === $this->_profileLocale) {
    		Mage::app()->getLocale()->emulate($this->getStoreId());
	    	$this->_profileLocale = clone Mage::app()->getLocale()->getLocale();
	    	Mage::app()->getLocale()->revert();
    	}
    	return $this->_profileLocale;
    }
}