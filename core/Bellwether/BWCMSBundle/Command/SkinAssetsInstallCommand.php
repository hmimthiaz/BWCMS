<?php

namespace Bellwether\BWCMSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Bellwether\BWCMSBundle\Classes\Service\TemplateService;

class SkinAssetsInstallCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('BWCMS:SkinAssetInstall')
            ->setDescription('Installs all the public assets of CMS skins to Public')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'web'),
            ))
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs all the public
assets of CMS skins to public directory (e.g. the <comment>web</comment> directory).

  <info>php %command.full_name% web</info>

A "skins" directory will be created inside the target directory and the
"{Bundle}/Skins/{SkinFolder}/Public" directory of each bundle will be copied into it.

This will install all the assets as symlinks only.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        $filesystem = $this->getContainer()->get('filesystem');

        // Create the skins directory otherwise symlink will fail.
        $skinsPublicDir = $targetArg . DIRECTORY_SEPARATOR . 'skins' . DIRECTORY_SEPARATOR;
        $filesystem->mkdir($skinsPublicDir, 0777);

        /**
         * @var TemplateService $templateService ;
         */
        $templateService = $this->getContainer()->get('bwcms.template');
        $templateService->init();
        $skins = $templateService->getSkins();

        if (empty($skins)) {
            return;
        }

        foreach ($skins as $skinFolder => $skinnName) {
            $skinClass = $templateService->getSkinClass($skinFolder);
            $sourceDir = $skinClass->getPath() . DIRECTORY_SEPARATOR . 'Public';
            $targetDir = $skinsPublicDir . strtolower($skinClass->getFolderName());

            if (file_exists($sourceDir)) {
                if($this->isWindows()){
                    $output->writeln(sprintf('Installing assets copy for skin : %s -> %s', $skinClass->getName(), $targetDir));
                    $this->hardCopy($sourceDir, $targetDir);
                }else{
                    $output->writeln(sprintf('Installing assets symlink for skin : %s -> %s', $skinClass->getName(), $targetDir));
                    $filesystem->remove($targetDir);
                    $filesystem->symlink($sourceDir, $targetDir);
                    if (!file_exists($targetDir)) {
                        throw new IOException('Symbolic link is broken');
                    }
                }
            }
        }
    }

    private function hardCopy($originDir, $targetDir)
    {
        $filesystem = $this->getContainer()->get('filesystem');

        $filesystem->mkdir($targetDir, 0777);
        // We use a custom iterator to ignore VCS files
        $filesystem->mirror($originDir, $targetDir, Finder::create()->ignoreDotFiles(false)->in($originDir));
    }

    function isWindows(){
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }
        return false;
    }
}