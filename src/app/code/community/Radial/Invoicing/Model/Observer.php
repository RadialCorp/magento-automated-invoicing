<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Radial_Invoicing_Model_Observer
{
    /** @var  Radial_Invoicing_Helper_Data */
    protected $helper;

    public function __construct(array $initParams = [])
    {
        list($this->helper) = $this->_checkTypes(
            $this->_nullCoalesce($initParams, 'helper', Mage::helper('radial_invoicing'))
        );
    }
    /**
     * Type hinting for self::__construct $initParams
     * @param Radial_Invoicing_Helper_Data
     * @return array
     */
    protected function _checkTypes(
        Radial_Invoicing_Helper_Data $helper
    ) {
        return func_get_args();
    }
    /**
     * Return the value at field in array if it exists. Otherwise, use the
     * default value.
     * @param array      $arr
     * @param string|int $field Valid array key
     * @param mixed      $default
     * @return mixed
     */
    protected function _nullCoalesce(array $arr, $field, $default)
    {
        return isset($arr[$field]) ? $arr[$field] : $default;
    }

    /**
     * @param Varien_Event_Observer
     */
    public function handleFraudAccept(Varien_Event_Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var Mage_Sales_Model_Order $order */
        $order = $event->getOrder();
        if ($this->helper->doesOrderNeedReconfirm($order)) {
            $this->helper->confirmFundsForOrder($order);
        }
        $this->helper->setOrderReadyToShip($order);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleShipmentSave(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $invoice = $this->helper->createInvoiceFromShipment($shipment);
        $this->helper->processInvoice($invoice);
    }
}
