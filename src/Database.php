<?php
/* This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details. */

namespace OpenAddressBook;

use Predis\Client;

class Database
{
    const DATABASE_PREFIX_NAME = 'OpenAddressBook-';

    private $client;
    private $object_name = null;
    private $object_id = null;
    private $database_name = null;

    public function __construct($options = [])
    {
        $this->client = new Client($options);
        $this->initDatabaseName();
    }


    public function useObjectName($object_name)
    {
        $this->object_name = $object_name;

        return $this;
    }


    public function useObjectId($id = null)
    {
        $this->object_id = $id;

        return $this;
    }

    public function set(array $datas)
    {
        if (null === $this->object_id) {
            $this->object_id = $this->reserveNextId();
        }

        $id = $this->buildId();

        $this->client->hset($id, 'id', $this->object_id);
        foreach ($datas as $key => $value) {
            $this->client->hset($id, $key, $value);
        }

        return $this;
    }


    public function get()
    {
        $id = $this->buildId();

        return $this->client->hgetall($id);
    }


    public function getAll()
    {
        $ids = $this->client->lrange($this->database_name.$this->object_name.'-ids', 0, -1);
        if (false === is_array($ids)) {
            return [];
        }

        $objects = [];
        foreach ($ids as $id) {
            $object = $this->useObjectId($id)->get();
            if (true === is_array($object) && false === empty($object)) {
                $objects[] = $object;
            }
        }

        return $objects;
    }


    public function delete()
    {
        $id = $this->buildId();

        foreach ($this->client->hgetall($id) as $key => $value) {
            $this->client->hdel($id, $key);
        }

        return $this;
    }


    private function buildId()
    {
        if (null === $this->object_id || null === $this->object_name) {
            throw new \Exception('id and name must be initialize');
        }

        return $this->database_name.$this->object_name.'_'.$this->object_id;
    }


    private function reserveNextId()
    {
        $key = $this->database_name.$this->object_name.'-id';
        $id = $this->client->incr($key);

        $this->client->rpush($key.'s', $id);

        return $id;
    }

    private function initDatabaseName()
    {
        $this->database_name = self::DATABASE_PREFIX_NAME;

        $db_name = getenv('OAB_DB_NAME', true);
        if (false === $db_name) {
            $db_name = getenv('OAB_BD_NAME');
        }

        if (false === empty($db_name)) {
            $this->database_name .= $db_name.'-';
        }
    }
}
