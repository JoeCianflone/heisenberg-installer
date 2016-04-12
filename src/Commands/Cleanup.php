<?php

namespace HeisenbergInstaller\Commands;

use League\Flysystem\Filesystem;
use HeisenbergInstaller\Support\Cleaner;

class Cleanup extends Command {

   protected $signature = 'cleanup';

   protected $description = 'Clears out any leftover files';

   private $filesystem;

   public function __construct(Filesystem $filesystem)
   {
      $this->filesystem = $filesystem;

      parent::__construct();
   }

   public function handle()
   {
      $this->info("Cleaning up...");
      Cleaner::clean($this->filesystem);
      $this->info("All Clean");
   }
}
