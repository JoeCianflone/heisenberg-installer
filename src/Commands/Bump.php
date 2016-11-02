<?php
namespace HeisenbergInstaller\Commands;

use League\Flysystem\Filesystem;
use HeisenbergInstaller\Support\Mover;
use HeisenbergInstaller\Support\Cleaner;
use HeisenbergInstaller\Support\Extractor;
use HeisenbergInstaller\Support\Downloader;
use HeisenbergInstaller\Support\Dependencies;

class Bump extends Command {

   protected $signature = 'bump
                           {current=current : current version number}
                           {to=to : new version number}';

   protected $description = 'Bumps the version number of heisenberg up';

   private $filesystem;
   private $bumpList;

   public function __construct(Filesystem $filesystem)
   {
      $this->filesystem = $filesystem;
      $this->bumpList = [
         'readme.md',
         'package.json',
         '.heisenberg',
         'bower.json',
      ];
      parent::__construct();
   }

   public function handle()
   {
      foreach ($this->bumpList as $fileName) {
         $file = $this->filesystem->read($fileName);
         $updated = str_replace($this->argument('current'), $this->argument('to'), $file);
         $this->filesystem->update($fileName, $updated);
      }

   }
}
