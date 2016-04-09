<?php
namespace HeisenbergInstaller\Commands;


use Symfony\Component\Process\Process;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Cleanup extends Command {

   protected $signature = 'cleanup';

   protected $description = 'Remove any installer cruft';

   public function __construct()
   {
      parent::__construct();
   }

   public function handle()
   {

   }
}
