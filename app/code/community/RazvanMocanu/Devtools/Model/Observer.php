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
 * Devtools observer
 *
 * PHP version 5.5
 *
 * @category RazvanMocanu_Devtools
 * @package  RazvanMocanu_Devtools
 * @author   Razvan Mocanu <razvan@mocanu.biz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://mocanu.biz
 */
class RazvanMocanu_Devtools_Model_Observer extends Varien_Event_Observer
{
    /**
     * Constructor
     */
    public function __construct()
    {

    }

    /**
     * Replaces the current content with the new content including information data.
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return void
     */
    public function highlightBlocks($observer)
    {
        if ((Mage::getDesign()->getArea() == 'frontend')
            && (Mage::getStoreConfig('devtools_options/block_info_settings/block_info_enabled'))
        ) {
            $observer->getTransport()->setHtml($this->_updateContent($observer));
        }
    }

    /**
     * Prepares the new content.
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return string
     */
    private function _updateContent($observer)
    {

        $blockDetails = $this->_prepareContent($observer);

        $_showEmptyBlocks = Mage::getStoreConfig('devtools_options/block_info_settings/show_empty_blocks');

        if ((!$_showEmptyBlocks && !$blockDetails['blockInitialContent'])) {
            $blockDetails['wrapperTag'] = "empty";
        }

        switch($blockDetails['wrapperTag']){
            case 'section':
                $newContent = $this->_prepareSection($blockDetails);
                break;
            case 'div':
                $newContent = $this->_prepareDiv($blockDetails);
                break;
            case 'comment':
                $newContent = $this->_prepareComment($blockDetails);
                break;
            default:
                $newContent = '';
        }

        return $newContent;
    }

    /**
     * Prepares and array with block data
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return array (Containing the block data.)
     */
    private function _prepareContent($observer)
    {
        $_currentBlock = $observer->getBlock();

        $_blockDetails = array();
        $_blockDetails['wrapperTag'] = $this->_getWrapperTag($_currentBlock);
        $_blockDetails['isRoot'] = $this->_getBlockIsRoot($_currentBlock);
        $_blockDetails['blockName'] = $this->_getBlockNameContent($_currentBlock);
        $_blockDetails['blockTemplate'] = $this->_getBlockTemplateContent($_currentBlock);
        $_blockDetails['CMSData'] = $this->_getBlockCMSInfoContent($_currentBlock);
        $_blockDetails['blockData'] = $this->_getBlockDataContent($_currentBlock);
        $_blockDetails['blockHover'] = $this->_getBlockHoverContent($_currentBlock);
        $_blockDetails['blockInitialContent'] = $observer->getTransport()->getHtml();

        return $_blockDetails;
    }

    /**
     * Gets from settings the wrapper tag used to encapsulate the information.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return mixed|string
     */
    private function _getWrapperTag($theBlock)
    {
        $_wrapperTag = Mage::getStoreConfig('devtools_options/block_info_settings/tag_select');
        // Set wrapper tag to comment if the block is root, head or contained in head.
        // In this cases no other tag can be used.
        if (($theBlock == 'root')
            || ($theBlock == 'head')
            || (($theBlock->getParentBlock() != null) && ($theBlock->getParentBlock()->getNameInLayout() == 'head'))
        ) {
            $_wrapperTag = 'comment';
        }
        return $_wrapperTag;
    }

    /**
     * Checks if the block is the root block.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return bool
     */
    private function _getBlockIsRoot($theBlock)
    {
        return ($theBlock->getNameInLayout() == 'root');
    }

