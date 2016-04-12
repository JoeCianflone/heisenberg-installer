<?php
namespace HeisenbergInstaller\Commands;

use League\Flysystem\Filesystem;
use HeisenbergInstaller\Support\Mover;
use HeisenbergInstaller\Support\Cleaner;
use HeisenbergInstaller\Support\Extractor;
use HeisenbergInstaller\Support\Downloader;
use HeisenbergInstaller\Support\Dependencies;

class Install extends Command {

   protected $signature = 'install
                           {src=src : where to place your source files}
                           {dest=assets : where Heisenberg compiles files to}
                           {--force : force override on all download}
                           {--deps : install bower and npm components}
                           {--dev :  use developer release instead of stable}';

   protected $description = 'Install all necessary files to run Heisenberg';

   private $filesystem;
   private $download;
   private $move;

   public function __construct(Filesystem $filesystem)
   {
      $this->filesystem = $filesystem;
      $this->download = new Downloader();

      parent::__construct();
   }

   public function handle()
   {
      $this->info("Downloading & Extracting Files...");
      $package = $this->download->getPackage($this->option('dev'), $this->filesystem);
      Extractor::get($package, $this->download->getTempFolder());

      $this->info("Moving files into place...");
      $move = new Mover($this->filesystem, $this->download->getTempFolder(), $this->argument("src"), $this->argument("dest"));
      $move->files($this->option('dev'), $this->option('force'));

      $this->info("Cleanup...");
      Cleaner::clean($this->filesystem);

      if ($this->option("deps")) {
         $this->info("Update Dependencies, this may take a bit (or just not work) because NPM is terrible");
         Dependencies::load();
      }

      $this->info("All done, now");
      $this->info("Say. My. Name.");
   }
}
