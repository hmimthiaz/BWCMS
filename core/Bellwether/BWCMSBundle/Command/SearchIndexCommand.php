<?php

namespace Bellwether\BWCMSBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Bellwether\BWCMSBundle\Classes\Service\SearchService;

class SearchIndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('BWCMS:SearchIndex')
            ->setDescription('Command to work with search index.')
            ->addOption('drop', null, InputOption::VALUE_NONE, 'Drops the search index');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('BWCMS.KernelEventListener')->init();
        if ($input->getOption('drop')) {
            $this->search()->dropSearchIndex();
            return;
        }
        $this->search()->runIndex();
    }

    /**
     * @return SearchService
     */
    public function search()
    {
        return $this->getContainer()->get('BWCMS.Search')->getManager();
    }
}