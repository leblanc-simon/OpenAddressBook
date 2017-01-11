<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector\Odoo;

use OpenAddressBook\Connector\ConnectorInterface;
use OpenAddressBook\Connector\ItemInterface;
use OpenErpByJsonRpc\Client\Model;
use OpenErpByJsonRpc\Criteria;
use OpenErpByJsonRpc\JsonRpc\OpenERP;
use OpenErpByJsonRpc\JsonRpc\ZendJsonRpc;
use OpenErpByJsonRpc\Storage\NullStorage;
use Symfony\Component\Yaml\Parser;

class Connector implements ConnectorInterface
{
    /**
     * @var string
     */
    private $settings_file;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['settings_file']) === false || is_string($options['settings_file']) === false) {
            throw new \RuntimeException('settings_file is required and must be a string');
        }

        $this->setSettingsFile($options['settings_file']);
    }

    /**
     * Return the list of items to import
     *
     * @return ItemInterface[]
     */
    public function getItems()
    {
        $model = $this->getOdooModel();

        $criteria = new Criteria();
        $criteria->equal('customer', 1);
        $criteria->equal('is_company', false);

        $results = $model->search(
            'res.partner',
            $criteria,
            [
                'id',
                'function',
                'parent_id',
                'mobile',
                'email',
                'phone',
                'name',
            ]
        );

        $items = [];
        foreach ($results as $result) {
            $items[] = new Item($result);
        }

        return $items;
    }

    /**
     * @param string $settings_file
     */
    private function setSettingsFile($settings_file)
    {
        if (file_exists($settings_file) === true) {
            $this->settings_file = $settings_file;
            return;
        }

        $filename = dirname(__DIR__).'/../../config/'.$settings_file;
        if (file_exists($filename) === true) {
            $this->settings_file = $filename;
            return;
        }

        throw new \InvalidArgumentException(sprintf(
            '%s doesn\'t exist',
            $settings_file
        ));
    }

    /**
     * Return the object to manipulate model Odoo
     *
     * @return Model
     */
    private function getOdooModel()
    {
        $parameters = $this->getSettings();
        $json_rpc = new ZendJsonRpc($parameters['server']);
        $odoo = new OpenERP($json_rpc, new NullStorage([]));

        $odoo
            ->setBaseUri($parameters['server'])
            ->setDatabase($parameters['database'])
            ->setUsername($parameters['username'])
            ->setPassword($parameters['password'])
        ;

        if (isset($parameters['port']) === true) {
            $odoo->setPort($parameters['port']);
        }

        return new Model($odoo);
    }

    /**
     * Return the Odoo settings
     *
     * @return array
     */
    private function getSettings()
    {
        $yaml = new Parser();
        $settings = $yaml->parse(file_get_contents($this->settings_file));

        if (isset($settings['odoo']) === false) {
            throw new \RuntimeException('odoo settings must be defined');
        }

        $parameters = $settings['odoo'];
        $required_settings = ['server', 'database', 'username', 'password'];
        foreach ($required_settings as $required_setting) {
            if (isset($parameters[$required_setting]) === false) {
                throw new \RuntimeException(sprintf(
                    'odoo setting "%s" must be defined',
                    $required_setting
                ));
            }
        }

        return $parameters;
    }
}
