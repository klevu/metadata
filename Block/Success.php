<?php

namespace Klevu\Metadata\Block;

use Klevu\Metadata\Api\SuccessMetadataProviderInterface;
use Klevu\Metadata\Api\SerializerInterface;
use Klevu\Metadata\Service\IsOrderSyncEnabledDeterminer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Success extends Template implements MetadataInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var SuccessMetadataProviderInterface
     */
    private $successMetadataProvider;
    /**
     * @var IsOrderSyncEnabledDeterminer
     */
    private $isOrderSyncDeterminer;

    /**
     * @param TemplateContext $context
     * @param SerializerInterface $serializer
     * @param CheckoutSession $checkoutSession
     * @param SuccessMetadataProviderInterface $successMetadataProvider
     * @param IsOrderSyncEnabledDeterminer|null $isOrderSyncDeterminer
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        SerializerInterface $serializer,
        CheckoutSession $checkoutSession,
        SuccessMetadataProviderInterface $successMetadataProvider,
        IsOrderSyncEnabledDeterminer $isOrderSyncDeterminer,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->successMetadataProvider = $successMetadataProvider;
        $this->isOrderSyncDeterminer = $isOrderSyncDeterminer;
    }

    /**
     * @inheritDoc
     */
    public function getKlevuPageMeta()
    {
        $successMetadata = [];
        try {
            $order = $this->checkoutSession->getLastRealOrder();
            if ($order) {
                $successMetadata = $this->successMetadataProvider->getMetadataForOrderSuccess($order);
            }
        } catch (\Exception $e) {
            $this->_logger->error(
                sprintf("Unable to retrieve Checkout Metadata: %s", $e->getMessage()),
                ['originalException' => $e]
            );
        }

        return count($successMetadata) ? $this->serializer->serialize($successMetadata) : '';
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    protected function _toHtml()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        if (!$order || !method_exists($order, 'getAllItems') || !$order->getAllItems()) {
            return '';
        }
        try {
            $store = $this->_storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage(), ['originalException' => $e]);

            return '';
        }
        /** @noinspection PhpCastIsUnnecessaryInspection */
        if (!$this->isOrderSyncDeterminer->execute((int)$store->getId())) {
            return '';
        }

        return parent::_toHtml();
    }
}
