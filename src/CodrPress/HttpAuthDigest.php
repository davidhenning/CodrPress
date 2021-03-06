<?php

namespace CodrPress;

use CodrPress\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HttpAuthDigest
{

    /**
     * Digest string from client
     * @var string
     */

    private $digestHash = null;

    /**
     * Parsed digest array
     * @var array
     */

    private $digest = null;

    /**
     * Realm name
     * @var string
     */

    private $realm = null;

    /**
     * Nonce
     * @var string
     */

    private $nonce = null;

    /**
     * Opaque
     * @var string
     */

    private $opaque = null;

    public function __construct(Request $request, $realm)
    {
        $digest = $request->server->get('PHP_AUTH_DIGEST');
        $httpAuth = $request->server->get('REDIRECT_HTTP_AUTHORIZATION');

        if (empty($digest) && !empty($httpAuth)) {
            $digest = $httpAuth;
        }

        $this->digestHash = $digest;
        $this->realm = $realm;

        $ip = $request->getClientIp();
        $opaque = sha1($realm . $request->server->get('HTTP_USER_AGENT') . $ip);

        $this->nonce = sha1(uniqid($ip));
        $this->opaque = $opaque;
    }

    private function parseDigest()
    {
        if (empty($this->digestHash)) {
            throw new HttpException('Unauthorized', 401);
        }

        $necessaryParts = [
            "nonce" => 1,
            "nc" => 1,
            "cnonce" => 1,
            "qop" => 1,
            "username" => 1,
            "uri" => 1,
            "response" => 1
        ];

        $necessaryPart = implode("|", array_keys($necessaryParts));
        $digest = [];

        preg_match_all('@(' . $necessaryPart . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $this->digestHash, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $digest[$match[1]] = $match[3] ? $match[3] : $match[4];
            unset($necessaryParts[$match[1]]);
        }

        if (!empty($necessaryParts)) {
            throw new HttpException('Bad Request', 400);
        }

        $this->digest = $digest;
    }

    public function sendAuthenticationHeader($force = false)
    {
        if (empty($this->digestHash) || $force === true) {
            $header = [
                'WWW-Authenticate' => 'Digest realm="' . $this->realm . '",nonce="' . $this->nonce . '",qop="auth",opaque="' . $this->opaque . '"'
            ];

            return new Response('Please authenticate', 401, $header);
        }

        return null;
    }

    public function getUserName()
    {
        $this->parseDigest();
        return $this->digest['username'];
    }

    public function authenticate($token)
    {
        $this->parseDigest();
        $a1 = $token; // md5("{$username}:{$realm}:{$password}")
        $a2 = md5("{$_SERVER['REQUEST_METHOD']}:{$this->digest['uri']}");

        $aValidRepsonse = [
            $a1,
            $this->digest["nonce"],
            $this->digest["nc"],
            $this->digest["cnonce"],
            $this->digest["qop"],
            $a2
        ];

        $validRepsonse = md5(implode(':', $aValidRepsonse));

        if (($validRepsonse === $this->digest["response"]) === false) {
            throw new HttpException('Unauthorized', 401);
        }
    }
}