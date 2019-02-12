<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 */

namespace Magenerds\PageDesigner\Controller\Adminhtml\PageDesigner;

use Magenerds\PageDesigner\Block\Adminhtml\SettingsAbstract as SettingsAbstractBlock;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class SettingsAbstract
 *
 * @package     Magenerds\PageDesigner\Controller\Adminhtml\PageDesigner
 * @file        SettingsAbstract.php
 * @copyright   Copyright (c) 2019 TechDivision GmbH (https://www.techdivision.com)
 * @site        https://www.techdivision.com/
 * @author      Simon Sippert <s.sippert@techdivision.com>
 */
abstract class SettingsAbstract extends Action
{
    /**
     * @var Json
     */
    protected $json;

    /**
     * SettingsAbstract constructor.
     *
     * @param Context $context
     * @param Json $json
     */
    public function __construct(
        Context $context,
        Json $json
    )
    {
        parent::__construct($context);
        $this->json = $json;
    }

    /**
     * Ajax responder for column settings
     */
    public function execute()
    {
        // load layout
        $this->_view->loadLayout();

        // retrieve parameters
        if (($params = $this->getRequest()->getParam('object')) &&
            ($params = $this->json->unserialize($params))
        ) {
            // set parameters on settings block
            /** @var SettingsAbstractBlock $settingsBlock */
            $settingsBlock = $this->_view->getLayout()->getBlock('page_designer.settings');
            $settingsBlock->setSettings($params);
        }

        // render view
        $this->_view->renderLayout();
    }
}
