<?php
/**
 * PHP Version 5.3
 *
 * @category  Totsy
 * @package   Totsy_Sailthru
 * @author    Slavik Koshelevskyy <skosh@totsy.com>
 * @copyright 2013 Totsy LLC Copyright (c) 
 */

class Totsy_Sailthru_Model_Archive extends Mage_Core_Model_Abstract 
{

	protected $_archive_ttl = 90; // archive time to live in days
	protected $_record_ttl = 7; // queue record to live (before archived) in days

	public function process(){

		// archive recors older than 7 days
		$this->archiveRecords();
		// remove arcive older than 3 month
		$this->removeOldArchivres();

		return true;
	}

	public function archiveRecords(){

		while (true) {
			$results = $this->getListOfDoneQueue();
			if ($results['totalRecords']==0){
				break;
			}
			$results = $results['items'];
			$ids = array();

			$filename = 'sailthru_arcive_';
			$filename.= date('ymd');
			$filename.= '_'.$results['0']['id'];
			$filename.= '-'.$results[count($results)-1]['id'];
			$filename.= '.json';

			$fh = fopen(Mage::getBaseDir('tmp').'/'.$filename,'w');
			fwrite($fh,json_encode($results));
			fclose($fh);

			$this->removeArchivedRecords($results);
			unset($results);
		}
	}

	protected function removeOldArchivres() {
		$dir = scandir(Mage::getBaseDir('tmp'));
		foreach ($dir as $d) {
			if ($d == '.' || $d == '..' || !is_file($dir.'/'.$d)){
				continue;
			}
			if (preg_match('/sailthru_arcive_/', $d)){
				$time = time() - filectime($dir.'/'.$d);
				if (ceil($time/60/60/24)>$this->_archive_ttl){
					unlink($dir.'/'.$d);
				}
			}
		}
	}

	protected function getListOfDoneQueue() {
		return Mage::getModel('emailfactory/sailthruqueue')
			->getCollection()
			->addFieldToSelect('*')
			->addFieldToFilter('status','done')
			->addFieldToFilter(
				'created_at',
				array(
					'lt'=>date('Y-m-d H:i:s',strtotime('-'.$this->_record_ttl.' days'))
				)
			)
			->setCurPage(1)
			->setPageSize(500)
			->load()
			->toArray();
	}

	protected function removeArchivedRecords(&$results){
		$c = Mage::getModel('emailfactory/sailthruqueue')
			->getCollection()
			->getConnection();
			
		$ids = array();
		foreach ($results as $result ) {
			$ids[] = $result['id'];
		}
		$c->raw_query('DELETE FROM `sailthru_queue` WHERE `id` in ('.implode(',',$ids).')');
	}

}