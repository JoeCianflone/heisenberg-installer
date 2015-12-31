<?php
namespace Heisenberg\Installer\Console;

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
     * @var String
     */
    private $tempFolder;

    /**
     * @var League\Flysystem\Filesystem;
     */
    private $filesystem;

    /**
     * @var ZipArchive
     */
    private $tempZipFile;

    /**
     * @var Array
     */
    private $copyFiles = [
        'folders' => [
            "src/js",
            "src/sass",
            "src/images",
            "src/fonts"
        ],
        'files' => [
            ".bowerrc",
            ".editorconfig",
            "bower.json",
            "gulpfile.js",
            "package.json",
            ".gitignore"
        ]
    ];

    /**
     * Console command configuration, this is where the command arguments
     * get set up and what the help menu pull from when it needs info for
     * about the commands
     *
     * @return null
     */
    protected function configure()
    {
        $this->setName('install')
             ->setDescription('Installs heisenberg to the current directory.')
             ->addOption("src", "source files", InputOption::VALUE_OPTIONAL, "where to place src files")
             ->addOption("dest", "compiled src files", InputOption::VALUE_OPTIONAL, "where heisenberg compiles files to")
             ->addOption("deps", null, InputOption::VALUE_NONE, "If set this will install bower and npm components");
    }

    /**
     * This is the main entry point for the command. When you run it, this
     * is what it fires
     *
     * @param  Symfony\Component\Console\Input\InputInterface  $input
     * @param  Symfony\Component\Console\Input\OutputInterface $output
     *
     * @return null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem(new Adapter(getcwd().'/'));
        $srcLocation      = is_null($input->getOption("src")) ? "src" : $input->getOption("src");
        $destLocation     = is_null($input->getOption("dest")) ? "assets" : $input->getOption("dest");
        $output->writeln('<info>Installing Heisenberg...</info>');
        $this->makeFilename()
             ->download()
             ->extract()
             ->move($srcLocation, $destLocation)
             ->cleanUp()
             ->installDeps($input->getOption('deps'));

        $output->writeln('<info>Say. My. Name.</info>');
    }

    /**
     * Creates the temp directories we're going to need for the
     * rest of the process
     *
     * @return $this
     */
    protected function makeFilename()
    {
        $this->tempFolder = 'mrwhite_'.md5(time().uniqid());
        $this->filesystem->createDir($this->tempFolder);
        $this->tempZipFile = $this->tempFolder . '/heisenberg_'.md5(time().uniqid()) . ".zip";

        return $this;
    }

    /**
     * Here we download the current master branch zip file and place
     * it into the $tempFolder
     *
     * @return $this
     */
    protected function download()
    {
        $response = (new Client)->get('https://github.com/JoeCianflone/heisenberg-toolkit/archive/master.zip');
        $this->filesystem->write($this->tempZipFile, $response->getBody());

        return $this;
    }

    /**
     * Takes the Zip we just downloaded and extracts it so we can
     * work with the contents
     *
     * @return $this
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
     * We've pulled down all the necessary files, now it's time to
     * move them into the correct places
     * @param  String $srcLocation  where you want the files currently in the heisenberg/src folder to go defaults to src
     * @param  String $destLocation where you want the files currently in heisenberg/assets folder to go defaults to assets
     *
     * @return $this
     */
    protected function move($srcLocation, $destLocation)
    {
        $downloadedFilePath = $this->tempFolder . "/heisenberg-toolkit-master/";

        $this->moveFiles($downloadedFilePath, $srcLocation, $destLocation);
        $this->moveFolders($downloadedFilePath, $srcLocation, $destLocation);

        return $this;
    }

    /**
     * Does the actual moving of files
     * @param  String $downloadedFilePath location of the extracted zip files
     * @param  String $srcLocationPath    path to where you want the files currently in the extracted zip folder to live
     *
     * @return $this
     */
    private function moveFiles($downloadedFilePath, $srcLocation, $destLocation)
    {
        foreach ($this->copyFiles['files'] as $file) {
            $oldFilePath = $downloadedFilePath.$file;
            $newFilePath = $file;
            $this->removeOldFiles($newFilePath);

            // TODO: Is this the best way to do this? Probably not.
            $content = $this->filesystem->read($oldFilePath);
            $content = str_replace("src/", "{SRC}" ."/", $content);
            $content = str_replace("assets/", "{DEST}" ."/", $content);
            $content = str_replace("{SRC}", $srcLocation, $content);
            $content = str_replace("{DEST}", $destLocation, $content);

            $this->filesystem->put($newFilePath, $content);
        }

        return $this;
    }

    /**
     * Does the actual moving of files in folders
     * @param  String $downloadedFilePath location of the extracted zip files
     * @param  String $srcLocation   path to where you want the files currently in the extracted zip folder to live
     *
     * @return $this
     */
    private function moveFolders($downloadedFilePath, $srcLocation)
    {
        foreach ($this->copyFiles['folders'] as $folder) {
            $path = $downloadedFilePath.$folder;
            $contents = $this->filesystem->listContents($path, true);

            $this->copyFolderContents($contents, $srcLocation, $downloadedFilePath."src/");
        }

        return $this;
    }

    /**
     * Copies the files out of the folder and into the new location
     * @param  [type] $contents           [description]
     * @param  [type] $srcLocation        [description]
     * @param  [type] $downloadedFilePath [description]
     *
     * @return $this
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

        return $this;
    }

    /**
     * Checks if a particular file exists in the new location and deletes
     * that file.
     *
     * @param  String $existingFile file we're checking on in the new location
     *
     * @return $this
     */
    private function removeOldFiles($existingFile)
    {
        if ($this->filesystem->has($existingFile)) {
            $this->filesystem->delete($existingFile);
        }

        return $this;
    }

    /**
     * Deletes the tempFolder and all of its contents.
     *
     * @return $this
     */
    protected function cleanUp()
    {
        $this->filesystem->deleteDir($this->tempFolder);

        return $this;
    }

    protected function installDeps($installDeps)
    {
        if ($installDeps) {
            echo "NPM \n";
            exec("npm install");
            echo "Bower \n";
            exec("bower install");
        }

        return $this;
    }
}
