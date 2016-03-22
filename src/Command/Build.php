<?php

namespace Dmitrynaum\SAM\Command;

use Dmitrynaum\SAM\AssetBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Команда для сборки (компиляции) asset`ов
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class Build extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Build assets')
            ->addArgument(
                'manifest',
                null,
                'Path to manifest file.',
                './sam.json'
            )
            ->addOption(
                'minify',
                'm',
                InputOption::VALUE_NONE,
                'Minify assets'
            )
            ->addOption(
                'freeze',
                'f',
                InputOption::VALUE_NONE,
                'Freeze assets'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manifestFilePath = $input->getArgument('manifest');
        
        $builder = new AssetBuilder($manifestFilePath);

        if ($input->getOption('minify')) {
            $builder->enableCompressor();
        }
        
        if ($input->getOption('freeze')) {
            $builder->enableFreezing();
        }

        $builder->build();
        $output->writeln('Assets succesfuly builded');
    }
}
