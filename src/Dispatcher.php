<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook;

use OpenAddressBook\Controller\AddressBook;
use OpenAddressBook\Controller\Click2Call;
use Silex\Application;

class Dispatcher
{
    /**
     * @var \Silex\Application
     */
    private $application;

    private $version = 'v1';

    public function __construct(Application $application)
    {
        $this->application = $application;
    }


    public function loadRoutes()
    {
        $this->loadShow();
        $this->loadSave();
        $this->loadDelete();
        $this->loadCall();
    }


    private function loadShow()
    {
        $this->application->get('/api/'.$this->version.'/address-books.json', function (Application $application) {
            $controller = new AddressBook($application);

            return $controller->getAll();
        });

        $this->application
            ->get('/api/'.$this->version.'/address-books/{id}.json', function (Application $application, $id) {
                $controller = new AddressBook($application);

                return $controller->get($id);
            })
            ->convert('id', function ($id) { return (int) $id; })
            ->assert('id', '\d+')
        ;
    }


    private function loadSave()
    {
        $this->application->post('/api/'.$this->version.'/address-books.json', function (Application $application) {
            $controller = new AddressBook($application);

            return $controller->save($application['request']);
        });

        $this->application
            ->post('/api/'.$this->version.'/address-books/{id}.json', function (Application $application, $id) {
                $controller = new AddressBook($application);

                return $controller->save($application['request'], $id);
            })
            ->convert('id', function ($id) { return (int) $id; })
            ->assert('id', '\d+')
        ;
    }


    private function loadDelete()
    {
        $this->application
            ->delete('/api/'.$this->version.'/address-books/{id}.json', function (Application $application, $id) {
                $controller = new AddressBook($application);

                return $controller->delete($id);
            })
            ->convert('id', function ($id) { return (int) $id; })
            ->assert('id', '\d+')
        ;
    }


    private function loadCall()
    {
        $this->application
            ->get('/api/'.$this->version.'/click2call.json', function (Application $application) {
                $controller = new Click2Call($application);

                return $controller->getAll();
            })
        ;

        $this->application
            ->get('/api/'.$this->version.'/click2call/call/{name}/{phone}', function (Application $application, $name, $phone) {
                $controller = new Click2Call($application);

                return $controller->call($name, $phone);
            })
        ;
    }
}