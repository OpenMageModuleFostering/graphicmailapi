<?php
require_once 'Mage/Newsletter/controllers/ManageController.php';

class Module_Graphicmail_ManageController extends Mage_Newsletter_ManageController
{

    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
		/**************** GRAPHICMAIL CODE ******************/
		$graphicmail = Mage::getModel('customer/graphicmail');
		$data=Mage::helper('customer')->getCustomer()->getData();
		/*********************** END ************************/

        try {
            Mage::getSingleton('customer/session')->getCustomer()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
            ->save();
            if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
				/**************** GRAPHICMAIL CODE ******************/
				$graphicmail->subscribeGraphicMailNewsletter($data['email']);
				/*********************** END ************************/
 				
            } else {
                Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
				/**************** GRAPHICMAIL CODE ******************/
				$graphicmail->unSubscribeGraphicMailNewsletter($data['email']);
				/*********************** END ************************/
				
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
