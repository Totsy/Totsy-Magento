<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_ShippingFactory_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCode($type, $code='')
    {
        $codes = array(
            'action'=>array(
                'single'=>'3',
                'all'=>'4',
            ),

            'originShipment'=>array(
                // United States Domestic Shipments
                'United States Domestic Shipments' => array(
                    '01' => Mage::helper('shippingfactory')->__('UPS Next Day Air'),
                    '02' => Mage::helper('shippingfactory')->__('UPS Second Day Air'),
                    '03' => Mage::helper('shippingfactory')->__('UPS Ground'),
                    '07' => Mage::helper('shippingfactory')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '12' => Mage::helper('shippingfactory')->__('UPS Three-Day Select'),
                    '13' => Mage::helper('shippingfactory')->__('UPS Next Day Air Saver'),
                    '14' => Mage::helper('shippingfactory')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('shippingfactory')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                ),
                // Shipments Originating in United States
                'Shipments Originating in United States' => array(
                    '01' => Mage::helper('shippingfactory')->__('UPS Next Day Air'),
                    '02' => Mage::helper('shippingfactory')->__('UPS Second Day Air'),
                    '03' => Mage::helper('shippingfactory')->__('UPS Ground'),
                    '07' => Mage::helper('shippingfactory')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '12' => Mage::helper('shippingfactory')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('shippingfactory')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('shippingfactory')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Worldwide Saver'),
                ),
                // Shipments Originating in Canada
                'Shipments Originating in Canada' => array(
                    '01' => Mage::helper('shippingfactory')->__('UPS Express'),
                    '02' => Mage::helper('shippingfactory')->__('UPS Expedited'),
                    '07' => Mage::helper('shippingfactory')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '12' => Mage::helper('shippingfactory')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('shippingfactory')->__('UPS Express Early A.M.'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                ),
                // Shipments Originating in the European Union
                'Shipments Originating in the European Union' => array(
                    '07' => Mage::helper('shippingfactory')->__('UPS Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express PlusSM'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                ),
                // Polish Domestic Shipments
                'Polish Domestic Shipments' => array(
                    '07' => Mage::helper('shippingfactory')->__('UPS Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                    '82' => Mage::helper('shippingfactory')->__('UPS Today Standard'),
                    '83' => Mage::helper('shippingfactory')->__('UPS Today Dedicated Courrier'),
                    '84' => Mage::helper('shippingfactory')->__('UPS Today Intercity'),
                    '85' => Mage::helper('shippingfactory')->__('UPS Today Express'),
                    '86' => Mage::helper('shippingfactory')->__('UPS Today Express Saver'),
                ),
                // Puerto Rico Origin
                'Puerto Rico Origin' => array(
                    '01' => Mage::helper('shippingfactory')->__('UPS Next Day Air'),
                    '02' => Mage::helper('shippingfactory')->__('UPS Second Day Air'),
                    '03' => Mage::helper('shippingfactory')->__('UPS Ground'),
                    '07' => Mage::helper('shippingfactory')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Worldwide Expedited'),
                    '14' => Mage::helper('shippingfactory')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                ),
                // Shipments Originating in Mexico
                'Shipments Originating in Mexico' => array(
                    '07' => Mage::helper('shippingfactory')->__('UPS Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Expedited'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Express Plus'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver'),
                ),
                // Shipments Originating in Other Countries
                'Shipments Originating in Other Countries' => array(
                    '07' => Mage::helper('shippingfactory')->__('UPS Express'),
                    '08' => Mage::helper('shippingfactory')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('shippingfactory')->__('UPS Standard'),
                    '54' => Mage::helper('shippingfactory')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('shippingfactory')->__('UPS Saver')
                )
            ),

            'method'=>array(
                '1DM'    => Mage::helper('shippingfactory')->__('Next Day Air Early AM'),
                '1DML'   => Mage::helper('shippingfactory')->__('Next Day Air Early AM Letter'),
                '1DA'    => Mage::helper('shippingfactory')->__('Next Day Air'),
                '1DAL'   => Mage::helper('shippingfactory')->__('Next Day Air Letter'),
                '1DAPI'  => Mage::helper('shippingfactory')->__('Next Day Air Intra (Puerto Rico)'),
                '1DP'    => Mage::helper('shippingfactory')->__('Next Day Air Saver'),
                '1DPL'   => Mage::helper('shippingfactory')->__('Next Day Air Saver Letter'),
                '2DM'    => Mage::helper('shippingfactory')->__('2nd Day Air AM'),
                '2DML'   => Mage::helper('shippingfactory')->__('2nd Day Air AM Letter'),
                '2DA'    => Mage::helper('shippingfactory')->__('2nd Day Air'),
                '2DAL'   => Mage::helper('shippingfactory')->__('2nd Day Air Letter'),
                '3DS'    => Mage::helper('shippingfactory')->__('3 Day Select'),
                'GND'    => Mage::helper('shippingfactory')->__('Ground'),
                'GNDCOM' => Mage::helper('shippingfactory')->__('Ground Commercial'),
                'GNDRES' => Mage::helper('shippingfactory')->__('Ground Residential'),
                'STD'    => Mage::helper('shippingfactory')->__('Canada Standard'),
                'XPR'    => Mage::helper('shippingfactory')->__('Worldwide Express'),
                'WXS'    => Mage::helper('shippingfactory')->__('Worldwide Express Saver'),
                'XPRL'   => Mage::helper('shippingfactory')->__('Worldwide Express Letter'),
                'XDM'    => Mage::helper('shippingfactory')->__('Worldwide Express Plus'),
                'XDML'   => Mage::helper('shippingfactory')->__('Worldwide Express Plus Letter'),
                'XPD'    => Mage::helper('shippingfactory')->__('Worldwide Expedited'),
            ),

            'pickup'=>array(
                'RDP'    => array("label"=>'Regular Daily Pickup',"code"=>"01"),
                'OCA'    => array("label"=>'On Call Air',"code"=>"07"),
                'OTP'    => array("label"=>'One Time Pickup',"code"=>"06"),
                'LC'     => array("label"=>'Letter Center',"code"=>"19"),
                'CC'     => array("label"=>'Customer Counter',"code"=>"03"),
            ),

            'container'=>array(
                'CP'     => '00', // Customer Packaging
                'ULE'    => '01', // UPS Letter Envelope
                'CSP'    => '02', // Customer Supplied Package
                'UT'     => '03', // UPS Tube
                'PAK'    => '04', // PAK
                'UEB'    => '21', // UPS Express Box
                'UW25'   => '24', // UPS Worldwide 25 kilo
                'UW10'   => '25', // UPS Worldwide 10 kilo
                'PLT'    => '30', // Pallet
                'SEB'    => '2a', // Small Express Box
                'MEB'    => '2b', // Medium Express Box
                'LEB'    => '2c', // Large Express Box
            ),

            'container_description'=>array(
                'CP'     => Mage::helper('shippingfactory')->__('Customer Packaging'),
                'ULE'    => Mage::helper('shippingfactory')->__('UPS Letter Envelope'),
                'CSP'    => Mage::helper('shippingfactory')->__('Customer Supplied Package'),
                'UT'     => Mage::helper('shippingfactory')->__('UPS Tube'),
                'PAK'    => Mage::helper('shippingfactory')->__('PAK'),
                'UEB'    => Mage::helper('shippingfactory')->__('UPS Express Box'),
                'UW25'   => Mage::helper('shippingfactory')->__('UPS Worldwide 25 kilo'),
                'UW10'   => Mage::helper('shippingfactory')->__('UPS Worldwide 10 kilo'),
                'PLT'    => Mage::helper('shippingfactory')->__('Pallet'),
                'SEB'    => Mage::helper('shippingfactory')->__('Small Express Box'),
                'MEB'    => Mage::helper('shippingfactory')->__('Medium Express Box'),
                'LEB'    => Mage::helper('shippingfactory')->__('Large Express Box'),
            ),

            'dest_type'=>array(
                'RES'    => '01', // Residential
                'COM'    => '02', // Commercial
            ),

            'dest_type_description'=>array(
                'RES'    => Mage::helper('shippingfactory')->__('Residential'),
                'COM'    => Mage::helper('shippingfactory')->__('Commercial'),
            ),

            'unit_of_measure'=>array(
                'LBS'   =>  Mage::helper('shippingfactory')->__('Pounds'),
                'KGS'   =>  Mage::helper('shippingfactory')->__('Kilograms'),
            ),
            'containers_filter' => array(
                array(
                    'containers' => array('00'), // Customer Packaging
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '12', // 3 Day Select
                                '59', // 2nd Day Air AM
                                '03', // Ground
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                                '11', // Standard
                            )
                        )
                    )
                ),
                array(
                    // Small Express Box, Medium Express Box, Large Express Box, UPS Tube
                    'containers' => array('2a', '2b', '2c', '03'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '13', // Next Day Air Saver
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '08', // Worldwide Expedited
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('24', '25'), // UPS Worldwide 25 kilo, UPS Worldwide 10 kilo
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array()
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('01', '04'), // UPS Letter, UPS PAK
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                '01', // Next Day Air
                                '14', // Next Day Air Early AM
                                '02', // 2nd Day Air
                                '59', // 2nd Day Air AM
                                '13', // Next Day Air Saver
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                '07', // Worldwide Express
                                '54', // Worldwide Express Plus
                                '65', // Worldwide Saver
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('04'), // UPS PAK
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array()
                        ),
                        'from_us' => array(
                            'method' => array(
                                '08', // Worldwide Expedited
                            )
                        )
                    )
                ),
            )
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }
} 