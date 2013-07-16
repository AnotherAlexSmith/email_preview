<?php
/**
 * Created by JetBrains PhpStorm.
 * User: alex
 * Date: 12.07.13
 * Time: 14:49
 * To change this template use File | Settings | File Templates.
 */
class SnowCommerce_MailPreview_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
//        die('dfghjk');
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'sc_mail';
        $this->_headerText = Mage::helper('sc_mail')->__('Orders');
        parent::__construct();
        $this->_removeButton('add');
    }
}