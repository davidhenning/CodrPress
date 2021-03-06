# Introducing CodrPress

[![Build Status](https://secure.travis-ci.org/MadCatme/CodrPress.png)](http://travis-ci.org/MadCatme/CodrPress)

### A lightweight blogging system for software developers

#### Inspired by Schnitzelpress

**CodrPress is still in an early development phase.**

CodrPress is build on top of [Silex](http://silex.sensiolabs.org/) and [Mango](https://github.com/WebCodr/Mango).

Write your blog posts in Markdown. Thanks to [Amplifyr](http://amplifyr.herokuapp.com/) CodrPress features a beautiful syntax highlighting for most languages.

The default design is based on Twitter Bootstrap and fully responsive. The templates are written HAML and easily customizeable. Don't like HAML? You can also use Twig.

### Requirements

- PHP 5.4
- MongoDB 2.*
- MongoDB driver for PHP (min. 1.2.0)
- Composer

### Setup

#### Clone the repository

~~~ bash
$ git clone git@github.com:MadCatme/CodrPress.git
~~~

#### Install the dependencies with Composer

~~~ bash
$ cd CodrPress
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar install
~~~

If you want to run the unit tests, please install the developer dependencies:

~~~ bash
$ php composer.phar install --dev
~~~

#### Configure CodrPress

~~~ bash
$ php console.php config
~~~

#### Create a user

~~~ bash
$ php console.php user:create <username> <mail address>
~~~

#### Administration

Goto `http://yourdomain.com/admin/posts` in your browser and login with the user you just created.

To create a post, enter a publish date. Leave it blank if you want to create a page.

The publish date can parse any [date format supported by PHP](http://www.php.net/manual/en/datetime.formats.php), f.e. `now`, `yesterday`, `+1 day`, `Monday next week` and many more.

At the moment you can not delete a post. For now just set the status to `draft` to hide a post.

#### Local testing without a web server

~~~ bash
php -S localhost:1337 -t .
~~~

Open `http://localhost:1337` in your browser.

### Common problems

- Check if the cache directory is writable for the web server user (please never use chmod 777!)

