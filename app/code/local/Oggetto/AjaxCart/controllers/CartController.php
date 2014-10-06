<?php
/**
 * AjaxCart Controller
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
 * the Oggetto AjaxCart module to newer versions in the future.
 * If you wish to customize the Oggetto AjaxCart module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_AjaxCart
 * @copyright  Copyright (C) 2012 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Controller handling the ajax request cart
 *
 * @category   Oggetto
 * @package    Oggetto_AjaxCart
 * @subpackage Controller
 * @author     Sergei Waribrus <svaribrus@oggettoweb.com>
 */

require_once Mage::getModuleDir('controllers', 'Mage_Checkout') . DS . 'CartController.php';
class Oggetto_AjaxCart_CartController extends Mage_Checkout_CartController
{
    /**
     * Add Product to Cart
     *
     * @return $this
     */
    public function addAction()
    {
        if (!$this->getRequest()->isAjax() || $this->getRequest()->getParam('theme') == 'elc_mobile') {
            return parent::addAction();
        }
        $response = Mage::getModel('ajax/response');
        try {
            $cart = $this->_getCart();
            $params = $this->getRequest()->getParams();

            $product = $this->_initProduct();

            if (!isset($params['qty'])) {
                $params['qty'] = 1;
            }
            $params['qty'] = $this->_countedQty($params['qty'], $cart, $product);

            $related = $this->getRequest()->getParam('related_product');

            if (!$product) {
                throw new Exception($this->__('Product is missing.'));
            }

            $cart->addProduct($product, $params);
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();
            $this->_getSession()->setCartWasUpdated(true);
            Mage::register('product', $product);
            if ($this->getRequest()->getParam('wishlist')) {
                $response->success()->setContent(Mage::helper('ajaxcart')->_render(array('cart', 'popup', 'wishlist')));
            } else {
                $response->success()->setContent(Mage::helper('ajaxcart')->_render(array('cart', 'popup')));
            }
        } catch (Mage_Core_Exception $e) {
            $response->error()->setMessage($e->getMessage());
        } catch(Exception $e) {
            $response->error()->setMessage($this->__('Something went wrong.'));
            Mage::logException($e);
        }
        Mage::helper('ajax')->sendResponse($response);
    }

    /**
     * Delete product from cart
     *
     * @return $this
     */
    public function deleteAction()
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::deleteAction();
        }

        $response = Mage::getModel('ajax/response');

        try {
            $id = (int) $this->getRequest()->getParam('id');
            $this->_getCart()->removeItem($id)->save();
            if ($this->getRequest()->getParam('big')) {
                $response->success()->setContent(Mage::helper('ajaxcart')->_render(['big_cart', 'cart']));
            } else {
                $response->success()->setContent(Mage::helper('ajaxcart')->_render(['cart']));
            }
        }
        catch (Mage_Core_Exception $e) {
            $response->error()->setMessage($e->getMessage());
        } catch (Exception $e) {
            $response->error()->setMessage($this->__('Something went wrong.'));
            Mage::logException($e);
        }

        Mage::helper('ajax')->sendResponse($response);
    }

    /**
     * Counted qty
     *
     * @param $qty
     * @param $cart
     * @param $product
     */
    private function _countedQty($qty, $cart, $product)
    {
        $filter = new Zend_Filter_LocalizedToNormalized(
            array('locale' => Mage::app()->getLocale()->getLocaleCode())
        );
        $qty = (int) $filter->filter($qty);

        if ($itemQuote = $cart->getQuote()->getItemByProduct($product)) {
            $itemQuoteQty = (int)$itemQuote->getQty();
        } else {
            $itemQuoteQty = 0;
        }
        $stockQty = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
        if (!Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock()) {
            throw new Mage_Core_Exception($this->
                __('Item %s is not available', $product->getName()));
        }

        if ($stockQty <= $itemQuoteQty) {
            throw new Mage_Core_Exception($this->
                __('Product %s is already added to the shopping cart, the quantity available in stock %s PCs.',
                    $product->getName(), $stockQty));
        }

        if ($itemQuoteQty + $qty > $stockQty) {
            return $stockQty - $itemQuoteQty;
        }

        return $qty;
    }
}
