<?php

namespace CodrPress\Console\Command\Config;

use Silex\Application;

use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
        ]
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
        if (file_exists('conf/codrpress.yml')) {
            $defaultValues = Yaml::parse('conf/codrpress.yml', true);
        }

        if (!is_writeable('conf/codrpress.yml') && !is_writeable('conf/')) {
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
            file_put_contents('conf/codrpress.yml', $yaml);
            $output->writeln('<info>New config file was written to "conf/codrpress.yml".</info>');
        }
    }
}