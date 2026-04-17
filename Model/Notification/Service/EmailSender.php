<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification\Service;

use Hmh\AbandonCartPromotion\Model\Notification\AbandonedCartNotificationData;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class EmailSender
{
    private const EMAIL_TEMPLATE_ID = 'hmh_abandon_cart_promo_email_template';

    public function __construct(
        private readonly TransportBuilder $transportBuilder,
        private readonly StateInterface $inlineTranslation
    ) {
    }

    public function send(AbandonedCartNotificationData $notificationData): void
    {
        $customerEmail = $notificationData->getCustomerEmail();

        if ($customerEmail === '') {
            return;
        }

        $this->inlineTranslation->suspend();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier(self::EMAIL_TEMPLATE_ID)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $notificationData->getStoreId(),
                ])
                ->setTemplateVars([
                    'coupon_code' => $notificationData->getCouponCode(),
                    'quote_id' => $notificationData->getQuoteId(),
                ])
                ->setFromByScope('general', $notificationData->getStoreId())
                ->addTo($customerEmail)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
