<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification\Strategy;

use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;

interface NotificationStrategyInterface
{
    public function notify(AbandonedCartNotificationData $notificationData): void;
}
