<?php

namespace HeisenbergInstaller\Support;

use ZipArchive;
use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Downloader
{
    private $repo = "https://github.com/JoeCianflone/heisenberg-toolkit/archive/master.zip";
    private $tempFolder;

    public function __construct()
    {
        $this->tempFolder = 'mrwhite_'.md5(time().uniqid());
    }

    public function getPackage($isDev, $filesystem)
    {
        if ($isDev) {
            $this->repo = str_replace("master", "develop", $this->repo);
        }

        return $this->savePackage((new Client)->get($this->repo), $filesystem);
    }

    private function savePackage($response, $filesystem)
    {
        $zipFile = $this->tempFolder . '/heisenberg_'.md5(time().uniqid()) . ".zip";
        $filesystem->createDir($this->tempFolder);
        $filesystem->write($zipFile, $response->getBody());

        return $zipFile;
    }

    public function getTempFolder()
    {
        return $this->tempFolder;
    }
}
