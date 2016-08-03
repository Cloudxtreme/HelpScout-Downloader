<?php

declare(strict_types=1);

/*
 * This file is part of HelpScout Downloader.
 *
 * (c) Graham Campbell <graham@alt-three.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GrahamCampbell\HelpScout;

use Bcn\Component\Json\Writer;
use Symfony\Component\Console\Command\Command as Base;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is the download command class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class Command extends Base
{
    /**
     * Configures the command.
     *
     * This method is called by the parent's constructor.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('download');
        $this->setDescription('Downloads a mailbox');

        $this->addArgument('key', InputArgument::REQUIRED, 'The api key');
        $this->addArgument('mailbox', InputArgument::REQUIRED, 'The mailbox');
        $this->addArgument('output', InputArgument::REQUIRED, 'The output file');
    }

    /**
     * Executes the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client($input->getArgument('key'));

        $writer = new Writer($file = fopen($input->getArgument('output'), 'w'));
        $writer->enter(Writer::TYPE_OBJECT);
        $writer->enter('conversations', Writer::TYPE_ARRAY);

        try {
            foreach ($client->conversations($input->getArgument('mailbox')) as $conversation) {
                if (!isset($progress)) {
                    $progress = new ProgressBar($output, $conversation['count']);
                    $progress->start();
                }

                $writer->write(null, $conversation['data']);
                $progress->advance();
            }
        } finally {
            $writer->leave();
            $writer->leave();
            fclose($file);
        }

        $progress->finish();

        $output->writeln('');
    }
}
