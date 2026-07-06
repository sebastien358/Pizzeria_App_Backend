<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\Cookie;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $response = $event->getResponse();

        $data = $event->getData();
        $token = $data['token'];

        $response->headers->setCookie(
            Cookie::create('token')
                ->withValue($token)
                ->withHttpOnly(true)
                ->withSecure(false) // true en prod
                ->withSameSite('strict')
                ->withPath('/')
        );
    }
}
