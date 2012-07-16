<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Model_Resource_Eav_Mysql4
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Model_Resource_Eav_Mysql4_Setup
    extends Mage_Eav_Model_Entity_Setup
{
    /**
     * Retrieve new customer entity attributes.
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        $entities = array(
            'customer' => array(
                'entity_model'                   => 'customer/customer',
                'attribute_model'                => 'customer/attribute',
                'table'                          => 'customer/entity',
                'increment_model'                => 'eav/entity_increment_numeric',
                'additional_attribute_table'     => 'customer/eav_attribute',
                'entity_attribute_collection'    => 'customer/attribute_collection',
                'attributes'      => array(
                    'deactivated'        => array(
                        'type'           => 'int',
                        'label'          => 'Deactivated',
                        'sort_order'     => 10,
                        'position'       => 10,
                        'adminhtml_only' => 1,
                    ),
                    'login_counter'        => array(
                        'type'           => 'int',
                        'label'          => 'Login Counter',
                        'sort_order'     => 15,
                        'position'       => 15,
                        'adminhtml_only' => 1,
                    ),
                )
            )
        );

        return $entities;
    }
}
