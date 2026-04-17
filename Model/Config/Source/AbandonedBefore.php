<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AbandonedBefore implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        $options = [];

        foreach (range(0, 48,6) as $hour) {
            $options[] = [
                'value' => $hour,
                'label' => (string) $hour,
            ];
        }

        return $options;
    }
}
