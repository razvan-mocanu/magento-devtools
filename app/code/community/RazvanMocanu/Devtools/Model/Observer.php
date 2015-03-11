<?php
namespace RazvanMocanu;

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
class RazvanMocanu_Devtools_Model_Observer extends Varien_Event_Observer {
    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * Replaces the current content with the new content including information data.
     *
     * @param Varien_Event_Observer $observer (The current observer.)
     *
     * @return void
     */
    public function highlightBlocks($observer) {
        if ((Mage::getDesign()->getArea() == 'frontend') && (Mage::getStoreConfig('devtools_options/block_info_settings/block_info_enabled'))) {
            $observer->getTransport()->setHtml($this->updateContent($observer));
        }
    }

    private function updateContent($observer) {

        $blockDetails = $this->prepareContentData($observer);

        $_showEmptyBlocks = Mage::getStoreConfig('devtools_options/block_info_settings/show_empty_blocks');

        if ((!$_showEmptyBlocks && !$blockDetails['blockInitialContent'])) {
            $blockDetails['wrapperTag'] = "empty";
        }

        return $this->prepareContent($blockDetails, $blockDetails['wrapperTag']);
    }

    private function prepareContentData($observer) {

        $_currentBlock = $observer->getBlock();

        $_blockDetails = array();
        $_blockDetails['wrapperTag'] = $this->getWrapperTag($_currentBlock)? $this->getWrapperTag($_currentBlock) : 'empty';
        $_blockDetails['isRoot'] = $this->_getBlockIsRoot($_currentBlock);
        $_blockDetails['blockName'] = $this->getBlockNameContent($_currentBlock);
        $_blockDetails['blockTemplate'] = $this->getBlockTemplateContent($_currentBlock);
        $_blockDetails['CMSData'] = $this->_getBlockCMSInfoContent($_currentBlock);
        $_blockDetails['blockData'] = $this->getBlockDataContent($_currentBlock);
        $_blockDetails['blockHover'] = $this->getBlockHoverContent($_currentBlock);
        $_blockDetails['blockInitialContent'] = $observer->getTransport()->getHtml();

        return $_blockDetails;
    }

    private function getWrapperTag($theBlock) {
        $_wrapperTag = Mage::getStoreConfig('devtools_options/block_info_settings/tag_select');
        // Set wrapper tag to comment if the block is root, head or contained in head.
        // In this cases no other tag can be used.

        $specialBlocks = array('root','head');

        if (in_array($theBlock,$specialBlocks) ||
            ($theBlock->getParentBlock() == null ? false : ($theBlock->getParentBlock()->getNameInLayout() == 'head'))
        ) {
            $_wrapperTag = 'comment';
        }
        return $_wrapperTag;
    }

    private function _getBlockIsRoot($theBlock)
    {
        return ($theBlock->getNameInLayout() == 'root');
    }

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

    private function getBlockNameContent($theBlock) {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_name')) {
            return "\n" . ' BlockName="' . $theBlock->getNameInLayout() . '"';
        } else {
            return "";
        }
    }

    private function getBlockTemplateContent($theBlock) {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_template')) {
            return "\n" . ' BlockTemplate="' . $theBlock->getTemplateFile() . '"';
        } else {
            return "";
        }
    }

    private function getBlockDataContent($theBlock) {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_data')) {
            return $this->prepareDataContent($theBlock);
        } else {
            return "";
        }
    }

    private function prepareDataContent($theBlock) {

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

        return "\n" . ' Data="' . $_currentData . '"';
    }

    private function getBlockHoverContent($theBlock) {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_on_hover')) {
            return ' title="' . $theBlock->getTemplateFile() . '" ';
        } else {
            return "";
        }
    }

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

    private function _getBlockCMSBlockInfo($theBlock)
    {

        return 'CMSBlockId: ' . $theBlock->getBlockId();
    }

    private function _getBlockCMSPageInfo($theBlock)
    {

        $currentPage = $theBlock->getPage();
        return 'CMSPageIdentifier: ' . $currentPage->getIdentifier();
    }

    private function prepareContent($blockDetails, $contentType) {
        $content = $blockDetails['blockName'] . $blockDetails['blockTemplate'] . $blockDetails['CMSData'] . $blockDetails['blockData'];
        $contentTypes = array(
            'section' => '<section' . $content . '>' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '</section>',
            'div' => '<div' . $blockDetails['blockHover'] . $content . '>' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '</div>',
            'comment' => "\n" . '<!--  Begin' . $content . ' -->' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '<!-- End' . $blockDetails['blockName'] . ' -->',
            'empty' => ''
        );


        $begin = "\n" . '<!--  Begin' . $content . ' -->' . '<html';
        $end = "\n" . '<!-- End' . $blockDetails['blockName'] . ' -->';

        if ($blockDetails['isRoot']) {
            $pos = strpos($blockDetails['blockInitialContent'], '<html');
            if ($pos !== false) {
                return substr_replace(
                    $blockDetails['blockInitialContent'],
                    $this->_getLayoutHandles() . $begin,
                    $pos,
                    5
                ) . $end;
            } else {
                $contentTypes['comment'] =  $begin . $blockDetails['blockInitialContent'] . $end;
            }

        } else {
            $contentTypes['comment'] =    $begin . $blockDetails['blockInitialContent'] . $end;
        }

        return $contentTypes[$contentType];
    }
}
