<?php

namespace HeisenbergInstaller\Support;

class Cleaner {

   public static function clean($filesystem)
   {

      $list = array_filter($filesystem->listContents("./", false), function($file) {
         return (0 === strpos($file['path'], 'mrwhite_'));
      });

      foreach ($list as $item) {
         $filesystem->deleteDir($item['path']);
      }
   }

}
