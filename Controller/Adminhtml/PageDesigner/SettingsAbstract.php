<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Controller\Adminhtml\PageDesigner;

use Magento\Backend\App\Action;

/**
 * Class SettingsAbstract
 *
 * @package     Magenerds\PageDesigner\Controller\Adminhtml\PageDesigner
 * @file        SettingsAbstract.php
 * @copyright   Copyright (c) 2017 TechDivision GmbH (http://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
abstract class SettingsAbstract extends Action
{
    /**
     * Ajax responder for column settings
     *
     * @return void
     */
    public function execute()
    {
        // load layout
        $this->_view->loadLayout();

        // retrieve parameters
        if (($params = $this->getRequest()->getParam('object')) &&
            ($params = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonDecode($params))
        ) {
            // set parameters on settings block
            $settingsBlock = $this->_view->getLayout()->getBlock('page_designer.settings');
            $settingsBlock->setSettings($params);
        }

        // render view
        $this->_view->renderLayout();
    }
}
