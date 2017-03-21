<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector\Csv;

use Goutte\Client;
use OpenAddressBook\Connector\ConnectorSpiceworksInterface;
use OpenAddressBook\Connector\ItemSpiceworksInterface;
use Symfony\Component\Yaml\Parser;

class SpiceworksConnector implements ConnectorSpiceworksInterface
{
    /**
     * @var string
     */
    private $settings_file;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var resource
     */
    private $fhandle;

    /**
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (isset($options['settings_file']) === false || is_string($options['settings_file']) === false) {
            throw new \RuntimeException('settings_file is required and must be a string');
        }

        $this->setSettingsFile($options['settings_file']);
        $this->downloadFile();
        $this->openFile();
    }

    /**
     * Return the list of items to import
     *
     * @return ItemSpiceworksInterface[]
     */
    public function getItems()
    {
        $iterator = 0;
        while (false !== ($row = fgetcsv($this->fhandle, 10000, ','))) {
            if (0 === $iterator++) {
                continue;
            }
            yield new SpiceworksItem($row);
        }
    }

    /**
     * Download the CSV file
     *
     * @throws \RuntimeException
     */
    private function downloadFile()
    {
        $parameters = $this->getSettings();

        $guzzle_client = new \GuzzleHttp\Client([
            'cookies' => true,
        ]);

        $http_client = new Client();
        $http_client->setClient($guzzle_client);

        // Login
        $crawler = $http_client->request('GET', $parameters['base_url'].$parameters['login_url']);
        $form = $crawler->selectButton('Log in')->form();
        $http_client->submit($form, [
            'pro_user[email]' => $parameters['login'],
            'pro_user[password]' => $parameters['password']
        ]);

        // Download CSV
        $filename = tempnam(sys_get_temp_dir(), 'spiceworks');
        $http_client->request(
            'GET',
            $parameters['base_url'].str_replace(
                '%csv_id%',
                $parameters['csv_id'],
                $parameters['csv_url']
            )
        );

        if (false === file_put_contents($filename, $http_client->getResponse()->getContent())) {
            throw new \RuntimeException('Impossible to download CSV file');
        }

        $this->filename = $filename;
    }


    /**
     * Open the CSV file
     */
    private function openFile()
    {
        if (false === file_exists($this->filename)) {
            throw new \DomainException('file '.$this->filename.' odesn\'t exist');
        }

        $this->fhandle = fopen($this->filename, 'rb');
        if (false === $this->fhandle) {
            $this->fhandle = null;
            throw new \DomainException('impossible to open file '.$this->filename);
        }
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
     * Return the Odoo settings
     *
     * @return array
     */
    private function getSettings()
    {
        $yaml = new Parser();
        $settings = $yaml->parse(file_get_contents($this->settings_file));

        if (isset($settings['spiceworks_csv']) === false) {
            throw new \RuntimeException('spiceworks_csv settings must be defined');
        }

        $parameters = $settings['spiceworks_csv'];
        $required_settings = ['base_url', 'csv_url', 'csv_id', 'login_url', 'login', 'password'];
        foreach ($required_settings as $required_setting) {
            if (isset($parameters[$required_setting]) === false) {
                throw new \RuntimeException(sprintf(
                    'spiceworks_csv setting "%s" must be defined',
                    $required_setting
                ));
            }
        }

        return $parameters;
    }

    public function __destruct()
    {
        if (true === file_exists($this->filename) && true === is_file($this->filename)) {
            @unlink($this->filename);
        }
    }
}
