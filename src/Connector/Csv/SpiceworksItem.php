<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector\Csv;

use OpenAddressBook\Connector\ItemSpiceworksInterface;

class SpiceworksItem implements ItemSpiceworksInterface
{
    private $id;
    private $name;
    private $domain;
    private $tag;
    private $last_user;
    private $last_logon;
    private $identifier;
    private $serial;
    private $model;

    /**
     * @param array $data the Odoo request's datas
     */
    public function __construct(array $data)
    {
        $this->populate($data);
    }

    /**
     * Populate object with the data from Odoo request
     *
     * @param array $data the Odoo request's datas
     */
    public function populate(array $data)
    {
        $this->reset();

        $relations = [
            0 => 'identifier',
            1 => 'tag',
            2 => 'domain',
            3 => 'name',
            4 => 'last_user',
            5 => 'last_logon',
            6 => 'serial',
            7 => 'model',
        ];

        foreach ($relations as $spicework => $property) {
            if (
                isset($data[$spicework]) === false ||
                (is_string($data[$spicework]) === false && is_numeric($data[$spicework]) === false)
            ) {
                continue;
            }

            if ('identifier' === $property) {
                $value = 'spiceworks-'.$data[$spicework];
            } elseif ('tag' === $property) {
                $value = '';
                $tag = trim($data[$spicework]);
                if (false !== strpos($tag, '|_')) {
                    $tags = explode('|', $tag);
                    foreach ($tags as $tag) {
                        if (substr($tag, 0, 1) === '_') {
                            $value = $tag;
                            break;
                        }
                    }
                }
            } elseif ('last_logon' === $property) {
                $value = trim($data[$spicework]);
                if (empty($value) === false) {
                    try {
                        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                        $value = $date->format('d/m/Y');
                    } catch (\Exception $e) {}
                }
            } else {
                $value = trim($data[$spicework]);
            }

            $this->{$property} = $value;

            if ('identifier' === $property) {
                $this->id = str_replace('spiceworks-', '', $value);
            }
        }
    }

    private function reset()
    {
        $properties = get_object_vars($this);
        foreach ($properties as $property => $value) {
            $this->{$property} = '';
        }
    }

    /**
     * Return the unique identifier for the contact
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Return the unique identifier for the contact
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the name of the contact
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return the direct phone line of the contact
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Return the cell phone number of the contact
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Return the standard phone line of the contact
     * @return string
     */
    public function getLastLogon()
    {
        return $this->last_logon;
    }

    /**
     * Return the mail of the contact
     * @return string
     */
    public function getLastUser()
    {
        return $this->last_user;
    }

    /**
     * Return the serial of the PC
     * @return mixed
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * Return the model of the PC
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
