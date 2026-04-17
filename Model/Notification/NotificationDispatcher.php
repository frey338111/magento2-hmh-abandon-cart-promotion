<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification;

use Hmh\AbandonCartPromotion\Model\Config\ConfigProvider;
use Hmh\AbandonCartPromotion\Model\Notification\Strategy\NotificationStrategyInterface;
use Psr\Log\LoggerInterface;

class NotificationDispatcher
{
    /**
     * @param array<string, NotificationStrategyInterface> $notificationStrategies
     */
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly LoggerInterface $logger,
        private readonly array $notificationStrategies
    ) {
    }

    public function dispatch(AbandonedCartNotificationData $notificationData): void
    {
        $notificationTypes = $this->configProvider->getMethod($notificationData->getStoreId());

        foreach ($this->resolveStrategies($notificationTypes) as $strategyCode => $strategy) {
            try {
                $strategy->notify($notificationData);
            } catch (\Throwable $exception) {
                $this->logger->error(
                    'Failed to execute abandoned cart notification strategy.',
                    [
                        'strategy_code' => $strategyCode,
                        'customer_id' => $notificationData->getCustomerId(),
                        'store_id' => $notificationData->getStoreId(),
                        'quote_id' => $notificationData->getQuoteId(),
                        'exception' => $exception->getMessage(),
                    ]
                );
            }
        }
    }

    /**
     * @param string[] $notificationTypes
     * @return array<string, NotificationStrategyInterface>
     */
    private function resolveStrategies(array $notificationTypes): array
    {
        $strategies = [];

        foreach ($notificationTypes as $notificationType) {
            if (
                !isset($this->notificationStrategies[$notificationType])
                || !$this->notificationStrategies[$notificationType] instanceof NotificationStrategyInterface
            ) {
                continue;
            }

            $strategies[$notificationType] = $this->notificationStrategies[$notificationType];
        }

        return $strategies;
    }
}
