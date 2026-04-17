<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory;

class CartPromotionRules implements OptionSourceInterface
{
    public function __construct(
        private readonly CollectionFactory $ruleCollectionFactory
    ) {
    }

    public function toOptionArray(): array
    {
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToSelect(['rule_id','description']);
        $collection->addFieldToFilter('name', ['like' => 'HMH_CART_PROMOTION%']);
        $collection->setOrder('rule_id', 'ASC');

        $options = [];

        foreach ($collection as $rule) {
            $options[] = [
                'value' => (int) $rule->getId(),
                'label' => (string) $rule->getDescription(),
            ];
        }

        return $options;
    }
}
