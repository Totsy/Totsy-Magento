<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Adminhtml customer grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('customerGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
    	//Harapartners, Jun, Query optimization
//    	$fields = array('firstname' => 'firstname', 'lastname' => 'lastname');
//    	$nameExpr = 'CONCAT_WS(LTRIM(RTRIM(at_firstname.value)), " ", LTRIM(RTRIM(at_lastname.value)))';
    	
        $collection = Mage::getResourceModel('customer/customer_collection')
//        	->addExpressionAttributeToSelect('name', $nameExpr, $fields)
//            ->addAttributeToSelect('email')
//            ->addAttributeToSelect('created_at')
//            ->addAttributeToSelect('group_id')
//            ->addAttributeToSelect('group_id')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));
//        $this->addColumn('name', array(
//            'header'    => Mage::helper('customer')->__('Name'),
//            'index'     => 'name',
//        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('Telephone', array(
            'header'    => Mage::helper('customer')->__('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => Mage::helper('customer')->__('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => Mage::helper('customer')->__('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));

        $this->addColumn('billing_region', array(
            'header'    => Mage::helper('customer')->__('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        	'filter'	=> 'adminhtml/widget_grid_column_filter_abstract'
        ));

        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('customer')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('customer')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('customer')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('customer')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return parent::_prepareColumns();
    }
    
    //Harapartners, Jun, Mass action generates gigantic JSON object, ignored here
//    protected function _prepareMassaction()
//    {
//        $this->setMassactionIdField('entity_id');
//        $this->getMassactionBlock()->setFormFieldName('customer');
//
//        //Harapartners Li
////        $this->getMassactionBlock()->addItem('delete', array(
////             'label'    => Mage::helper('customer')->__('Delete'),
////             'url'      => $this->getUrl('*/*/massDelete'),
////             'confirm'  => Mage::helper('customer')->__('Are you sure?')
////        ));
//        //
//
//        $this->getMassactionBlock()->addItem('newsletter_subscribe', array(
//             'label'    => Mage::helper('customer')->__('Subscribe to Newsletter'),
//             'url'      => $this->getUrl('*/*/massSubscribe')
//        ));
//
//        $this->getMassactionBlock()->addItem('newsletter_unsubscribe', array(
//             'label'    => Mage::helper('customer')->__('Unsubscribe from Newsletter'),
//             'url'      => $this->getUrl('*/*/massUnsubscribe')
//        ));
//
//        $groups = $this->helper('customer')->getGroups()->toOptionArray();
//
//        array_unshift($groups, array('label'=> '', 'value'=> ''));
//        $this->getMassactionBlock()->addItem('assign_group', array(
//             'label'        => Mage::helper('customer')->__('Assign a Customer Group'),
//             'url'          => $this->getUrl('*/*/massAssignGroup'),
//             'additional'   => array(
//                'visibility'    => array(
//                     'name'     => 'group',
//                     'type'     => 'select',
//                     'class'    => 'required-entry',
//                     'label'    => Mage::helper('customer')->__('Group'),
//                     'values'   => $groups
//                 )
//            )
//        ));
//
//        return $this;
//    }
    
    //Harapartners, Jun, Query optimization, Search email must use exact string
	protected function _addColumnFilterToCollection($column) {
		$exactSearchColumns = array('firstname', 'lastname', 'name', 'email');
		
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
            	if(in_array($field, $exactSearchColumns)){
            		$cond = $column->getFilter()->getValue();
            	}else{
                	$cond = $column->getFilter()->getCondition();
            	}
                if ($field && isset($cond)) {
                    $this->getCollection()->addFieldToFilter($field , $cond);
                }
            }
        }
        return $this;
    }
    
    protected function _setFilterValues($data) {
    	if (array_key_exists('email',$data)){
    		$data['email'] = $this->_trimGmail($data['email']);
    	}
    	
    	return parent::_setFilterValues($data);
    }
    
    protected function _trimGmail($email) {
        $strArray = explode('@', $email);
        
        if(empty($strArray) ||
           empty($strArray[1]) ||
           strtolower($strArray[1]) != 'gmail.com') {
        	echo 'Not Gmail<br>';
            return $email;
        }

        //get username, such as 'abcd'
        $trimmedGmail = preg_replace("/[\.]/",'',trim($strArray[0]));
        unset($strArray);

        if (strlen($trimmedGmail)==0){
        	return $email;
        }
        
        if (preg_match("/[\.]+/",$trimmedGmail)){
        	echo 'Found plus ..+.. <br>';
        	$u = explode('+',$trimmedGmail);
        	$trimmedGmail = $u[0];
        	unset($u);
        }
        
        $trimmedGmail .= '@gmail.com';

        return $trimmedGmail;
    }
    
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
