<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Coupon;

use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponInterface;
use Magento\SalesRule\Model\CouponFactory;

class CouponCodeGenerator
{
    private const COUPON_PREFIX = 'OFFER';
    private const COUPON_SEGMENT_LENGTH = 4;
    private const COUPON_SEGMENT_COUNT = 3;
    private const MAX_COUPON_GENERATION_ATTEMPTS = 5;

    public function __construct(
        private readonly CouponFactory $couponFactory,
        private readonly CouponRepositoryInterface $couponRepository
    ) {
    }

    public function generate(int $ruleId): string
    {
        for ($attempt = 0; $attempt < self::MAX_COUPON_GENERATION_ATTEMPTS; $attempt++) {
            $couponCode = $this->generateCouponCode();
            $coupon = $this->couponFactory->create();
            $coupon->setRuleId($ruleId);
            $coupon->setCode($couponCode);
            $coupon->setUsageLimit(1);
            $coupon->setUsagePerCustomer(1);
            $coupon->setType(CouponInterface::TYPE_GENERATED);
            $coupon->setIsPrimary(false);

            try {
                $this->couponRepository->save($coupon);
                return $couponCode;
            } catch (LocalizedException $exception) {
                if ($attempt === self::MAX_COUPON_GENERATION_ATTEMPTS - 1) {
                    throw $exception;
                }
            }
        }

        throw new LocalizedException(__('Unable to generate abandoned cart coupon code.'));
    }

    private function generateCouponCode(): string
    {
        $segments = [];

        for ($index = 0; $index < self::COUPON_SEGMENT_COUNT; $index++) {
            $segments[] = strtoupper(substr(bin2hex(random_bytes(self::COUPON_SEGMENT_LENGTH)), 0, self::COUPON_SEGMENT_LENGTH));
        }

        return self::COUPON_PREFIX . '-' . implode('-', $segments);
    }
}
