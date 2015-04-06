<?php namespace Heisenberg\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use League\Flysystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use League\Flysystem\Adapter\Local as Adapter;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * [$files description]
     * @var [type]
     */
    private $files;

    /**
     * [$tempFolder description]
     * @var [type]
     */
    private $tempFolder;

    /**
     * [$filesystem description]
     * @var [type]
     */
    private $filesystem;

    /**
     * [$tempZipFile description]
     * @var [type]
     */
    private $tempZipFile;

    /**
     * [$copyFiles description]
     * @var [type]
     */
    private $copyFiles = [
        'folders' => [
            "src/js",
            "src/sass"
        ],
        'files' => [
            ".bowerrc",
            ".editorconfig",
            "bower.json",
            "gulpfile.js",
            "package.json"
        ]
    ];

    /**
     * [configure description]
     * @return [type] [description]
     */
    protected function configure()
    {
        $this->setName('install')
             ->setDescription('Installs heisenberg to the current directory.')
             ->addOption("src", "source files", InputOption::VALUE_OPTIONAL, "where to place src files")
             ->addOption("assets", "public assets", InputOption::VALUE_OPTIONAL, "where heisenberg compiles files to");
    }

    /**
     * [execute description]
     * @param  InputInterface  $input  [description]
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem(new Adapter(getcwd().'/'));
        $srcLocation      = is_null($input->getOption("src")) ? "src" : $input->getOption("src");
        $assetsLocation   = is_null($input->getOption("src")) ? "assets" : $input->getOption("assets");

        $output->writeln('<info>Installing Heisenberg...</info>');
        $this->makeFilename()
             ->download()
             ->extract()
             ->move($srcLocation, $assetsLocation)
             ->cleanUp();

        $output->writeln('<info>Say. My. Name.</info>');
    }

    /**
     * [makeFilename description]
     * @return [type] [description]
     */
    protected function makeFilename()
    {
        $this->tempFolder = 'mrwhite_'.md5(time().uniqid());
        $this->filesystem->createDir($this->tempFolder);
        $this->tempZipFile = $this->tempFolder . '/heisenberg_'.md5(time().uniqid()) . ".zip";

        return $this;
    }

    /**
     * [download description]
     * @return [type] [description]
     */
    protected function download()
    {
        $response = (new Client)->get('https://github.com/JoeCianflone/heisenberg-toolkit/archive/master.zip');
        $this->filesystem->write($this->tempZipFile, $response->getBody());

        return $this;
    }

    /**
     * [extract description]
     * @return [type] [description]
     */
    protected function extract()
    {
        $archive = new ZipArchive;

        $archive->open($this->tempZipFile);
        $archive->extractTo($this->tempFolder);
        $archive->close();

        return $this;
    }

    /**
     * [move description]
     * @param  [type] $srcLocation    [description]
     * @param  [type] $assetsLocation [description]
     * @return [type]                 [description]
     */
    protected function move($srcLocation, $assetsLocation)
    {
        $downloadedFilePath = $this->tempFolder . "/heisenberg-toolkit-master/";

        $this->moveFiles($downloadedFilePath);
        $this->moveFolders($downloadedFilePath, $srcLocation, $assetsLocation);

        return $this;
    }

    /**
     * [moveFiles description]
     * @param  [type] $downloadedFilePath [description]
     * @return [type]                     [description]
     */
    private function moveFiles($downloadedFilePath)
    {
        foreach ($this->copyFiles['files'] as $file) {
            $oldFilePath = $downloadedFilePath.$file;
            $newFilePath = $file;
            $this->removeOldFiles($newFilePath);

            $this->filesystem->copy($oldFilePath, $newFilePath);
        }

        return $this;
    }

    /**
     * [moveFolders description]
     * @param  [type] $downloadedFilePath [description]
     * @param  [type] $srcLocation        [description]
     * @param  [type] $assetsLocation     [description]
     * @return [type]                     [description]
     */
    private function moveFolders($downloadedFilePath, $srcLocation, $assetsLocation)
    {
        foreach ($this->copyFiles['folders'] as $folder) {
            $path = $downloadedFilePath.$folder;
            $contents = $this->filesystem->listContents($path, true);

            $this->copyFolderContents($contents, $srcLocation, $downloadedFilePath."src/");
        }

        return $this;
    }

    /**
     * [copyFolderContents description]
     * @param  [type] $contents           [description]
     * @param  [type] $srcLocation        [description]
     * @param  [type] $downloadedFilePath [description]
     * @return [type]                     [description]
     */
    private function copyFolderContents($contents, $srcLocation, $downloadedFilePath)
    {
        foreach ($contents as $file) {
            if ($file['type'] === "file") {
                $newFile = $srcLocation."/".str_replace($downloadedFilePath, "", $file['path']);
                $this->removeOldFiles($newFile);
                $this->filesystem->copy($file['path'], $newFile);
            }
        }
    }

    /**
     * [removeOldFiles description]
     * @param  [type] $existingFile [description]
     * @return [type]               [description]
     */
    private function removeOldFiles($existingFile)
    {
        if ($this->filesystem->has($existingFile)) {
            $this->filesystem->delete($existingFile);
        }

        return $this;
    }

    /**
     * [cleanUp description]
     * @return [type] [description]
     */
    protected function cleanUp()
    {
        $this->filesystem->deleteDir($this->tempFolder);

        return $this;
    }
}
