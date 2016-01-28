<?php

namespace Bellwether\BWCMSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Bellwether\BWCMSBundle\Classes\Service\SearchService;

class S3UploadCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('BWCMS:S3Upload')
            ->setDescription('Command to work with search index.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


    }

    /**
     * @return SearchService
     */
    public function search()
    {
        return $this->getContainer()->get('BWCMS.Search')->getManager();
    }
}