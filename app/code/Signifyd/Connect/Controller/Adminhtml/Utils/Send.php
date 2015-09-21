<?php
namespace Signifyd\Connect\Controller\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Model\Resource\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Signifyd\Connect\Lib\SDK\Core\SignifydAPI;
use Signifyd\Connect\Lib\SDK\Core\SignifydSettings;
use Signifyd\Connect\Helper\LogHelper;

/**
 * Controller action for handling mass sending of Magento orders to Signifyd
 */
class Send extends AbstractMassAction
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Signifyd\Connect\Helper\LogHelper
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var SignifydAPI
     */
    protected $_api;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param ObjectManagerInterface $objectManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ObjectManagerInterface $objectManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->_coreConfig = $scopeConfig;
        $this->_logger = new LogHelper($logger, $scopeConfig);

        try {
            $settings = new SignifydSettings();
            $settings->apiKey = $scopeConfig->getValue('signifyd/general/key');

            $this->_api = new SignifydAPI($settings);
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    public function massAction(AbstractCollection $collection)
    {
        $this->_logger->debug("Backend hit!");

        // Redirect back to order grid
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');
        return $resultRedirect;
    }
}
