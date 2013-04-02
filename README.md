# Introducing CodrPress

[![Build Status](https://secure.travis-ci.org/MadCatme/CodrPress.png)](http://travis-ci.org/MadCatme/CodrPress)

### A lightweight blogging system for software developers

#### Inspired by Schnitzelpress

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

#### Configure CodrPress

~~~ bash
$ cp config/codrpress.yml.dist config/codrpress.yml
~~~

Now change the MongoDB URI in the `codrpress.yml`

#### Set up a user and administration

Shame on me, that isn't finished yet.

### Common problems

- Check if the cache directory is writable for the web server user (please never use chmod 777!)

