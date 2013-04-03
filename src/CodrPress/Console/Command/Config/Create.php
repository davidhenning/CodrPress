<?php

namespace CodrPress\Console\Command\Config;

use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

class Create extends Command
{
    private $options = [
        [
            'name' => 'codrpress.db.mongo.uri',
            'question' => 'Enter MongoDB URI'
        ],
        [
            'name' => 'codrpress.auth.digest.realm',
            'question' => 'Enter realm id for HTTP digest auth'
        ],
        [
            'name' => 'codrpress.debug',
            'question' => 'Debug mode enabled?',
            'type' => 'boolean'
        ],
        [
            'name' => 'codrpress.layout.posts_per_page',
            'question' => 'How many posts per page'
        ],
        [
            'name' => 'codrpress.http.use_cache',
            'question' => 'Enable HTTP cache header?',
            'type' => 'boolean'
        ],
        [
            'name' => 'codrpress.http.cache_ttl',
            'question' => 'Enter the HTTP cache header lifetime in seconds'
        ],
        [
            'name' => 'codrpress.info.blog_title',
            'question' => 'Enter the name of your blog'
        ],
        [
            'name' => 'codrpress.info.author_name',
            'question' => 'Enter your name for author information in posts and feeds'
        ],
    ];

    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Creates or edits the CodrPress config file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (file_exists('config/codrpress.yml')) {
            $defaultValues = Yaml::parse('config/codrpress.yml', true);
        }

        if (!is_writeable('config/codrpress.yml') && !is_writeable('config/')) {
            $output->writeln('Config directory or config file is not writeable. Please check the file permissions!');
            exit();
        }

        $dialog = $this->getHelperSet()->get('dialog');
        $config = [];

        foreach ($this->options as $option) {
            $name = $option['name'];
            $default = (isset($defaultValues[$name])) ? $defaultValues[$name] : null;

            $value = $dialog->ask(
                $output,
                "{$option['question']} [{$default}]: ",
                $default,
                [$default]
            );

            if (isset($option['type']) && $option['type'] === 'boolean') {
                $value = (bool)$value;
            }

            $config[$name] = $value;
        }

        if ($dialog->askConfirmation($output, 'Do you really want to write the new config file? [yes] ')) {
            $dumper = new Dumper();
            $yaml = $dumper->dump($config, 1);
            file_put_contents('config/codrpress.yml', $yaml);
            $output->writeln('<info>New config file was written to "config/codrpress.yml".</info>');
        }
    }
}