<?php
namespace HeisenbergInstaller\Commands;


use Symfony\Component\Process\Process;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Update extends Command {

   protected $signature = 'update
                           {--force : force override on all download}
                           {--deps : install bower and npm components}
                           {--dev :  use developer release instead of stable}';

   protected $description = 'Update to the latest version of Heisenberg';

   public function __construct()
   {
      parent::__construct();
   }

   public function handle()
   {
   }
}
