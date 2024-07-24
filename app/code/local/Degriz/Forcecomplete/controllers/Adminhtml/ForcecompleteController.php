<?php
class Degriz_Forcecomplete_Adminhtml_ForcecompleteController extends Mage_Adminhtml_Controller_Action
{

	public function forcecompleteAction(){
		
		$ids=Mage::app()->getRequest()->getPost('order_ids');
		
		for($i=0;$i<count($ids);$i++){
					
			$order = Mage::getModel('sales/order')->load((int)$ids[$i]);

			try {

				if(!$order->canInvoice()) {
					$order->save();
				}

				$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
				$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
				$invoice->register();
                $invoice->setEmailSent(true);
				$invoice->getOrder()->setCustomerNoteNotify(false);
				$invoice->getOrder()->setIsInProcess(true);
				
				$transactionSave = Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder());
				$transactionSave->save();
                // $invoice->sendEmail(true, '');
				unset($invoice);
				
				if($order->canShip())
				{
					$shipment = $order->prepareShipment();
					$shipment->register();
					$order->setIsInProcess(true);							
					$transactionSave = Mage::getModel('core/resource_transaction')
						->addObject($shipment)
						->addObject($shipment->getOrder())
						->save()
					;
					$shipment->sendEmail(true, '');
					$shipment->setEmailSent(true);
					unset($shipment);
				}

				$order->save();

			} catch (Exception $e) {
				$order->save();
			}
			
		}
		
		Mage::getSingleton('core/session')->addSuccess("Selected orders have been completed!");

		// $this->_redirectReferer();
		
		Mage::app()->getResponse()->setRedirect($_SERVER['HTTP_REFERER']);
		Mage::app()->getResponse()->sendResponse();
		exit;
		
	}

    public function confirmshipmentAction()
    {
        $ids = Mage::app()->getRequest()->getPost('order_ids');

        for ($i = 0; $i < count($ids); $i++) {
            $order = Mage::getModel('sales/order')->load((int)$ids[$i]);

            try {
                if ($order->canShip()) {
                    $shipment = $order->prepareShipment();
                    $shipment->register();
                    $order->setIsInProcess(true);

                    $transactionSave = Mage::getModel('core/resource_transaction')
                        ->addObject($shipment)
                        ->addObject($shipment->getOrder())
                        ->save();
                    $shipment->sendEmail(true, '');
                    $shipment->setEmailSent(true);
                    unset($shipment);
                }

                $order->save();
            } catch (Exception $e) {
                $order->save();
            }
        }

        Mage::getSingleton('core/session')->addSuccess("Selected orders are confirmed for shipment!");

        Mage::app()->getResponse()->setRedirect($_SERVER['HTTP_REFERER']);
        Mage::app()->getResponse()->sendResponse();
        exit;
    }
}