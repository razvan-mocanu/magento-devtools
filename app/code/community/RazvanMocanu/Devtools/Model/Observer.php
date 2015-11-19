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
 * Class RazvanMocanu_Devtools_Model_Observer
 *
 * @category RazvanMocanu/Devtools
 * @package  RazvanMocanu
 * @author   Razvan Mocanu <razvan@mocanu.biz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://mocanu.biz
 */
class RazvanMocanu_Devtools_Model_Observer extends Varien_Event_Observer
{

    private $_helper;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_helper = Mage::helper('devtools');
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
        if ($this->_helper->highlightFrontend() || $this->_helper->highlightAdmin())
        {
            $observer->getTransport()->setHtml($this->_updateContent($observer));
        }
    }

    /**
     * Updates the content with the block information.
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return string
     */
    private function _updateContent($observer)
    {

        $blockDetails = $this->_prepareContentData($observer);

        $_showEmptyBlocks = Mage::getStoreConfig('devtools_options/block_info_settings/show_empty_blocks');

        if ((!$_showEmptyBlocks && !$blockDetails['blockInitialContent'])) {
            $blockDetails['wrapperTag'] = "empty";
        }

        return $this->_prepareContent($blockDetails, $blockDetails['wrapperTag']);
    }

    /**
     * Prepares an array containing the block information
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return array
     */
    private function _prepareContentData($observer)
    {

        $_currentBlock = $observer->getBlock();

        $_blockDetails = array();
        $_blockDetails['wrapperTag'] = $this->_helper->getWrapperTag($_currentBlock);
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
     * Checks if the block is the root block
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return bool
     */
    private function _getBlockIsRoot($theBlock)
    {
        return ($theBlock->getNameInLayout() == 'root');
    }

    /**
     * Gets the list of update handles for the current page
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
     * Get the block name
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function _getBlockNameContent($theBlock)
    {
        return $this->_helper->makeAttribute(
            Mage::getStoreConfig('devtools_options/block_info_settings/show_block_name'),
            'BlockName',
            $theBlock->getNameInLayout()
        );
    }

    /**
     * Get the block template
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function _getBlockTemplateContent($theBlock)
    {
        return $this->_helper->makeAttribute(
            Mage::getStoreConfig('devtools_options/block_info_settings/show_block_template'),
            'BlockTemplate',
            $theBlock->getTemplateFile()
        );
    }

    /**
     * Get the block data (only partial)
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function _getBlockDataContent($theBlock)
    {
        return $this->_helper->makeAttribute(
            Mage::getStoreConfig('devtools_options/block_info_settings/show_block_data'),
            'Data',
            $this->_prepareDataContent($theBlock)
        );
    }

    /**
     * Prepares the block data content
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
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

        return $_currentData;
    }

    /**
     * Get block hover content
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function _getBlockHoverContent($theBlock)
    {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_on_hover')) {
            return ' title="' . $theBlock->getTemplateFile() . '" ';
        } else {
            return "";
        }
    }

    /**
     * Get information about CMS blocks
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function _getBlockCMSInfoContent($theBlock)
    {

        if ($this->_helper->getShowCMSInfo($theBlock)) {

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
     * Get CMS block ID
     *
     * @param Mage_Cms_Block_Block $theBlock (The actual CMS block block.)
     *
     * @return string
     */
    private function _getBlockCMSBlockInfo($theBlock)
    {

        return 'CMSBlockId: ' . $theBlock->getBlockId();
    }

    /**
     * Get CMS page Identifier
     *
     * @param Mage_Cms_Block_Page $theBlock (The actual CMS page block.)
     *
     * @return string
     */
    private function _getBlockCMSPageInfo($theBlock)
    {

        $currentPage = $theBlock->getPage();
        return 'CMSPageIdentifier: ' . $currentPage->getIdentifier();
    }

    /**
     * Prepares the actual content
     *
     * @param array  $blockDetails (Array containing the block details)
     * @param string $contentType  (String containing the content type)
     *
     * @return string
     */
    private function _prepareContent($blockDetails, $contentType)
    {
        $content = $blockDetails['blockName']
            . $blockDetails['blockTemplate']
            . $blockDetails['CMSData']
            . $blockDetails['blockData'];

        $contentTypes = array(

            'section' => '<section' . $content . '>'
                . "\n" . $blockDetails['blockInitialContent'] . "\n"
                . '</section>',

            'div' => '<div' . $blockDetails['blockHover'] . $content . '>'
                . "\n" . $blockDetails['blockInitialContent'] . "\n"
                . '</div>',

            'comment' => "\n" . '<!--  Begin' . $content . ' -->'
                . "\n" . $blockDetails['blockInitialContent'] . "\n"
                . '<!-- End' . $blockDetails['blockName'] . ' -->',

            'empty' => ''
        );


        $begin = "\n" . '<!--  Begin' . $content . ' -->';
        $end = "\n" . '<!-- End' . $blockDetails['blockName'] . ' -->';

        if ($blockDetails['isRoot']) {
            $pos = strpos($blockDetails['blockInitialContent'], '<html');
            if ($pos !== false) {
                return substr_replace(
                    $blockDetails['blockInitialContent'],
                    $this->_getLayoutHandles() . $begin . "\n" . '<html',
                    $pos,
                    5
                ) . $end;
            } else {
                $contentTypes['comment'] =  $begin
                    . $blockDetails['blockInitialContent']
                    . $end;
            }

        } else {
            $contentTypes['comment'] = $begin
                . $blockDetails['blockInitialContent']
                . $end;
        }

        return $contentTypes[$contentType];
    }
}
