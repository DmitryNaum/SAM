<?php

namespace Dmitrynaum\SAM\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Description of StartServer
 *
 * @author Naumov Dmitry <naym333@gmail.com>
 */
class StartServer extends Command
{

    protected function configure()
    {
        $this
            ->setName('start-server')
            ->setDescription('Starts a webserver to building assets in realtime (DEVELOPMENT ONLY!)')
            ->addArgument('manifest', null, 'Path to manifest file.', 'sam.json');
    }

    /**
     * Выполнить команду
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $pathToServerWorker = realpath(__DIR__ . '/../../bin/');
        $serverProcess      = $this->startWebServer($input, $output, $pathToServerWorker);

        while ($serverProcess->isRunning()) {
            sleep(1);
        }

        throw new \RuntimeException('The HTTP server has stopped');
    }

    /**
     * Запустить сервер
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $targetDirectory
     * @return \Symfony\Component\Process\Process
     */
    private function startWebServer(InputInterface $input, OutputInterface $output, $targetDirectory)
    {
        $manifestPath = getcwd() . '/' . $input->getArgument('manifest');
        
        $manifest = new \Dmitrynaum\SAM\Component\Manifest($manifestPath);
        $address  = $manifest->getServerAddress();
        
        $builder = new ProcessBuilder([PHP_BINARY, '-S', $address, 'server.php']);
        
        $builder->setWorkingDirectory($targetDirectory);
        $builder->setEnv('projectDir', getcwd());
        $builder->setEnv('manifestPath', $manifestPath);
        $builder->setTimeout(null);
        
        $process = $builder->getProcess();
        $process->start();
        $output->writeln(sprintf('Server running on <comment>%s</comment>', $address));
        
        return $process;
    }
}
