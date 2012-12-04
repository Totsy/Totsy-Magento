<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_AdminNotification
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    WebShopApps
 * @package     WebShopApps WsaLogger
 * @copyright   Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Webshopapps_Wsalogger_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('wsalogger/log', 'notification_id');
    }

    public function loadLatestNotice(Webshopapps_Wsalogger_Model_Log $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' desc')
            ->where('is_read <> 1')
            ->where('is_remove <> 1')
            ->limit(1);
        $data = $this->_getReadAdapter()->fetchRow($select);

        if ($data) {
            $object->setData($data);
        }

        $this->_afterLoad($object);

        return $this;
    }

    public function getNoticeStatus(Webshopapps_Wsalogger_Model_Log $object)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array(
                'severity'     => 'severity',
                'count_notice' => 'COUNT(' . $this->getIdFieldName() . ')'))
            ->group('severity')
            ->where('is_remove=?', 0)
            ->where('is_read=?', 0);
        $return = array();
        $rowSet = $this->_getReadAdapter()->fetchAll($select);
        foreach ($rowSet as $row) {
            $return[$row['severity']] = $row['count_notice'];
        }
        return $return;
    }

    public function parse(Webshopapps_Wsalogger_Model_Log $object, array $data)
    {
        $write = $this->_getWriteAdapter();
        foreach ($data as $item) {
            $write->insert($this->getMainTable(), $item);
        }
    }
    
    public function truncate()
    {
    	$this->_getWriteAdapter()->truncate($this->getMainTable());
    }
}
