<?php

declare(strict_types=1);

namespace Hmh\AbandonCartPromotion\Console\Command;

use Hmh\AbandonCartPromotion\Model\AbandonedCartProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessAbandonedCartCommand extends Command
{
    public function __construct(
        private readonly AbandonedCartProcessor $abandonedCartProcessor
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('hmh:abandon-cart:process');
        $this->setDescription('Process abandoned cart promotion messages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->abandonedCartProcessor->process();
        $output->writeln('<info>Abandoned cart processor completed.</info>');

        return Command::SUCCESS;
    }
}
