<?php
namespace HeisenbergInstaller\Commands;

use GuzzleHttp\Client;
use League\Flysystem\Filesystem;
use HeisenbergInstaller\Support\Mover;
use HeisenbergInstaller\Support\Cleaner;
use HeisenbergInstaller\Support\Extractor;
use HeisenbergInstaller\Support\Downloader;
use HeisenbergInstaller\Support\Dependencies;


class Update extends Command {

   protected $signature = 'update
                           {--force : force override on all download}
                           {--deps : install bower and npm components}
                           {--dev :  use developer release instead of stable}';

   protected $description = 'Update to the latest version of Heisenberg';

   private $filesystem;
   private $download;
   private $move;

   public function __construct(Filesystem $filesystem)
   {
      $this->filesystem = $filesystem;
      $this->download = new Downloader();

      parent::__construct();
   }

   // TODO: this is pretty much identical to the install
   // the only difference here is we check the version
   // strings and we set the src and dest path differently
   // this function needs to be refactored out of here
   public function handle()
   {
      $client = new Client();
      $heisenbergInfo = json_decode($this->filesystem->read(".heisenberg"), true);
      $response = $client->get("https://api.github.com/repos/joecianflone/heisenberg-toolkit/releases/latest");
      $release = $response->json();

      if (version_compare($release['tag_name'], $heisenbergInfo['version'],'>')) {

         $this->info("Downloading & Extracting Files...");
         $package = $this->download->getPackage($this->option('dev'), $this->filesystem);
         Extractor::get($package, $this->download->getTempFolder());

         $this->info("Moving files into place...");
         $move = new Mover($this->filesystem, $this->download->getTempFolder(), $heisenbergInfo['path']['src'], $heisenbergInfo['path']['dest']);
         $move->files($this->option('dev'), $this->option('force'));

         $this->info("Cleanup...");
         Cleaner::clean($this->filesystem);

         if ($this->option("deps")) {
            $this->info("Update Dependencies, this may take a bit (or just not work) because NPM is terrible");
            Dependencies::load();
         }

         $this->info("All done, now");
         $this->info("Say. My. Name.");
      } else {
         $this->info("You're already running the latest");
      }
   }
}
