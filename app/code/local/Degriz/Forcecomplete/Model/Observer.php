<?php
class Degriz_Forcecomplete_Model_Observer
{

    public function addMassAction($observer)
    {
		$block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order')
        {
            $block->addItem('forcecomplete', array(
                'label' => Mage::helper('degriz_forcecomplete')->__('Complete order in one step'),
                'url' => Mage::app()->getStore()->getUrl('forcecomplete/adminhtml_forcecomplete/forcecomplete'),
            ));

            // Add "Confirm Shipment" mass action
            $block->addItem('confirmshipment', array(
                'label' => Mage::helper('degriz_forcecomplete')->__('Confirm Shipment'),
                'url' => Mage::app()->getStore()->getUrl('forcecomplete/adminhtml_forcecomplete/confirmshipment'),
            ));
			
        }
		
    }

    public function doForceInvoiceWithShipment($observer) {
            $order = $observer->getOrder();
            $orderId = $order->getIncrementId();
            Mage::getModel('sales/order')->loadByIncrementId($orderId)->setForcedDoShipmentWithInvoice(true)->save();
    }

}