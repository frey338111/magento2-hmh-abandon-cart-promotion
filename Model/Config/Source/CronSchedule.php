<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CronSchedule implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => '0 23 * * *',
                'label' => __('11:00 PM'),
            ],
            [
                'value' => '0 0 * * *',
                'label' => __('12:00 AM'),
            ],
            [
                'value' => '0 1 * * *',
                'label' => __('1:00 AM'),
            ],
            [
                'value' => '0 2 * * *',
                'label' => __('2:00 AM'),
            ],
            [
                'value' => '0 3 * * *',
                'label' => __('3:00 AM'),
            ],
            [
                'value' => '0 4 * * *',
                'label' => __('4:00 AM'),
            ],
            [
                'value' => '0 5 * * *',
                'label' => __('5:00 AM'),
            ],
        ];
    }
}
