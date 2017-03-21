<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Command;

use OpenAddressBook\Connector\ConnectorSpiceworksInterface;
use OpenAddressBook\Connector\ItemSpiceworksInterface;
use OpenAddressBook\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class RetrieveSpiceworksCommand extends Command
{
    /**
     * @var Database
     */
    private $database;

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('spiceworks:retrieve')
            ->setDescription('Retrieve the spicework datas from an external source')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'The path of the configuration file',
                dirname(dirname(__DIR__)).'/config/command.yml'
            )
        ;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = $this->loadSettings($input, $output);

        $this->database = new Database();
        $this->database->useObjectName('not-address-book');

        /** @var ConnectorSpiceworksInterface $connector */
        $connector = new $settings['connector']($settings['options']);
        $items = $connector->getItems();

        $current_items = $this->database->getAll();
        foreach ($items as $item) {
            if ($this->validateIsImportable($item, $settings['blacklist'], $output) === false) {
                continue;
            }

            $this->addOrUpdate($item, $current_items, $output);
        }

        return 0;
    }

    /**
     * Validate that a item must be import
     *
     * @param ItemSpiceworksInterface   $item
     * @param array           $blacklist
     * @param OutputInterface $output
     * @return bool
     */
    private function validateIsImportable(ItemSpiceworksInterface $item, array $blacklist, OutputInterface $output)
    {
        if ($item->getDomain() === '' || $item->getName() === '') {
            $output->writeln(sprintf(
                '<error>"%s" (%s) isn\'t imported because required field are blank</error>',
                $item->getName(),
                $item->getIdentifier()
            ));
            return false;
        }

        if (in_array($item->getIdentifier(), $blacklist) === true) {
            $output->writeln(sprintf(
                '<error>"%s" (%s) isn\'t imported because it\'s in the blacklist</error>',
                $item->getName(),
                $item->getIdentifier()
            ));
            return false;
        }

        return true;
    }

    /**
     * Add or update a item from Connector
     *
     * @param ItemSpiceworksInterface   $item
     * @param array           $current_items
     * @param OutputInterface $output
     */
    private function addOrUpdate(ItemSpiceworksInterface $item, array $current_items, OutputInterface $output)
    {
        $identifier = $item->getIdentifier();

        $output->writeln(sprintf(
            '<info>add or update "%s" (%s)</info>',
            $item->getName(),
            $identifier
        ));


        $output->writeln(sprintf(
            '<comment>add "%s" (%s)</comment>',
            $item->getName(),
            $identifier
        ));

        $this->database
            ->useObjectId($item->getId())
            ->set(
                $this->populateRedisItem([], $item)
            )
        ;
    }

    /**
     * @param array         $redis_item
     * @param ItemSpiceworksInterface $item
     * @return array
     */
    private function populateRedisItem(array $redis_item, ItemSpiceworksInterface $item)
    {
        $relations = [
            'name' => $item->getName(),
            'tag' => $item->getTag(),
            'domain' => $item->getDomain(),
            'last_user' => $item->getLastUser(),
            'last_logon' => $item->getLastLogon(),
            'id' => $item->getId(),
            'serial' => $item->getSerial(),
            'model' => $item->getModel(),
            'connector-identifier' => $item->getIdentifier(),
        ];

        foreach ($relations as $key => $new_value) {
            $redis_item[$key] = $new_value;
        }

        return $redis_item;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return array
     */
    private function loadSettings(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getOption('config');

        $output->writeln(sprintf(
            '<info>Using "%s" for configuration file</info>',
            $filename
        ));

        if (is_file($filename) === false) {
            throw new \InvalidArgumentException('config file must exist');
        }

        $yaml = new Parser();
        $parameters = $yaml->parse(file_get_contents($filename));

        if (is_array($parameters) === false || isset($parameters['command_spiceworks']) === false) {
            throw new \InvalidArgumentException('config file is not well formated');
        }

        $settings = $parameters['command_spiceworks'];
        if (isset($settings['connector']) === false) {
            throw new \InvalidArgumentException('config file must contains the connector class');
        }

        $output->writeln(sprintf(
            '<info>Using connector : "%s"</info>',
            $settings['connector']
        ));

        if (isset($settings['options']) === true && is_array($settings['options']) === true) {
            $output->writeln(sprintf(
                '<info>Using connector options : "%s"</info>',
                serialize($settings['options'])
            ));
        } else {
            $settings['options'] = [];
        }

        if (isset($settings['blacklist']) === false || is_array($settings['blacklist']) === false) {
            $settings['blacklist'] = [];
        }

        return $settings;
    }
}
