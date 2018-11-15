<?php

namespace App\Services\Auth;

use App\Services\Auth\Parser;
use App\Services\Auth\Factory;
use App\Services\Auth\Contracts\JwtSubject;
use App\Services\Auth\Providers\Auth\AuthProviderInterface;

class JwtAuth
{
    protected $auth;

    protected $factory;

    protected $parser;

    protected $user = null;

    public function __construct(AuthProviderInterface $auth, Factory $factory, Parser $parser)
    {
        $this->auth = $auth;
        $this->factory = $factory;
        $this->parser = $parser;
    }

    public function attempt($username, $password)
    {
        if (!$user = $this->auth->byCredentials($username, $password)) {
            return false;
        }

        return $this->fromSubject($user);
    }

    public function authenticate($token)
    {
        $this->user = $this->auth->byId(
            $this->parser->decode($token)->sub
        );

        return $this;
    }

    public function user()
    {
        return $this->user;
    }

    protected function fromSubject(JwtSubject $subject)
    {
        return $this->factory->encode($this->makePayload($subject));
    }

    protected function makePayload(JwtSubject $subject)
    {
        return $this->factory->withClaims($this->getClaimsForSubject($subject))->make();
    }

    protected function getClaimsForSubject(JwtSubject $subject)
    {
        return [
            'sub' => $subject->getJwtIdentifier()
        ];
    }
}
