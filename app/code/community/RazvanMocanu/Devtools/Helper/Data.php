<?php
/**
 * Development Tools
 *
 * PHP version 5.5
 *
 * @category  RazvanMocanu
 * @package   RazvanMocanu_Devtools
 * @author    Razvan Mocanu <razvan@mocanu.biz>
 * @copyright 2015 Razvan Mocanu (http://mocanu.biz)
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link      http://mocanu.biz
 */

/**
 * Devtools helper class
 *
 * PHP version 5.5
 *
 * @category RazvanMocanu_Devtools
 * @package  RazvanMocanu_Devtools
 * @author   Razvan Mocanu <razvan@mocanu.biz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://mocanu.biz
 */

class RazvanMocanu_Devtools_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Makes the string representation of the tag attribute
     *
     * @param boolean $isUsed         (If false returns empty string)
     * @param string  $attributeName  (The attribute name)
     * @param string  $attributeValue (The attribute value)
     * @param bool    $onNewLine      (If true, a new line char is added before)
     *
     * @return string
     */
    public function makeAttribute($isUsed, $attributeName, $attributeValue, $onNewLine = true)
    {
        if ($isUsed) {
            return ($onNewLine? "\n" : "")
                . ' ' . $attributeName . '="' . $attributeValue . '"';
        } else {
            return "";
        }
    }
}
