<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Controller;

use Silex\Application;

abstract class Controller
{
    /**
     * @var \Silex\Application
     */
    protected $application;

    /**
     * @var \OpenAddressBook\Database
     */
    protected $database;


    public function __construct(Application $application)
    {
        $this->application = $application;
        $this->database = $application['database'];
    }


    protected function render($datas)
    {
        return $this->application->json($datas);
    }


    protected function renderError($message, $code = 500)
    {
        return $this->application->json(array('error' => true, 'message' => $message), $code);
    }


    protected function render404($object)
    {
        return $this->renderError($object.' don\'t exist', 404);
    }
}