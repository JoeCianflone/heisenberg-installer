<?php namespace Heisenberg\Installer\Console;

use ZipArchive;
use RuntimeException;
use GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class NewProjectCommand extends Command
{

    private $tempFolder;
    private $tempZipFile;
    private $filesystem;

    protected function configure()
    {
        $this->setName('new')
             ->setDescription('Say. My. Name.')
             ->addOption("src", null, InputOption::VALUE_NONE, "where to place src files");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->filesystem = new Filesystem();

        $output->writeln('<info>Say. My. Name.</info>');

        $this->makeFilename()
             ->download()
             ->extract()
             ->move()
             ->cleanUp();


        $output->writeln('<comment>Heisenberg Ready.</comment>');
    }

    protected function move()
    {
        $d = $this->tempFolder . "/heisenberg-toolkit-master/";
        $tempSrc = [
            "src/js",
            "src/sass",
        ];
        $tempFiles = [
            ".bowerrc",
            ".editorconfig",
            "bower.json",
            "gulpfile.js",
            "package.json"
        ];

        foreach ($tempFiles as $file) {
            $this->filesystem->copy($d.$file, getcwd()."/".$file, true);
        }

        foreach ($tempSrc as $folder) {
            $this->filesystem->mirror($d.$folder, getcwd()."/hberg-assets/".$folder);
        }

        return $this;
    }

    protected function makeFilename()
    {
        $this->tempFolder = getcwd().'/mrwhite_'.md5(time().uniqid());
        $this->filesystem->mkdir($this->tempFolder);

        $this->tempZipFile = $this->tempFolder . '/heisenberg_'.md5(time().uniqid()) . ".zip";

        return $this;
    }

    protected function download()
    {
        $response = (new Client)->get('https://github.com/JoeCianflone/heisenberg-toolkit/archive/master.zip');

        file_put_contents($this->tempZipFile, $response->getBody());

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

    protected function cleanUp()
    {
        $this->filesystem->remove($this->tempFolder);

        return $this;
    }
}
