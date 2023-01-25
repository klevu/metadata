<?php

namespace Klevu\Metadata\Service\IsEnabledCondition;

use Klevu\Metadata\Api\IsEnabledConditionInterface;
use Klevu\Metadata\Constants;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class OrderSyncIsEnabledCondition implements IsEnabledConditionInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function execute($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            Constants::XML_PATH_METADATA_ORDERSYNC,
            ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }
}
