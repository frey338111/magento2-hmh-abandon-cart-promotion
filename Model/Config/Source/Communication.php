<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Communication implements OptionSourceInterface
{
    /**
     * @param array<int, array{value:string, label:string}> $options
     */
    public function __construct(
        private readonly array $options = []
    ) {
    }

    public function toOptionArray(): array
    {
        return array_map(
            static fn (array $option): array => [
                'value' => $option['value'],
                'label' => __($option['label']),
            ],
            $this->options
        );
    }
}
