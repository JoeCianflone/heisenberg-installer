<?php
namespace Heisenberg\Installer\Console;

use RuntimeException;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupCommand extends Command
{
    protected function configure()
    {
      $this->setName('cleanup')
           ->setDescription('Removes any cruft left by the installer');
    }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
      $filesystem = new Filesystem(new Adapter(getcwd().'/'));

      $output->writeln('<info>Checking for any leftover files...</info>');
      $currentFiles = $filesystem->listContents();
      $this->cleanUp($currentFiles, $filesystem);

      $output->writeln('<info>So fresh, so clean.</info>');
   }

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
