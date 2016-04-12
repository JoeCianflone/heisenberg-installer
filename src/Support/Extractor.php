<?php

namespace HeisenbergInstaller\Support;

use ZipArchive;

class Extractor {

   public static function get($zipFile, $location)
   {
      $archive = new ZipArchive;
      $archive->open($zipFile);
      $archive->extractTo($location);
      $archive->close();
   }

}
