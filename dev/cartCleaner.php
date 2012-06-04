<?php	  
ini_set('memory_limit', '3G');
ini_set('max_input_time', 0);
require_once '../app/Mage.php';	  	  
umask(0);
$mageRunCode = isset($_SERVER['MAGE_RUN_CODE']) ? $_SERVER['MAGE_RUN_CODE'] : '';
$mageRunType = isset($_SERVER['MAGE_RUN_TYPE']) ? $_SERVER['MAGE_RUN_TYPE'] : 'store';
//Mage::app();//->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
Mage::app($mageRunCode, $mageRunType);	  

echo 'Start:' . PHP_EOL;

	define('DEFAULT_COLLECTION_SIZE_LIMIT', 100);
	$startTime = time();
	$itemCount = 0;
	$itemQtyCount = 0;
	$cartCount = 0;
	$lifetimes = Mage::getConfig()->getStoresConfigByPath('config/rushcheckout_timer/limit_timer');
        
	try{
        //Separate for different stores
        foreach ($lifetimes as $storeId => $lifetime) {
        	//Optimization for large collections, since sales/quote is a flat table, this is relatively efficient
        	//Entity ID offset is safer than LIMIT clause offset, this would safe guard against endless loop
        	//LIMIT clause offset is vulnerable if other processes also update the table
        	echo 'Cleaning store ' . $storeId . PHP_EOL;
        	$entityIdOffset = 0;
        	do{
	            $quoteCollection = Mage::getModel('sales/quote')->getCollection();
	            $quoteCollection->addFieldToFilter('store_id', $storeId)
						->addFieldToFilter('entity_id', array('gt' => $entityIdOffset))
	            		->addFieldToFilter('items_count', array('gt' => 0))
	            		->addFieldToFilter('updated_at', array('to' => date("Y-m-d H:i:s", time() - $lifetime)));
            	$quoteCollection->getSelect()
            			->limit(DEFAULT_COLLECTION_SIZE_LIMIT)
            			->order('entity_id ASC');
	            foreach($quoteCollection as $quote){
	                foreach($quote->getAllItems() as $item){
	                    $item->isDeleted(true);
	                    $item->delete(); //Cart reservation logic: item qty is release back to available by observer
	                    
	                    //Avoid double counting parent/children items
	                    if(!$item->getParentItemId()){
	                    	$itemQtyCount += $item->getQty(); 
	                    	$itemCount++;
	                    }
	                    
	                    unset($item);
	                }
	                $entityIdOffset = $quote->getId();
	                $quote->setData('items_count', 0)
	                		->setData('items_qty', 0)
	                		->setData('grand_total', 0)
	                		->setData('base_grand_total', 0)
	                		->setData('subtotal', 0)
	                		->setData('base_subtotal', 0)
	                		->setData('subtotal_with_discount', 0)
	                		->setData('base_subtotal_with_discount', 0)
	                		->save();
	                $cartCount++;
	                
	                if($cartCount % 20 == 0){
						echo sprintf('Pruned %d line items (%d total qty) out of %d carts, duration %s(s).', $itemCount, $itemQtyCount, $cartCount, time() - $startTime) . PHP_EOL;
						$startTime = time();	                	
	                }
	                unset($quote);
	            }
	            $collectionSize = count($quoteCollection);
	            unset($quoteCollection);
            }while($collectionSize >= DEFAULT_COLLECTION_SIZE_LIMIT);
        }
	}catch(Exception $e){
		echo $e->getMessage() . PHP_EOL;
	}

echo 'End!' . PHP_EOL;