<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
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
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if($request->isMethod("OPTIONS")) {
            $event->stopPropagation();
        }

        $key = $event->getRequest()->headers->get('Authorization');

        if (!in_array($key, $keys) && !$request->isMethod("OPTIONS")) {
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

    public function onRequest(RequestEvent $event) {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        if($event->getRequest()->isMethod("OPTIONS")) {
            $event->setResponse(
                new Response('', 204, [
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Credentials' => 'true',
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'DNT, X-User-Token, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type',
                    'Access-Control-Max-Age' => 1728000,
                    'Content-Type' => 'text/plain charset=UTF-8',
                    'Content-Length' => 0
                ])
            );
        }

        $event->stopPropagation();

    }

    public function responseController(ResponseEvent $event)
    {
//        var_dump("Henlo");
//        var_dump($event->getRequest()->getMethod());
//        $event->getResponse()->headers->set('Access-Control-Allow-Origin', '*');
//        if ($event->getRequest()->getMethod() === 'OPTIONS') {
//            $event->setResponse(
//                new Response('', 204, [
//                    'Access-Control-Allow-Origin' => '*',
//                    'Access-Control-Allow-Credentials' => 'true',
//                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
//                    'Access-Control-Allow-Headers' => 'DNT, X-User-Token, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type',
//                    'Access-Control-Max-Age' => 1728000,
//                    'Content-Type' => 'text/plain charset=UTF-8',
//                    'Content-Length' => 0
//                ])
//            );
//            return;
//        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::RESPONSE => 'responseController'
        ];
    }
}