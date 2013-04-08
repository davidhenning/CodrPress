<?php

namespace CodrPress\Console\Command\Post;

use CodrPress\Model\Post;
use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Render extends Command
{
    private $app;

    public function __construct(Application $app, $name = null)
    {
        $this->app = $app;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('post:render')
            ->setDescription('Re-render Markdown source in all posts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');

        foreach (Post::posts() as $document) {
            if ($verbose === true) {
                $output->writeln("<info>Rendering post: {$document->title}</info>");
            }

            $document->store();
        }

        $output->writeln('<info>Rendering complete.</info>');
    }
}