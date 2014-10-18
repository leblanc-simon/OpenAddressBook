<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Click2Call;

interface Click2CallInterface
{
    public function __construct();

    /**
     * Set the parameters from the config file
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters);

    /**
     * Launch call
     *
     * @param   string  $number_to_call     the number to call
     * @param   string  $caller             the identifiant of the caller
     * @return  bool                        true if the call is OK, false else
     */
    public function call($number_to_call, $caller);

    /**
     * Return the last error
     *
     * @return string
     */
    public function getError();
}
