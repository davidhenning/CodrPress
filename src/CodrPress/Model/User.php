<?php

namespace CodrPress\Model;

use Mango\DocumentInterface,
    Mango\Document;

class User implements DocumentInterface
{
    use Document;

    private function addFields()
    {
        $this->addField(
            'created_at',
            [
                'type' => 'DateTime',
                'index' => true,
                'default' => 'now'
            ]
        );

        $this->addField(
            'updated_at',
            [
                'type' => 'DateTime',
                'index' => true,
                'default' => 'now'
            ]
        );

        $this->addField(
            'name',
            [
                'index' => true
            ]
        );

        $this->addField(
            'email',
            [
                'index' => true
            ]
        );

        $this->addField(
            'digest_hash',
            [
                'index' => true
            ]
        );
    }

    public function setDigestHash($username, $realm, $password)
    {
        $this->digest_hash = md5("{$username}:{$realm}:{$password}");
    }
}