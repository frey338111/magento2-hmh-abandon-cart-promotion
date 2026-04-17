<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model;

use Hmh\AbandonCartPromotion\Model\Config\ConfigProvider;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class AbandonedCartProcessor
{
    private const TOPIC_ABANDON_CART_PROMO = 'hmh.abandon.cart.promo';

    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly AbandonedCartProvider $abandonedCartProvider,
        private readonly PublisherInterface $publisher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(): void
    {
        $quoteCollection = $this->abandonedCartProvider->getList();
        $cartCount = $quoteCollection->getSize();
        $publishedCount = 0;
        $skippedCount = 0;

        $this->logger->info(
            'Starting abandoned cart promotion processing.',
            [
                'abandoned_before' => $this->configProvider->getAbandonedBefore(),
                'cart_count' => $cartCount,
                'topic' => self::TOPIC_ABANDON_CART_PROMO,
            ]
        );

        /** @var Quote $quote */
        foreach ($quoteCollection as $quote) {
            $customerEmail = (string) $quote->getCustomerEmail();

            if ($customerEmail === '') {
                $skippedCount++;
                $this->logger->warning(
                    'Skipping abandoned cart promotion message because customer email is missing.',
                    [
                        'quote_id' => (int) $quote->getId(),
                        'customer_id' => (int) $quote->getCustomerId(),
                        'store_id' => (int) $quote->getStoreId(),
                    ]
                );
                continue;
            }

            $message = [
                'quote_id' => (int) $quote->getId(),
                'customer_id' => (int) $quote->getCustomerId(),
                'customer_email' => $customerEmail,
                'store_id' => (int) $quote->getStoreId(),
            ];

            $this->logger->info(
                'Publishing abandoned cart promotion message.',
                $message
            );

            try {
                $this->publisher->publish(
                    self::TOPIC_ABANDON_CART_PROMO,
                    json_encode($message, JSON_THROW_ON_ERROR)
                );
                $publishedCount++;
                $this->logger->info(
                    'Published abandoned cart promotion message.',
                    $message
                );
            } catch (\Throwable $exception) {
                $this->logger->error(
                    'Failed to publish abandoned cart promotion message.',
                    [
                        'quote_id' => (int) $quote->getId(),
                        'customer_email' => $customerEmail,
                        'exception' => $exception->getMessage(),
                    ]
                );
            }
        }

        $this->logger->info(
            'Hmh_AbandonCartPromotion abandoned cart check completed.',
            [
                'abandoned_before' => $this->configProvider->getAbandonedBefore(),
                'cart_count' => $cartCount,
                'published_count' => $publishedCount,
                'skipped_count' => $skippedCount,
            ]
        );
    }
}
