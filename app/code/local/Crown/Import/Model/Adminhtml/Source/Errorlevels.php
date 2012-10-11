<?php
/**
 *
 * @category 	Crown
 * @package 	Crown_Import
 * @since 		1.1.4
 */
class Crown_Import_Model_Adminhtml_Source_Errorlevels extends Mage_Core_Model_Abstract {

    /**
     *
     * @var int
     * @since 1.1.4
     */
    const LEVEL_WARNING     = 0;

    /**
     *
     * @var int
     * @since 1.1.4
     */
    const LEVEL_ERROR       = 1;

    /**
     * Returns the levels for error messages
     * @since 1.1.4
     * @return array
     */
    public function toOptionArray() {
        return array(
            self::LEVEL_WARNING => 'Warning',
            self::LEVEL_ERROR   => 'Error',
        );
    }
}
