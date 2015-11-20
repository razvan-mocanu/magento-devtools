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
            $observer->getTransport()->setHtml($this->updateContent($observer));
        }
    }

    /**
     * Updates the content with the block information.
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return string
     */
    private function updateContent($observer)
    {

        $blockDetails = $this->prepareContentData($observer);

        $_showEmptyBlocks = Mage::getStoreConfig('devtools_options/block_info_settings/show_empty_blocks');

        if ((!$_showEmptyBlocks && !$blockDetails['blockInitialContent'])) {
            $blockDetails['wrapperTag'] = "empty";
        }

        return $this->prepareContent($blockDetails, $blockDetails['wrapperTag']);
    }

    /**
     * Prepares an array containing the block information
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return array
     */
    private function prepareContentData($observer)
    {

        $_currentBlock = $observer->getBlock();

        $_blockDetails = array();
        $_blockDetails['wrapperTag'] = $this->_helper->getWrapperTag($_currentBlock);
        $_blockDetails['isRoot'] = $this->getBlockIsRoot($_currentBlock);
        $_blockDetails['blockName'] = $this->getBlockNameContent($_currentBlock);
        $_blockDetails['blockTemplate'] = $this->getBlockTemplateContent($_currentBlock);
        $_blockDetails['CMSData'] = $this->getBlockCMSInfoContent($_currentBlock);
        $_blockDetails['blockData'] = $this->getBlockDataContent($_currentBlock);
        $_blockDetails['blockHover'] = $this->getBlockHoverContent($_currentBlock);
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
    private function getBlockIsRoot($theBlock)
    {
        return ($theBlock->getNameInLayout() == 'root');
    }

    /**
     * Gets the list of update handles for the current page
     *
     * @return string
     */
    private function getLayoutHandles()
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
    private function getBlockNameContent($theBlock)
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
    private function getBlockTemplateContent($theBlock)
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
    private function getBlockDataContent($theBlock)
    {
        return $this->_helper->makeAttribute(
            Mage::getStoreConfig('devtools_options/block_info_settings/show_block_data'),
            'Data',
            $this->prepareDataContent($theBlock)
        );
    }

    /**
     * Prepares the block data content
     *
     * @param Mage_Core_Block_Abstract $theBlock (The actual block extends the core block)
     *
     * @return string
     */
    private function prepareDataContent($theBlock)
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
    private function getBlockHoverContent($theBlock)
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
    private function getBlockCMSInfoContent($theBlock)
    {

        if ($this->_helper->getShowCMSInfo($theBlock)) {

            switch($theBlock->getType()){
                case 'cms/block':
                    $CMSInfo = $this->getBlockCMSBlockInfo($theBlock);
                    break;
                case 'cms/page':
                    $CMSInfo = $this->getBlockCMSPageInfo($theBlock);
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
    private function getBlockCMSBlockInfo($theBlock)
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
    private function getBlockCMSPageInfo($theBlock)
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
    private function prepareContent($blockDetails, $contentType)
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
                    $this->getLayoutHandles() . $begin . "\n" . '<html',
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
