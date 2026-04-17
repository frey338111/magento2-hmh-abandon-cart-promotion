<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification\Service;

use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;
use Hmh\InternalMessage\Api\InternalMessageManagementInterface;
use Hmh\InternalMessage\Model\Data\InternalMessageDtoFactory;
use Magento\Framework\Escaper;

class InternalMessageSender
{
    public function __construct(
        private readonly InternalMessageManagementInterface $internalMessageManagement,
        private readonly InternalMessageDtoFactory $internalMessageDtoFactory,
        private readonly Escaper $escaper
    ) {
    }

    public function send(AbandonedCartNotificationData $notificationData): void
    {
        $messageDto = $this->internalMessageDtoFactory->create();
        $messageDto->setData([
            'title' => (string) __('You left something great behind — it’s still waiting in your basket.'),
            'message_content' => sprintf(
                '<p>%s</p>',
                $this->escaper->escapeHtml((string) __(
                    'Complete your checkout today and enjoy a little saving with code %1.',
                    $notificationData->getCouponCode()
                ))
            ),
            'customer_id' => $notificationData->getCustomerId(),
            'store_id' => $notificationData->getStoreId(),
        ]);

        $this->internalMessageManagement->createMessage($messageDto);
    }
}
