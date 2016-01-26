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
    private $repoPath = 'https://github.com/JoeCianflone/heisenberg-toolkit/archive/master.zip';
    private $tempFolder;
    private $tempZipFile;

    private $folders = [
        'src',
        'gulp',
    ];

    private $files = [
        '.bowerrc',
        '.editorconfig',
        'bower.json',
        'gulpfile.js',
        'package.json',
        '.gitignore',
        'gulp/config.js',
        'heisenberg.html',
    ];

    private $srcPath;
    private $compiledPath;

    protected function configure()
    {
        $this->setName('install')
             ->setDescription('Installs heisenberg to the current directory.')
             ->addOption("src", "source files", InputOption::VALUE_OPTIONAL, "where to place src files")
             ->addOption("dest", "compiled src files", InputOption::VALUE_OPTIONAL, "where heisenberg compiles files to")
             ->addOption("deps", null, InputOption::VALUE_NONE, "If set this will install bower and npm components");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem    = new Filesystem(new Adapter(getcwd().'/'));
        $this->srcPath       = $input->getOption("src")  ?: "src";
        $this->compiledPath  = $input->getOption("dest") ?: "assets";
        $this->tempFolder    = 'mrwhite_'.md5(time().uniqid());
        $this->tempZipFile   = $this->tempFolder . '/heisenberg_'.md5(time().uniqid()) . ".zip";


        $output->writeln('<info>Installing Heisenberg...</info>');
        $this->download()
             ->extract()
             ->move()
             ->cleanup()
             ->dependencies($input->getOption('deps'), $output);

        $output->writeln('<info>Heisenberg Installed. Now...</info>');
        $output->writeln('<info>Say. My. Name.</info>');
    }

    protected function download()
    {
        $response = (new Client)->get($this->repoPath);

        $this->filesystem->createDir($this->tempFolder);
        $this->filesystem->write($this->tempZipFile, $response->getBody());

        return $this;
    }

    protected function extract()
    {
        $archive = new ZipArchive;

        $archive->open($this->tempZipFile);
        $archive->extractTo($this->tempFolder);
        $archive->close();

        return $this;
    }

    protected function move()
    {
        $dlPath = $this->tempFolder . "/heisenberg-toolkit-master/";
        $this->moveFolders($dlPath);
        $this->moveFiles($dlPath);

        return $this;
    }

    private function moveFolders($dlPath)
    {
        foreach ($this->folders as $folder) {
            $list = $this->filesystem->listContents($dlPath.$folder, true);
            $this->copyFiles($list, $dlPath);
        }
    }

    private function moveFiles($dlPath)
    {
        foreach ($this->files as $file) {
            $fileContent = $this->filesystem->read($dlPath.$file);
            $fileContent = $this->updatePaths($fileContent);

            $this->filesystem->put($file, $fileContent);
        }
    }

    private function updatePaths($content)
    {
        $content = str_replace('src/', $this->srcPath.'/', $content);
        $content = str_replace('assets/', $this->compiledPath.'/', $content);

        return $content;
    }
    private function copyFiles($list, $dlPath) {
        foreach ($list as $file) {
            if ($file['type'] === 'file') {
                $newPath = str_replace($dlPath, "", $file['path']);
                $newPath = str_replace("src", $this->srcPath, $newPath);
                $this->filesystem->copy($file['path'], $newPath);
            }
        }
    }

    protected function cleanup()
    {
        $this->filesystem->deleteDir($this->tempFolder);
        return $this;
    }

    protected function dependencies($installDeps, $output)
    {
        if ($installDeps) {
            $output->writeln("<info>Attempting to install dependencies, this will take a moment...</info>");
            try {
                $this->processCLI("npm install");
                $this->processCLI("bower install");
                $this->processCLI("gulp compile");
            } catch(ProcessFailedException $e) {
                $output->writeln("<error>Error ProcessFailedException".$e->getMessage()."</error>");
            }
        }
        return $this;
    }

    private function processCLI($cmd)
    {
        $process = new Process($cmd);
        $process->setTimeout(3600);
        $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}

