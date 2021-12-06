<?php

namespace Klevu\Metadata\Block;

use Klevu\Metadata\Api\CheckoutMetadataProviderInterface;
use Klevu\Metadata\Api\SerializerInterface;
use Klevu\Metadata\Service\IsEnabledDeterminer;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Quote\Api\Data\CartInterface;

class Checkout extends Template implements MetadataInterface
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
     * @var CheckoutMetadataProviderInterface
     */
    private $checkoutMetadataProvider;

    /**
     * @var IsEnabledDeterminer
     */
    private $isEnabledDeterminer;

    /**
     * @param TemplateContext $context
     * @param SerializerInterface $serializer
     * @param CheckoutSession $checkoutSession
     * @param CheckoutMetadataProviderInterface $checkoutMetadataProvider
     * @param array $data
     * @param IsEnabledDeterminer|null $isEnabledDeterminer
     */
    public function __construct(
        TemplateContext $context,
        SerializerInterface $serializer,
        CheckoutSession $checkoutSession,
        CheckoutMetadataProviderInterface $checkoutMetadataProvider,
        array $data = [],
        IsEnabledDeterminer $isEnabledDeterminer = null
    ) {
        parent::__construct($context, $data);

        $this->serializer = $serializer;
        $this->checkoutSession = $checkoutSession;
        $this->checkoutMetadataProvider = $checkoutMetadataProvider;
        $this->isEnabledDeterminer = $isEnabledDeterminer ?: ObjectManager::getInstance()->get(IsEnabledDeterminer::class);
    }

    /**
     * @inheritDoc
     */
    public function getKlevuPageMeta()
    {
        $checkoutMetadata = [];
        try {
            $quote = $this->getQuote();
            if ($quote) {
                $checkoutMetadata = $this->checkoutMetadataProvider->getMetadataForCart($quote);
            }
        } catch (\Exception $e) {
            $this->_logger->error(
                sprintf("Unable to retrieve Checkout Metadata: %s", $e->getMessage()),
                ['originalException' => $e]
            );
        }

        return $this->serializer->serialize($checkoutMetadata);
    }

    /**
     * @return CartInterface|null
     */
    public function getQuote()
    {
        $return = null;
        try {
            $return = $this->checkoutSession->getQuote();
        } catch (NoSuchEntityException $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            // We don't need to worry about this
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage(), ['originalException' => $e]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    protected function _toHtml()
    {
        $quote = $this->getQuote();
        if (!$quote || !method_exists($quote, 'getAllItems') || !$quote->getAllItems()) {
            return '';
        }

        try {
            $store = $this->_storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage(), ['originalException' => $e]);

            return '';
        }

        /** @noinspection PhpCastIsUnnecessaryInspection */
        if (!$this->isEnabledDeterminer->execute((int)$store->getId())) {
            return '';
        }

        return parent::_toHtml();
    }
}