    /**
     * Returns a string containing the layout update handles.
     *
     * @return string
     */
    private function _getLayoutHandles()
    {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_layout_handles')) {
            return "<!-- \n"
            . "Layout update handles: \n - "
            . implode("\n - ", Mage::app()->getLayout()->getUpdate()->getHandles())
            . "\n -->";
        } else {
            return "";
        }
    }

    /**
     * Returns the block name if the setting to display block name is enabled.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (The full attribute containing separator space,
     * attribute name "BlockName" and attribute value which is the
     * current block name.
     */
    private function _getBlockNameContent($theBlock)
    {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_name')) {
            return "\n" . 'BlockName="' . $theBlock->getNameInLayout() . '"';
        } else {
            return "";
        }
    }

    /**
     * Returns the block template file path if the setting
     * to display the template is enabled.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (The template file path if there is any,
     * formated as attribute.)
     */
    private function _getBlockTemplateContent($theBlock)
    {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_template')) {
            return "\n" . 'BlockTemplate="' . $theBlock->getTemplateFile() . '"';
        } else {
            return "";
        }
    }

    /**
     * Returns block data if the setting to display block data is enabled.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (Block data in a string.)
     */
    private function _getBlockDataContent($theBlock)
    {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_data')) {
            return $this->_prepareDataContent($theBlock);
        } else {
            return "";
        }
    }

    /**
     * Returns block data as string.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (The block data as string formated as attribute.)
     */
    private function _prepareDataContent($theBlock)
    {

        $_currentData = '';

        //get first level of data in array
        //if the value is array it will not be parsed
        foreach ($theBlock->debug() as $key => $value) {
            if ($key != "text") {
                if (!is_array($value)) {
                    $_currentData .= $key . ':' . $value . '; ';
                } else {
                    $_currentData .= $key . ':' . 'ARRAY' . '; ';
                }
            }
        }

        return "\n" . 'Data="' . $_currentData . '"';
    }

    /**
     * Returns a string of information to be displayed on hover
     * if hovering information can be displayed.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (Hover information formatted as attribute.)
     */
    private function _getBlockHoverContent($theBlock)
    {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_on_hover')) {
            return "\n" . 'title="' . $theBlock->getTemplateFile() . '" ';
        } else {
            return "";
        }
    }

    /**
     * Returns CMS information if block type is CMS
     * and CMS information display is enabled.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (CMS information formatted as attribute.)
     */
    private function _getBlockCMSInfoContent($theBlock)
    {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_cms_data')
            && in_array($theBlock->getType(), ["cms/block","cms/page"])
        ) {

            switch($theBlock->getType()){
                case 'cms/block':
                    $CMSInfo = $this->_getBlockCMSBlockInfo($theBlock);
                    break;
                case 'cms/page':
                    $CMSInfo = $this->_getBlockCMSPageInfo($theBlock);
                    break;
                default:
                    $CMSInfo = '';
            }

            return "\n" . 'CMSData="' . $CMSInfo . '"';
        } else {
            return "";
        }
    }

    /**
     * Get CMS Block information.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (CMS block information in string format.)
     */
    private function _getBlockCMSBlockInfo($theBlock)
    {

        return 'CMSBlockId: ' . $theBlock->getBlockId();
    }

    /**
     * Get CMS Page information.
     *
     * @param Mage_Core_Block_Template $theBlock (The current block.)
     * (There are different types of blocks extending the core block template.)
     *
     * @return string (CMS page information in string format.)
     */
    private function _getBlockCMSPageInfo($theBlock)
    {

        $currentPage = $theBlock->getPage();
        return 'CMSPageIdentifier: ' . $currentPage->getIdentifier();
    }

    /**
     * Prepares the information in format of section.
     *
     * @param array $blockDetails (Array containing the block information.)
     *
     * @return string (The new content including the information data as section.)
     */
    private function _prepareSection($blockDetails)
    {
        $begin = '<section'
            . $blockDetails['blockName']
            . $blockDetails['blockTemplate']
            . $blockDetails['CMSData']
            . $blockDetails['blockData']
            . '>' . "\n";
        $end = "\n" . '</section>';
        return $begin . $blockDetails['blockInitialContent'] . $end;
    }

    /**
     *  Prepares the information in format of div.
     *
     * @param array $blockDetails (Array containing the block information.)
     *
     * @return string (The new content including the information data as div.)
     */
    private function _prepareDiv($blockDetails)
    {
        $begin = '<div'
            . $blockDetails['blockHover']
            . $blockDetails['blockName']
            . $blockDetails['blockTemplate']
            . $blockDetails['CMSData']
            . $blockDetails['blockData']
            . '>' . "\n";
        $end = "\n" . '</div>';
        return $begin . $blockDetails['blockInitialContent'] . $end;
    }

    /**
     *  Prepares the information in format of comment.
     *
     * @param array $blockDetails (Array containing the block information.)
     *
     * @return string (The new content including the information data as comment.)
     */
    private function _prepareComment($blockDetails)
    {
        $begin = "\n" . '<!--' . "\n" . 'Begin'
            . $blockDetails['blockName']
            . $blockDetails['blockTemplate']
            . $blockDetails['CMSData']
            . $blockDetails['blockData']
            . "\n" . ' -->' . "\n";
        $end = "\n" . '<!-- End' . $blockDetails['blockName'] . ' -->';

        if ($blockDetails['isRoot']) {
            $pos = strpos($blockDetails['blockInitialContent'], '<html');
            if ($pos !== false) {
                return substr_replace(
                    $blockDetails['blockInitialContent'],
                    $this->_getLayoutHandles() . $begin . '<html',
                    $pos,
                    5
                ) . $end;
            } else {
                return  $begin . $blockDetails['blockInitialContent'] . $end;
            }

        } else {
            return  $begin . $blockDetails['blockInitialContent'] . $end;
        }

    }
}
