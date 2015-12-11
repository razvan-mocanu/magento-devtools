<?php
/**
 * Devtools tag selector model
 *
 * PHP version 5.5
 *
 * @category RazvanMocanu_Devtools
 * @package  RazvanMocanu_Devtools
 * @author   Razvan Mocanu <razvan@mocanu.biz>
 * @license  http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     http://mocanu.biz
 */
class RazvanMocanu_Devtools_Model_Tagselect
{
    /**
     * Defines the tag array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 'comment',
                'label' => Mage::helper('devtools')->__('Comment block')
            ),

            array('value' => 'section',
                'label' => Mage::helper('devtools')->__('Section')
            ),

            array('value' => 'div',
                'label' => Mage::helper('devtools')->__('Div')
            ),
        );
    }
}
