<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook;

use Silex\Application;

class OpenAddressBook
{
    /**
     * @var \Silex\Application
     */
    private $application;

    public function __construct($debug = false)
    {
        $this->application = new Application();

        if (true === $debug) {
            $this->application['debug'] = true;
        }

        $this->configure();
    }


    public function run()
    {
        $this->application->run();
    }


    private function configure()
    {
        $this->application['database'] = function() {
            return new Database();
        };

        $this->application['request'] = $this->application->factory(function ($app) {
            return $app['request_stack']->getCurrentRequest();
        });

        $dispatcher = new Dispatcher($this->application);
        $dispatcher->loadRoutes();

    }
}
