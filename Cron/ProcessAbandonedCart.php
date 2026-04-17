<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Cron;

use Hmh\AbandonCartPromotion\Model\AbandonedCartProcessor;
use Hmh\AbandonCartPromotion\Model\Config\ConfigProvider;

class ProcessAbandonedCart
{
    public function __construct(
        private readonly ConfigProvider $configProvider,
        private readonly AbandonedCartProcessor $abandonedCartProcessor
    ) {
    }

    public function execute(): void
    {
        if (!$this->configProvider->isEnabled()) {
            return;
        }

        $this->abandonedCartProcessor->process();
    }
}
