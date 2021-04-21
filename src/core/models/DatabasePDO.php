<?php

/**
 * Copyright Maarch since 2008 under license.
 * See LICENSE.txt file at the root folder for more details.
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

        if (!empty($args['configPath'])) {
            $loadedXml = CoreConfigModel::getXmlLoaded(['path' => $args['configPath'] . '/config.xml']);
        } else {
            $loadedXml = CoreConfigModel::getConfig();
        }

        if (!$loadedXml) {
            throw new \Exception('Configuration file can not be read');
        }

        foreach ($loadedXml->database as $key => $database) {
            $server     = (string)$database->server;
            $port       = (string)$database->port;
            $name       = (string)$database->name;
            $user       = (string)$database->user;
            $password   = (string)$database->password;
            self::$type = (string)$database->type;

            $formattedDriver = 'pgsql';
            if (self::$type == 'POSTGRESQL') {
                $formattedDriver = 'pgsql';
            } elseif (self::$type == 'MYSQL') {
                $formattedDriver = 'mysql';
            } elseif (self::$type == 'ORACLE') {
                $formattedDriver = 'oci';
            }
    
            ValidatorModel::notEmpty(['server' => $server, 'port' => $port, 'name' => $name, 'user' => $user], ['server', 'port', 'name', 'user']);
            ValidatorModel::stringType(['server' => $server, 'name' => $name, 'user' => $user], ['server', 'name', 'user']);
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
                break;
            } catch (\PDOException $PDOException) {
                try {
                    $options[\PDO::ATTR_PERSISTENT] = false;
                    self::$pdo = new \PDO($dsn, $user, $password, $options);
                    break;
                } catch (\PDOException $PDOException) {
                    if (!empty($loadedXml->database[$key + 1])) {
                        continue;
                    } else {
                        throw new \Exception($PDOException->getMessage());
                    }
                }
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
            $position = 0;
            foreach ($data as $value) {
                if (is_array($value)) {
                    $placeholders = implode(',', array_fill(0, count($value), '?'));
                    $position = strpos($queryString, '(?)', $position + 1);
                    $queryString = substr_replace($queryString, $placeholders, $position + 1, 1);

                    $tmpData = array_merge($tmpData, $value);
                } else {
                    $tmpData[] = $value;
                }
            }
            $data = $tmpData;
        }

        try {
            self::$pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_NATURAL);
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

    public function beginTransaction()
    {
        return self::$pdo->beginTransaction();
    }

    public function commitTransaction()
    {
        return self::$pdo->commit();
    }

    public function rollbackTransaction()
    {
        return self::$pdo->rollBack();
    }
}
