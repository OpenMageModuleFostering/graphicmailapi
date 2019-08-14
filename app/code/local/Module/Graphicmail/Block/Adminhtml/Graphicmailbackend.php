<?php  

class Module_Graphicmail_Block_Adminhtml_Graphicmailbackend extends Mage_Adminhtml_Block_Template {

    public function __construct() {  
        parent::__construct(); 
        $this->setTemplate('Graphicmail/graphicmailbackend.phtml');  
        //$this->setFormAction(Mage::getUrl('*/*/save/index/key'));  
    } 
}
?>