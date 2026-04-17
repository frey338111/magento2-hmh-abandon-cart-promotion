<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification\Strategy;

use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;
use Hmh\AbandonCartPromotion\Model\Notification\Service\EmailSender;

class EmailNotificationStrategy implements NotificationStrategyInterface
{
    public function __construct(
        private readonly EmailSender $emailSender
    ) {
    }

    public function notify(AbandonedCartNotificationData $notificationData): void
    {
        $this->emailSender->send($notificationData);
    }
}
