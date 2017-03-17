<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Connector;

interface ItemSpiceworksInterface
{
    /**
     * Return the unique identifier
     * @return string
     */
    public function getIdentifier();

    /**
     * Return the ide
     * @return string
     */
    public function getId();

    /**
     * Return the name of the PC
     * @return string
     */
    public function getName();

    /**
     * Return the tag
     * @return string
     */
    public function getTag();

    /**
     * Return the domain
     * @return string
     */
    public function getDomain();

    /**
     * Return the last user logon
     * @return string
     */
    public function getLastUser();

    /**
     * Return the date of last logon
     * @return string
     */
    public function getLastLogon();
}
