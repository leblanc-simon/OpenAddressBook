<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector;

interface ItemInterface
{
    /**
     * Return the unique identifier for the contact
     * @return string
     */
    public function getIdentifier();

    /**
     * Return the name of the contact
     * @return string
     */
    public function getName();

    /**
     * Return the direct phone line of the contact
     * @return string
     */
    public function getPhone();

    /**
     * Return the cell phone number of the contact
     * @return string
     */
    public function getPortable();

    /**
     * Return the standard phone line of the contact
     * @return string
     */
    public function getStandard();

    /**
     * Return the mail of the contact
     * @return string
     */
    public function getMail();

    /**
     * Return the role of the contact in the company
     * @return string
     */
    public function getRole();

    /**
     * Return the name of the company
     * @return string
     */
    public function getCustomer();
}
