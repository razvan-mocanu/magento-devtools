<?php

class RazvanMocanu_Devtools_Model_Observer extends Varien_Event_Observer {
    public function __construct() {
    }

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

        switch($blockDetails['wrapperTag']):
            case 'section':
                $newContent = $this->prepareContent($blockDetails, 'section');
                break;
            case 'div':
                $newContent = $this->prepareContent($blockDetails, 'div');
                break;
            case 'comment':
                $newContent = $this->prepareContent($blockDetails, 'comment');
                break;
            default:
                $newContent = '';
        endswitch;

        return $newContent;
    }

    private function prepareContentData($observer) {

        $_currentBlock = $observer->getBlock();

        $_blockDetails = array();
        $_blockDetails['wrapperTag'] = $this->getWrapperTag($_currentBlock);
        $_blockDetails['blockName'] = $this->getBlockNameContent($_currentBlock);
        $_blockDetails['blockTemplate'] = $this->getBlockTemplateContent($_currentBlock);
        $_blockDetails['blockData'] = $this->getBlockDataContent($_currentBlock);
        $_blockDetails['blockHover'] = $this->getBlockHoverContent($_currentBlock);
        $_blockDetails['blockInitialContent'] = $observer->getTransport()->getHtml();

        return $_blockDetails;
    }

    private function getWrapperTag($theBlock) {
        $_wrapperTag = Mage::getStoreConfig('devtools_options/block_info_settings/tag_select');
        // Set wrapper tag to comment if the block is root, head or contained in head.
        // In this cases no other tag can be used.
        if (($theBlock == 'root') ||
            ($theBlock == 'head') ||
            (($theBlock->getParentBlock() != null) &&
                ($theBlock->getParentBlock()->getNameInLayout() == 'head')
            )
        ) {
            $_wrapperTag = 'comment';
        }
        return $_wrapperTag;
    }

    private function getBlockNameContent($theBlock) {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_name')) {
            return ' BlockName="' . $theBlock->getNameInLayout() . '"';
        } else {
            return "";
        }
    }

    private function getBlockTemplateContent($theBlock) {
        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_block_template')) {
            return ' BlockTemplate="' . $theBlock->getTemplateFile() . '"';
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

        return ' Data="' . $_currentData . '"';
    }

    private function getBlockHoverContent($theBlock) {

        if (Mage::getStoreConfig('devtools_options/block_info_settings/show_on_hover')) {
            return ' title="' . $theBlock->getTemplateFile() . '" ';
        } else {
            return "";
        }
    }

    private function prepareContent($blockDetails, $contentType) {
        $content = $blockDetails['blockName'] . $blockDetails['blockTemplate'] . $blockDetails['blockData'];
        $contentTypes = array(
            'section' => '<section' . $content . '>' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '</section>',
            'div' => '<div' . $blockDetails['blockHover'] . $content . '>' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '</div>',
            'comment' => '<!--  Begin' . $content . ' -->' . "\n" . $blockDetails['blockInitialContent'] . "\n" . '<!-- End' . $blockDetails['blockName'] . ' -->'
        );

        return $contentTypes[$contentType];
    }
}
