<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector\Odoo;

use OpenAddressBook\Connector\ItemInterface;

class Item implements ItemInterface
{
    private $name;
    private $phone;
    private $mobile;
    private $standard;
    private $mail;
    private $role;
    private $customer;
    private $identifier;

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
            'name' => 'name',
            'phone' => 'phone',
            'mobile' => 'mobile',
            'email' => 'mail',
            'function' => 'role',
            'id' => 'identifier',
        ];

        foreach ($relations as $odoo => $property) {
            if (
                isset($data[$odoo]) === false ||
                (is_string($data[$odoo]) === false && is_numeric($data[$odoo]) === false)
            ) {
                continue;
            }

            if ('id' === $odoo) {
                $value = 'odoo-'.$data[$odoo];
            } else {
                $value = trim($data[$odoo]);
            }
            $this->{$property} = $value;
        }

        if (
            isset($data['parent_id']) === true && is_array($data['parent_id'])
            && count($data['parent_id']) === 2
        ) {
            $this->customer = trim($data['parent_id'][1]);
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
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Return the cell phone number of the contact
     * @return string
     */
    public function getPortable()
    {
        return $this->mobile;
    }

    /**
     * Return the standard phone line of the contact
     * @return string
     */
    public function getStandard()
    {
        return $this->standard;
    }

    /**
     * Return the mail of the contact
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Return the role of the contact in the company
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Return the name of the company
     * @return string
     */
    public function getCustomer()
    {
        return $this->customer;
    }

}
