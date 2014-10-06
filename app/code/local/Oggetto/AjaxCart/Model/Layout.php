<?php
/**
 * Checkout module
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
 * the Oggetto OpCheckout module to newer versions in the future.
 * If you wish to customize the Oggetto OpCheckout module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Oggetto
 * @package    Oggetto_Checkout
 * @copyright  Copyright (C) 2012 Oggetto Web ltd (http://oggettoweb.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout layout operations model
 *
 * @category   Oggetto
 * @package    Oggetto_Checkout
 * @subpackage Model
 * @author     Dan Kocherga <dan@oggettoweb.com>
 */
class Oggetto_AjaxCart_Model_Layout
{
    /**
     * Get update name by frontend alias
     *
     * @param string $alias Alias
     * @return string
     */
    public function getUpdateByFrontendAlias($alias)
    {
        $updates = Mage::app()->getConfig()->getNode('ajaxcart/block_layout_udpates');
        return (string) $updates->$alias;
    }

    /**
     * Load layout update html
     *
     * @param string $updateCode Update code
     * @return string
     */
    public function loadUpdateHtml($updateCode)
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load($updateCode);
        $layout->generateXml();
        $layout->generateBlocks();
        return $layout->getOutput();
    }

    /**
     * Render blocks content
     *
     * @param array $blocks Array of block codes
     * @return array (code1 => html1, code2 => html2, ...)
     */
    public function renderBlocks($blocks)
    {
        $result = array();
        foreach ($blocks as $_block) {
            $update = $this->getUpdateByFrontendAlias($_block);
            $result[$_block] = $update ? $this->loadUpdateHtml($update) : '';
        }
        return $result;
    }
}
