<?php
class SnowCommerce_MailPreview_Adminhtml_OrderController extends Mage_Adminhtml_Controller_Action
{
    const XML_PATH_EMAIL_TEMPLATE               = 'sales_email/order/template';
    const XML_PATH_EMAIL_GUEST_TEMPLATE         = 'sales_email/order/guest_template';
    const XML_PATH_EMAIL_IDENTITY               = 'sales_email/order/identity';

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('sc_mail/order');
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction();
//            ->renderLayout();
//        $this->loadLayout();
//        var_dump($this->getLayout()->getUpdate()->getHandles()); die();
        $this->renderLayout();
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    public function viewAction()
    {
        $order = $this->_initOrder();
        $storeId = $order->getStore()->getId();

        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        if ($order->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = 'Guest';
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $order->getCustomerName();
        }

        $emailTemplate  = Mage::getModel('core/email_template')
            ->load($templateId)
            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));
//Create an array of variables to assign to template
        $logo_url = Mage::getBaseUrl()."skin/adminhtml/default/default/images/logo.png";
        $emailTemplateVariables = array(
            'name'         => $customerName,
            'order'        => $order,
            'billing'      => $order->getBillingAddress(),
            'payment_html' => $paymentBlockHtml,
            'store'        => Mage::app()->getStore($storeId),
            'logo_url'     => $logo_url
        );
        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);

        echo $processedTemplate;
    }


}