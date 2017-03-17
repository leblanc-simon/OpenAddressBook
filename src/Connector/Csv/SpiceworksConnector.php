<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector\Csv;

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
     * Openthe CSV file
     */
    private function openFile()
    {
        $parameters = $this->getSettings();

        $filename = __DIR__.'/../../../'.$parameters['file'];
        if (false === file_exists($filename)) {
            throw new \DomainException('file '.$parameters['file'].' odesn\'t exist');
        }

        $this->fhandle = fopen($filename, 'rb');
        if (false === $this->fhandle) {
            $this->fhandle = null;
            throw new \DomainException('impossible to open file '.$parameters['file']);
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
        $required_settings = ['file'];
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
}
