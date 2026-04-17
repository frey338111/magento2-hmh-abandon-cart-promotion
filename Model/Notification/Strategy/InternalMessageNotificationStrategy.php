<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification\Strategy;

use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;
use Hmh\AbandonCartPromotion\Model\Notification\Service\InternalMessageSender;

class InternalMessageNotificationStrategy implements NotificationStrategyInterface
{
    public function __construct(
        private readonly InternalMessageSender $internalMessageSender
    ) {
    }

    public function notify(AbandonedCartNotificationData $notificationData): void
    {
        $this->internalMessageSender->send($notificationData);
    }
}
