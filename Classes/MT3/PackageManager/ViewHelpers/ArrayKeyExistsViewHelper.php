<?php
namespace MT3\PackageManager\ViewHelpers;

use TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
 
/**
 * This view helper implements a condition for the PHP function array_key_exists()
 */
class ArrayKeyExistsViewHelper extends AbstractConditionViewHelper {

    /**
     * Check if given array contains given key. If yes, render the thenChild, otherwise
     * the elseChild
     *
     * @param array $array list of items
     * @param string $key key name
     * @return string
     */
    public function render($array, $key) {
        if (array_key_exists($key, $array)) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }
}
