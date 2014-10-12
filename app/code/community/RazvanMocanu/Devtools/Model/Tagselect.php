<?php

class RazvanMocanu_Devtools_Model_Tagselect {
    public function toOptionArray() {
        return array(
            array('value' => 'comment', 'label' => Mage::helper('devtools')->__('Comment block')),
            array('value' => 'section', 'label' => Mage::helper('devtools')->__('Section')),
            array('value' => 'div', 'label' => Mage::helper('devtools')->__('Div')),
        );
    }
}