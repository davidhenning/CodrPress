<?php

namespace CodrPress\Console\Command\User;

use CodrPress\Model\User;
use Silex\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class Create extends Command
{
    private $app = array();


    public function __construct(Application $app, $name = null)
    {
        $this->app = $app;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Creates a new CodrPress user')
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'username'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'mail address'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get arguments and options
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $realm = $this->app['config']->get('codrpress.auth.digest.realm');

        $dialog = $this->getHelperSet()->get('dialog');
        $password = $dialog->askHiddenResponse(
            $output,
            'password:'
        );

        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->setDigestHash($username, $realm, $password);
        $user->store();

        $output->writeln('<info>User created!</info>');
    }
}