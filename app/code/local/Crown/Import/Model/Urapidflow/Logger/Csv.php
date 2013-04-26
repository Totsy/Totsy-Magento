<?php
/**
 *
 * @category Crown
 * @package Crown_Import
 * @since 1.3.5
 */

class Crown_Import_Model_Urapidflow_Logger_Csv extends Unirgy_RapidFlow_Model_Logger_Csv
{
    public  $suppressedWarningDelta     = 0;
    private $suppressedWarningStrings   = array(
        'Unknown field',
        'does not apply to product type',
        'will be ignored'
    );

    public function warning($message)
    {
        if ($this->getLevelWarning() && !$this->isSuppressed($message)) {
            $this->log('WARNING', array($this->getLine(), $this->getColumn(), $message));
        } else {
            $this->suppressedWarningDelta--;
        }

        return $this;
    }

    public function isSuppressed($message) {
        foreach ($this->suppressedWarningStrings as $warningString) {
            if (preg_match("/$warningString/", $message) == 1) {
                return true;
            }
        }

        return false;
    }
}
