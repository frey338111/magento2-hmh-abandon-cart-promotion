<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CartAge implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        foreach (range(1, 30) as $day) {
            $options[] = [
                'value' => $day,
                'label' => (string) $day,
            ];
        }

        return $options;
    }
}
