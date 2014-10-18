<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Controller;

use OpenAddressBook\Click2Call\Click2CallInterface;
use Silex\Application;
use Symfony\Component\Yaml\Parser;

class Click2Call extends Controller
{
    /**
     * Return all available caller
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAll()
    {
        $parameters = $this->getParametersFromConfig();
        if (is_array($parameters) === false || isset($parameters['directory']) === false) {
            return $this->render404('directory');
        }

        ksort($parameters['directory']);

        return $this->render($parameters['directory']);
    }


    public function call($name, $phone)
    {
        $parameters = $this->getParametersFromConfig();

        if (false === $parameters || isset($parameters['class']) === false) {
            return $this->renderError('parameters must be defined');
        }

        $api_call = new $parameters['class']();
        if (($api_call instanceof Click2CallInterface) === false) {
            return $this->renderError('click2call class must be an instance of Click2CallInterface');
        }
        
        $result = $api_call
            ->setParameters($parameters)
            ->call($phone, $name);

        if (false === $result) {
            return $this->renderError($api_call->getError());
        }

        return $this->render(array('result' => true));
    }


    /**
     * @return array|bool
     */
    private function getParametersFromConfig()
    {
        static $parameters = null;

        if (null === $parameters) {
            $filename = __DIR__.'/../../config/click2call.yml';
            if (is_file($filename) === false) {
                return false;
            }

            $yaml = new Parser();
            $parameters = $yaml->parse(file_get_contents($filename));

            if (isset($parameters['click2call']) === false) {
                return false;
            }

            return $parameters['click2call'];
        }

        return $parameters['click2call'];
    }
}
