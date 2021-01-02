<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
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

        $request = $event->getRequest();

        $key = $event->getRequest()->headers->get('Authorization');
        if (!in_array($key, $keys)) {
            if(!$this->check_legacy($request, $keys)) {
                throw new AccessDeniedHttpException('This action needs a valid token! You gave: "'.$key.'"');
            }
        }
    }

    private function check_legacy(Request $request, $keys):bool {
        if(in_array($request->get('key'), $keys)) {
            return true;
        }
        if($request->getMethod() == 'POST') {
            $body = json_decode($request->getContent(), true);
            if($body !== null && in_array($body['key'], $keys)) {
                return true;
            }
        }

        return false;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}