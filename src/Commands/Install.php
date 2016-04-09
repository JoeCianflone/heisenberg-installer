<?php
namespace HeisenbergInstaller\Commands;

use League\Flysystem\Filesystem;

class Install extends Command {

   protected $signature = 'install
                           {src=src : where to place your source files}
                           {dest=assets : where Heisenberg compiles files to}
                           {--force : force override on all download}
                           {--deps : install bower and npm components}
                           {--dev :  use developer release instead of stable}';

   protected $description = 'Install all necessary files to run Heisenberg';

   public $files;
   public $repo;

   public function __construct(Filesystem $files, )
   {
      $this->files = $files;
      $this->repo = "https://github.com/JoeCianflone/heisenberg-toolkit/archive/";

      parent::__construct();
   }

   public function handle()
   {

   }
}
