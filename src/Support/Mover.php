<?php

namespace HeisenbergInstaller\Support;

use League\Flysystem\Filesystem;
use SebastianBergmann\Diff\Differ;

class Mover {

   public $downloadPath;
   public $filesystem;
   public $diffedFiles;

   public function __construct(Filesystem $filesystem, $tempFolder, $src, $dest)
   {
      $this->filesystem = $filesystem;
      $this->tempFolder = $tempFolder;
      $this->diffedFiles = [];
      $this->src = $src;
      $this->dest = $dest;
   }

   public function files($isDev, $useForce)
   {
      $dlPath = $this->getExtractedPath($isDev);

      $list = array_filter($this->filesystem->listContents($dlPath, true), function($file)  {
         return $file['type'] === 'file';
      });

      $this->move($list, $dlPath, $useForce);

      return $this->diffedFiles;
   }

   private function getExtractedPath($isDev)
   {
      if ($isDev) {
         return $this->tempFolder . "/heisenberg-toolkit-develop/";
      }

      return $this->tempFolder . "/heisenberg-toolkit-master/";
   }

   private function move($fileList, $dlPath, $useForce)
   {
      foreach ($fileList as $file) {
         $path = str_replace($dlPath, "", str_replace("src", $this->src, $file['path']));
         $rawContent = $this->filesystem->read($file['path']);
         $newContent = $this->updateContent($rawContent);

         if ($this->filesystem->has($path)) {
            $existingContent = $this->filesystem->read($path);
            $this->updateExistingFile($path, $existingContent, $newContent, $useForce);
         } else {
            $this->filesystem->write($path, $newContent);
         }
      }
   }

   private function updateContent($file)
   {
      return str_replace("{{src}}", $this->src, str_replace("{{dest}}", $this->dest, $file));
   }

   private function updateExistingFile($path, $oldContent, $newContent, $force)
   {
      if ($force || $path == ".heisenberg") {
         $this->filesystem->update($path, $newContent);
      } else {
         $this->diffFiles($path, $oldContent, $newContent);
      }

   }

   // Fast and probably good enough...*crosses fingers*
   private function compareFiles($fileA, $fileB)
   {
      if (sha1($fileA) === sha1($fileB)) {
         return true;
      }

      return false;
   }

   private function diffFiles($path, $from, $to)
   {
      if (! $this->compareFiles($from, $to)) {
         $differ = new Differ;
         $this->filesystem->put($path, $differ->diff($from, $to));
         $this->diffedFiles[] = $path;
      } else {
         $this->filesystem->put($path, $to);
      }
   }
}
