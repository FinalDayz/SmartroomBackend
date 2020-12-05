<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthenticationSubscriber implements EventSubscriberInterface
{
    public function onKernelController(ControllerEvent $event)
    {
        $keys = str_getcsv($_SERVER['AUTHENTICATION_KEYS']);
        $controller = $event->getController();


        if (is_array($controller)) {
            $controller = $controller[0];
        }

        $key = $event->getRequest()->headers->get('Authorization');
        if (!in_array($key, $keys)) {
            throw new AccessDeniedHttpException('This action needs a valid token! You gave: "'.$key.'"');
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}