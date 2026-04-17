<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\MessageQueue;

use Hmh\AbandonCartPromotion\Model\Config\ConfigProvider;
use Hmh\AbandonCartPromotion\Model\Coupon\CouponCodeGenerator;
use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;
use Hmh\AbandonCartPromotion\Model\Notification\NotificationDispatcher;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Psr\Log\LoggerInterface;

class AbandonCartPromoConsumer
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly RuleRepositoryInterface $ruleRepository,
        private readonly CouponCodeGenerator $couponCodeGenerator,
        private readonly NotificationDispatcher $notificationDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    public function process(string $message): void
    {
        $payload = json_decode($message, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            $this->logger->error(
                'Abandoned cart promotion message payload is invalid.',
                [
                    'message' => $message,
                    'json_error' => json_last_error_msg(),
                ]
            );
            return;
        }

        $customerId = (int) ($payload['customer_id'] ?? 0);
        $storeId = (int) ($payload['store_id'] ?? 0);
        $quoteId = (int) ($payload['quote_id'] ?? 0);
        $discountType = $this->configProvider->getDiscountType($storeId > 0 ? $storeId : null);

        if ($customerId <= 0 || $storeId <= 0 || $quoteId <= 0 || $discountType === null) {
            $this->logger->error(
                'Abandoned cart promotion message is missing required data.',
                [
                    'payload' => $payload,
                    'discount_type' => $discountType,
                ]
            );
            return;
        }

        try {
            $rule = $this->ruleRepository->getById($discountType);
            $couponCode = $this->couponCodeGenerator->generate((int) $rule->getRuleId());
            $notificationData = new AbandonedCartNotificationData(
                $customerId,
                $storeId,
                $quoteId,
                (string) ($payload['customer_email'] ?? ''),
                $couponCode
            );

            $this->notificationDispatcher->dispatch($notificationData);
        } catch (\Throwable $exception) {
            $this->logger->error(
                'Failed to process abandoned cart promotion message.',
                [
                    'payload' => $payload,
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }
}
