<?php

namespace KKsonFramework\App;

use KKsonFramework\Conf\DbConfig;
use mysqli;

class MySQLiHelper
{
    /**
     * @var mysqli
     */
    private $mysqli;

    public function __construct() {
        $config = DbConfig::get();
        $this->mysqli = new mysqli($config->dbHost(), $config->dbUsername(), $config->dbPassword(), $config->dbDatabase());

        if ($this->mysqli->connect_errno) {
            throw new \Exception("Failed to connect to MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
        }

        $this->mysqli->set_charset("utf8mb4");

        if ($this->mysqli->connect_errno) {
            throw new \Exception("Failed to set charset : (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error);
        }
    }

    /**
     * @param $sql
     * @param array $data
     * @param Callable $fetchCallback
     * @return mixed
     * @throws \Exception
     */
    public function query($sql, $data = [], $fetchCallback = null) {
        if (!($stmt = $this->mysqli->prepare($sql))) {
            throw new \Exception("Prepare failed: (" . $this->mysqli->errno . ") " . $this->mysqli->error);
        }

        $params = [""];
        foreach ($data as &$d) {
            $params[0].= "s";
            if(is_float($d)) {
                $params[0] .= "d";
            } else if(is_int($d)) {
                $params[0] .= "i";
            }
            $params[] = &$d;
        }

        if(!empty($data)) {
            if (!call_user_func_array([$stmt, 'bind_param'], $params)) {
                throw new \Exception("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            }
        }


        if(!$stmt->execute()) {
            throw new \Exception("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        if($fetchCallback !== false) {
            if (!($res = $stmt->get_result()) && $stmt->errno != 0) {
                throw new \Exception("Getting result set failed: (" . $stmt->errno . ") " . $stmt->error);
            }
            if($fetchCallback) {
                while($row = $res->fetch_assoc()) {
                    $fetchCallback($row);
                }
                $res->close();
                $stmt->close();
            } else {
                if(is_bool($res)) {
                    $stmt->close();
                    return $res;
                } else {
                    $result = $res->fetch_all();
                    $res->close();
                    $stmt->close();
                    return $result;
                }

            }
        } else {
            $stmt->close();
        }
        return null;
    }

    public function exec($sql, $data = []) {
        $this->query($sql, $data);
    }

    public function requestLongTimeoutQuery() {
        //self::query('SET SESSION connect_timeout=28800', [], false);
        $this->query('SET SESSION wait_timeout=28800', [], false);
        $this->query('SET SESSION interactive_timeout=28800', [], false);
    }

    public function close() {
        $this->mysqli->close();
    }
}