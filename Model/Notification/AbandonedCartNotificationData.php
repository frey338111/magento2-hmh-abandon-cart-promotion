<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Notification;

class AbandonedCartNotificationData
{
    public function __construct(
        private readonly int $customerId,
        private readonly int $storeId,
        private readonly int $quoteId,
        private readonly string $customerEmail,
        private readonly string $couponCode
    ) {
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getStoreId(): int
    {
        return $this->storeId;
    }

    public function getQuoteId(): int
    {
        return $this->quoteId;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCouponCode(): string
    {
        return $this->couponCode;
    }
}
