<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider
{
    public const XML_PATH_ENABLED = 'hmh_abandoncartpromotion/general/enabled';
    public const XML_PATH_ABANDONED_BEFORE = 'hmh_abandoncartpromotion/general/abandoned_before';
    public const XML_PATH_CRON_SCHEDULE = 'hmh_abandoncartpromotion/general/cron_schedule';
    public const XML_PATH_METHOD = 'hmh_abandoncartpromotion/communication/method';
    public const XML_PATH_DISCOUNT_TYPE = 'hmh_abandoncartpromotion/discount/type';
    public const XML_PATH_DISCOUNT_THRESHOLD = 'hmh_abandoncartpromotion/discount/threshold';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAbandonedBefore(?int $storeId = null): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_ABANDONED_BEFORE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCronSchedule(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_CRON_SCHEDULE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMethod(?int $storeId = null): array
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_METHOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === '') {
            return [];
        }

        return explode(',', $value);
    }

    public function getDiscountType(?int $storeId = null): ?int
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_DISCOUNT_TYPE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function getDiscountThreshold(?int $storeId = null): ?float
    {
        $value = $this->scopeConfig->getValue(
            self::XML_PATH_DISCOUNT_THRESHOLD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
