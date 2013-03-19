<?php

namespace CodrPress;

use CodrPress\Exception\HttpException;

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

class HttpAuthDigest
{

    /**
     * Digest string from client
     * @var string
     */

    protected $_digestHash = null;

    /**
     * Parsed digest array
     * @var array
     */

    protected $_digest = null;

    /**
     * Realm name
     * @var string
     */

    protected $_realm = null;

    /**
     * Nonce
     * @var string
     */

    protected $_nonce = null;

    /**
     * Opaque
     * @var string
     */

    protected $_opaque = null;

    public function __construct(Request $request, $realm)
    {
        $digest = $request->server->get('PHP_AUTH_DIGEST');
        $httpAuth = $request->server->get('REDIRECT_HTTP_AUTHORIZATION');

        if (empty($digest) && !empty($httpAuth)) {
            $digest = $httpAuth;
        }

        $this->_digestHash = $digest;
        $this->_realm = $realm;

        $ip = $request->getClientIp();
        $opaque = sha1($realm . $request->server->get('HTTP_USER_AGENT') . $ip);

        $this->_nonce = sha1(uniqid($ip));
        $this->_opaque = $opaque;
    }

    protected function _parseDigest()
    {
        if (empty($this->_digestHash)) {
            throw new HttpException('Unauthorized', 401);
        }

        $necessaryParts = array(
            "nonce" => 1,
            "nc" => 1,
            "cnonce" => 1,
            "qop" => 1,
            "username" => 1,
            "uri" => 1,
            "response" => 1
        );

        $necessaryPart = implode("|", array_keys($necessaryParts));
        $digest = array();

        preg_match_all('@(' . $necessaryPart . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $this->_digestHash, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $digest[$match[1]] = $match[3] ? $match[3] : $match[4];
            unset($necessaryParts[$match[1]]);
        }

        if (!empty($necessaryParts)) {
            throw new HttpException('Bad Request', 400);
        }

        $this->_digest = $digest;
    }

    public function sendAuthenticationHeader($force = false)
    {
        if (empty($this->_digestHash) || $force === true) {
            $header = array(
                'WWW-Authenticate' => 'Digest realm="' . $this->_realm . '",nonce="' . $this->_nonce . '",qop="auth",opaque="' . $this->_opaque . '"'
            );

            return new Response('Please authenticate', 401, $header);
        }

        return null;
    }

    public function getUserName()
    {
        $this->_parseDigest();
        return $this->_digest['username'];
    }

    public function authenticate($token)
    {
        $this->_parseDigest();
        $a1 = $token; // md5("{$username}:{$realm}:{$password}")
        $a2 = md5("{$_SERVER['REQUEST_METHOD']}:{$this->_digest['uri']}");

        $aValidRepsonse = array(
            $a1,
            $this->_digest["nonce"],
            $this->_digest["nc"],
            $this->_digest["cnonce"],
            $this->_digest["qop"],
            $a2
        );

        $validRepsonse = md5(implode(':', $aValidRepsonse));

        if (($validRepsonse === $this->_digest["response"]) === false) {
            $e = new HttpException('Unauthorized', 401);
            $e->setCallingObject($this);
            throw $e;
        }
    }
}