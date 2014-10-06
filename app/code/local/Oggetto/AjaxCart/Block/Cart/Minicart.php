<?php
/**
 * Oggetto Web extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Oggetto Module AjaxCart to newer versions in the future.
 * If you wish to customize the Oggetto AjaxCart moduel for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_AjaxCart
 * @copyright  Copyright (C) 2014 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for header cart
 *
 * @category   Oggetto
 * @package    Oggetto_AjaxCart
 * @subpackage Block
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */
class Oggetto_AjaxCart_Block_Cart_Minicart extends Mage_Checkout_Block_Cart_Abstract
{
    /**
     * Get shopping cart items qty based on configuration
     *
     * @return int | float
     */
    public function getSummaryCount()
    {
        $cartQty = Mage::getSingleton('checkout/cart')->getSummaryQty();
        if (empty($cartQty)) {
            return 0;
        }
        return $cartQty;
    }

    /**
     * Get total cart
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return Mage::getSingleton('checkout/cart')->getQuote()->collectTotals()->getGrandTotal();
    }

    /**
     * Get text cart to header
     *
     * @return float
     */
    public function getTextCart()
    {
        return $this->__($this->helper('lang')->caseRuNumber($this->getSummaryCount(), 'product'));
    }
}
