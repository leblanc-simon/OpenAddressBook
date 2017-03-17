<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AddressBook extends Controller
{
    public function __construct(Application $application)
    {
        parent::__construct($application);

        $this->database->useObjectName('not-address-book');
    }


    public function getAll()
    {
        $datas = $this->database->getAll();
        if (false === is_array($datas)) {
            $datas = [];
        }

        return $this->render($datas);
    }


    public function get($id)
    {
        $datas = $this->database
                    ->useObjectId($id)
                    ->get();

        if (true === empty($datas)) {
            return $this->render404('address book');
        }

        return $this->render($datas);
    }


    public function save(Request $request, $id = null)
    {
        $this->database->useObjectId($id);

        $datas = $request->request->all();

        $datas = $this->database->set($datas)->get();

        return $this->render($datas);
    }


    public function delete($id)
    {
        $this->database->useObjectId($id)->delete();

        return $this->render(null);
    }
}
