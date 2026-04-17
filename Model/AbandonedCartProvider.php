<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model;

use Hmh\AbandonCartPromotion\Model\Config\ConfigProvider;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

class AbandonedCartProvider
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly CollectionFactory $quoteCollectionFactory,
        private readonly DateTime $dateTime
    ) {
    }

    public function getList(): Collection
    {
        $abandonedBefore = $this->configProvider->getAbandonedBefore();
        $discountThreshold = $this->configProvider->getDiscountThreshold();
        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $fromThreshold = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            $currentTimestamp - ($abandonedBefore * 3600)
        );
        $toThreshold = $this->dateTime->gmtDate(
            'Y-m-d H:i:s',
            strtotime($fromThreshold . ' -1 day')
        );

        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToSelect(['entity_id', 'customer_id', 'customer_email', 'store_id', 'created_at']);
        $quoteCollection->addFieldToFilter('is_active', 1);
        $quoteCollection->addFieldToFilter('customer_is_guest', 0);
        $quoteCollection->addFieldToFilter('customer_id', ['notnull' => true]);
        $quoteCollection->addFieldToFilter('items_count', ['gt' => 0]);
        $quoteCollection->addFieldToFilter('reserved_order_id', ['null' => true]);
        $quoteCollection->addFieldToFilter('created_at', ['gt' => $toThreshold]);
        $quoteCollection->addFieldToFilter('created_at', ['lteq' => $fromThreshold]);
        if ($discountThreshold !== null) {
            $quoteCollection->addFieldToFilter('subtotal', ['gt' => $discountThreshold]);
        }

        return $quoteCollection;
    }
}
