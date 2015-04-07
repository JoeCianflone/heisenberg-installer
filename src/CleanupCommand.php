<?php namespace Heisenberg\Installer\Console;

use RuntimeException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{

    /**
     * Console command configuration, this is where the command arguments
     * get set up and what the help menu pull from when it needs info for
     * about the commands
     *
     * @return null
     */
    protected function configure()
    {
        $this->setName('cleanup')
             ->setDescription('Removes any cruft left by the installer');
    }

    /**
     * This is the main entry point for the command. When you run it, this
     * is what it fires
     *
     * @param  Symfony\Component\Console\Input\InputInterface  $input
     * @param  Symfony\Component\Console\Input\OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem(new Adapter(getcwd().'/'));

        $output->writeln('<info>Checking for any leftover files...</info>');
        $currentFiles = $filesystem->listContents();
        $this->cleanUp($currentFiles, $filesystem);

        $output->writeln('<info>So fresh, so clean.</info>');
    }

    /**
     * Seeks out any temp folders that may have not been deleted due to
     * an error durning a prior install.
     *
     * @param League\Flysystem\Filesystem $currentFiles contents of the current directory
     *
     * @return $this
     */
    protected function cleanUp($currentFiles, $filesystem)
    {
        foreach ($currentFiles as $file) {
            if ($file["type"] === "dir" && strpos($file['path'], "mrwhite_") === 0) {
                $filesystem->deleteDir($file['path']);
            }
        }

        return $this;
    }
}
