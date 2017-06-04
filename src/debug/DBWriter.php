<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-06-04
 */

namespace fk\helpers\debug;

abstract class DBWriter implements WriterInterface
{

    protected $dsn;
    protected $username;
    protected $password;
    protected $table;
    protected $bindings = [];

    /**
     * @var integer ID of the record in db
     */
    protected $requestID;

    public function __construct($dsn, $username, $password, $table)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->table = $table;
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return \PDOStatement
     */
    protected function execute(string $sql, array $bindings = [])
    {
        $pdo = new \PDO($this->dsn, $this->username, $this->password);
        if ($pdo->errorCode()) {
            print_r($pdo->errorInfo());
            die;
        }
        $pdo->exec('SET NAMES utf8mb4');
        $stmt = $pdo->prepare($sql);
        if (false === $stmt) {
            $this->error($pdo);
        } else if ($stmt->errorCode()) {
            $this->error($stmt);
        } else if (!$stmt->execute($bindings)) {
            $this->error($stmt);
        } else {
            return $pdo->lastInsertId();
        }
    }

    /**
     * @param \PDO|\PDOStatement $obj
     * @throws \Exception
     */
    protected function error($obj)
    {
        //SQLSTATE
        list ($code, $driverCode, $message) = $obj->errorInfo();
        $msg = "SQLSTATE[$code]: $message.";
        if ($obj instanceof \PDOStatement) {
            $msg .= " SQL: $obj->queryString";
        }
        throw new \Exception($msg);
    }

    protected function save(array $data)
    {
        return $this->requestID ? $this->update($data) : $this->insert($data);
    }

    protected function insert(array $data)
    {
        $sql = "INSERT INTO $this->table SET ";
        $bindings = [];
        foreach ($data as $k => $v) {
            $sql .= "$k=:$k,";
            $bindings[":$k"] = $v;
        }

        $sql = substr($sql, 0, -1);

        $this->requestID = $this->execute($sql, $bindings);
    }

    protected function update(array $data)
    {
        if (!$this->requestID) return;

        $bindings = [];
        $sql = "UPDATE $this->table SET ";
        foreach ($data as $k => $v) {
            $sql .= "$k=:$k,";
            $bindings[":$k"] = $v;
        }

        $sql = substr($sql, 0, -1);

        $sql .= " WHERE id=$this->requestID";

        $this->execute($sql, $bindings);
    }

}