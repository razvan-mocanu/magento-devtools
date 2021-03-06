<?php
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

    /**
     * Checks if highlighting is applied in Frontend.
     *
     * @return boolean
     */
    public function highlightFrontend()
    {
        return (
            (Mage::getDesign()->getArea() == 'frontend')
            && (Mage::getStoreConfig('devtools_options/block_info_settings/block_info_enabled'))
        );
    }

    /**
     * Checks if highlighting is applied in Admin.
     *
     * @return boolean
     */
    public function highlightAdmin()
    {
        return (
            (Mage::getDesign()->getArea() == 'adminhtml')
            && (Mage::getStoreConfig('devtools_options/block_info_settings/block_info_enabled_admin'))
        );
    }

    /**
     * Get the wrapper tag from config
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    public function getWrapperTag($theBlock)
    {
        $wrapperTag = Mage::getStoreConfig('devtools_options/block_info_settings/tag_select');

        // Set wrapper tag to comment if the block is root, head or contained in head.
        // In this cases no other tag can be used.
        if ($this->isSpecialBlock($theBlock)) {
            $wrapperTag = 'comment';
        }
        return $wrapperTag ? $wrapperTag : 'empty';
    }

    /**
     * Check if block is root, head or contained in head.
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return bool
     */
    private function isSpecialBlock($theBlock)
    {
        $specialBlocks = array('root','head');

        return (in_array($theBlock, $specialBlocks) ||
            ($theBlock->getParentBlock() === null ? false : ($theBlock->getParentBlock()->getNameInLayout() == 'head'))
        );
    }

    /**
     * Check if CMS data is to be displayed.
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return bool
     */
    public function hasShowCMSInfo($theBlock)
    {
        return (Mage::getStoreConfig('devtools_options/block_info_settings/show_cms_data')
        && in_array($theBlock->getType(), ["cms/block","cms/page"]));
    }
}
