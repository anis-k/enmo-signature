<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief DatabasePDO
 * @author dev@maarch.org
 */

namespace SrcCore\models;

class DatabasePDO
{
    private static $pdo             = null;
    private static $type            = null;
    private static $preparedQueries = [];


    public function __construct(array $args = [])
    {
        if (!empty(self::$pdo)) {
            return;
        }

        $server = '';
        $port = '';
        $name = '';
        $user = '';
        $password = '';
        $formattedDriver = '';

        if (!empty($args['server'])) {
            $server     = $args['server'];
            $port       = $args['port'];
            $name       = $args['name'];
            $user       = $args['user'];
            $password   = $args['password'];
            self::$type = $args['type'];
        } else {
            $loadedXml = CoreConfigModel::getConfig();
            if ($loadedXml) {
                $server     = (string)$loadedXml->database->server;
                $port       = (string)$loadedXml->database->port;
                $name       = (string)$loadedXml->database->name;
                $user       = (string)$loadedXml->database->user;
                $password   = (string)$loadedXml->database->password;
                self::$type = (string)$loadedXml->database->type;
            }
        }

        if (self::$type == 'POSTGRESQL') {
            $formattedDriver = 'pgsql';
        } elseif (self::$type == 'MYSQL') {
            $formattedDriver = 'mysql';
        } elseif (self::$type == 'ORACLE') {
            $formattedDriver = 'oci';
        }

        ValidatorModel::notEmpty(
            ['driver' => $formattedDriver, 'server' => $server, 'port' => $port, 'name' => $name, 'user' => $user],
            ['driver', 'server', 'port', 'name', 'user']
        );
        ValidatorModel::stringType(
            ['driver' => $formattedDriver, 'server' => $server, 'name' => $name, 'user' => $user],
            ['driver', 'server', 'name', 'user']
        );
        ValidatorModel::intVal(['port' => $port], ['port']);

        if (self::$type == 'ORACLE') {
            $dsn = "oci:dbname=(DESCRIPTION = (ADDRESS_LIST =(ADDRESS = (PROTOCOL = TCP)(HOST = {$server})(PORT = {$port})))(CONNECT_DATA =(SERVICE_NAME = {$name})))";
        } else {
            $dsn = "{$formattedDriver}:host={$server};port={$port};dbname={$name}";
        }

        $options = [
            \PDO::ATTR_PERSISTENT   => true,
            \PDO::ATTR_ERRMODE      => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_CASE         => \PDO::CASE_LOWER
        ];

        try {
            self::$pdo = new \PDO($dsn, $user, $password, $options);
        } catch (\PDOException $PDOException) {
            try {
                $options[\PDO::ATTR_PERSISTENT] = false;
                self::$pdo = new \PDO($dsn, $user, $password, $options);
            } catch (\PDOException $PDOException) {
                throw new \Exception($PDOException->getMessage());
            }
        }

        if (self::$type == 'ORACLE') {
            $this->query("alter session set nls_date_format='dd-mm-yyyy HH24:MI:SS'");
        }
    }

    public function query($queryString, array $data = [])
    {
        if (self::$type == 'ORACLE') {
            $queryString = str_ireplace('CURRENT_TIMESTAMP', 'SYSDATE', $queryString);
        }

        if (!empty($data)) {
            $tmpData = [];
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $position = strpos($queryString, '(?)');
                    $queryString = substr_replace($queryString, $placeholders, $position + 1, 1);

                    $tmpData = array_merge($tmpData, $value);
                } else {
                    $tmpData[] = $value;
                }
            }
            $data = $tmpData;
        }

        try {
            if (empty(self::$preparedQueries[$queryString])) {
                $query = self::$pdo->prepare($queryString);
                self::$preparedQueries[$queryString] = $query;
            } else {
                $query = self::$preparedQueries[$queryString];
            }

            $query->execute($data);
        } catch (\PDOException $PDOException) {
            $param = implode(', ', $data);

            $file = fopen('queries_error.log', 'a');
            fwrite($file, '[' . date('Y-m-d H:i:s') . '] ' . $queryString . PHP_EOL);
            fwrite($file, '[' . date('Y-m-d H:i:s') . "] [{$param}]" . PHP_EOL);
            fclose($file);

            throw new \Exception($PDOException->getMessage());
        }

        return $query;
    }

    public static function reset()
    {
        self::$pdo = null;
        self::$preparedQueries = [];
    }
}
