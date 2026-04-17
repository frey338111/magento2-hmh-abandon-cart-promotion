<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model;

use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Quote\Model\Quote;
use Psr\Log\LoggerInterface;

class AbandonedCartProcessor
{
    private const TOPIC_ABANDON_CART_PROMO = 'hmh.abandon.cart.promo';

    public function __construct(
        private readonly AbandonedCartProvider $abandonedCartProvider,
        private readonly PublisherInterface $publisher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(): void
    {
        $quoteCollection = $this->abandonedCartProvider->getList();

        /** @var Quote $quote */
        foreach ($quoteCollection as $quote) {
            $customerEmail = (string) $quote->getCustomerEmail();

            if ($customerEmail === '') {
                continue;
            }

            $message = [
                'quote_id' => (int) $quote->getId(),
                'customer_id' => (int) $quote->getCustomerId(),
                'customer_email' => $customerEmail,
                'store_id' => (int) $quote->getStoreId(),
            ];

            try {
                $this->publisher->publish(
                    self::TOPIC_ABANDON_CART_PROMO,
                    json_encode($message, JSON_THROW_ON_ERROR)
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
    }
}
