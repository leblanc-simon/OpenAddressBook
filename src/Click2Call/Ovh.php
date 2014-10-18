<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Click2Call;

/**
 * Class Ovh
 * @package OpenAddressBook\Click2Call
 */
class Ovh implements Click2CallInterface
{
    /**
     * @var \SoapClient
     */
    private $soap;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $nic_handle;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $calling_external;

    /**
     * @var string
     */
    private $calling_internal;


    /**
     * @inheritdoc
     */
    public function __construct()
    {
        $this->soap = new \SoapClient("https://www.ovh.com/soapi/soapi-re-1.16.wsdl");
    }


    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }


    /**
     * @inheritdoc
     */
    public function call($number_to_call, $caller)
    {
        try {
            $this->getInformations($caller);

            $this->soap->telephonyClick2CallDo(
                $this->nic_handle,
                $this->password,
                $this->calling_external,
                $number_to_call,
                $this->calling_internal
            );
        } catch (\InvalidArgumentException $e) {
            $this->error = $e->getMessage();
            return false;
        } catch (\SoapFault $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return true;
    }


    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * Initialize the configuration (nic-handle, password, caller numbers)
     *
     * @param $caller
     * @return $this
     */
    private function getInformations($caller)
    {
        $this->getConfigApi();
        $this->getConfigByName($caller);

        return $this;
    }


    /**
     *
     *
     * @throws \InvalidArgumentException
     */
    private function getConfigApi()
    {
        if (
            is_array($this->parameters) === false
            || isset($this->parameters['parameters']) === false
            || is_array($this->parameters['parameters']) === false
        ) {
            throw new \InvalidArgumentException('parameters is required');
        }

        $requires = array(
            'nic_handle',
            'password',
        );

        foreach ($requires as $require) {
            if (isset($this->parameters['parameters'][$require]) === false) {
                throw new \InvalidArgumentException($require.' is required');
            }
        }

        $this->nic_handle = $this->parameters['parameters']['nic_handle'];
        $this->password = $this->parameters['parameters']['password'];
    }


    /**
     * @param $name
     * @throws \InvalidArgumentException
     */
    private function getConfigByName($name)
    {
        if (
            is_array($this->parameters) === false
            || isset($this->parameters['directory']) === false
            || isset($this->parameters['directory'][$name]) === false
        ) {
            throw new \InvalidArgumentException('directory is required');
        }

        $requires = array(
            'external',
            'internal',
        );

        foreach ($requires as $require) {
            if (isset($this->parameters['directory'][$name][$require]) === false) {
                throw new \InvalidArgumentException($require.' is required');
            }
        }

        $this->calling_external = $this->parameters['directory'][$name]['external'];
        $this->calling_internal = $this->parameters['directory'][$name]['internal'];
    }
}