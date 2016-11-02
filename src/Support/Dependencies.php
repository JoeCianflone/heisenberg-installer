<?php

namespace HeisenbergInstaller\Support;

use Symfony\Component\Process\Process;
use HeisenbergInstaller\Exceptions\ProcessFailed;

class Dependencies {

   public static function load()
   {
      try {
         self::processCLI("yarn install");
         self::processCLI("gulp");
      } catch(ProcessFailed $e) {
         echo $e->getMessage();
      }
   }

   private static function processCLI($cmd)
   {
      $process = new Process($cmd);
      $process->setTimeout(3600);
      $process->run(function ($type, $buffer) {
         echo $buffer;
      });

      if (! $process->isSuccessful()) {
         throw new ProcessFailed($process);
      }
   }
}
