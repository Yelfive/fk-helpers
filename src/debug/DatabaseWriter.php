<?php

/**
 * @author Felix Huang <yelfivehuang@gmail.com>
 * @date 2017-12-14
 */

namespace fk\helpers\debug;

use fk\helpers\Dumper;

class DatabaseWriter implements WriterInterface
{
    protected $attributes = [];
    protected $bindings = [];

    protected $dsn;
    protected $username;
    protected $password;
    protected $table;
    protected $errorHandler;

    /**
     * @var callable To overwrite method persist
     */
    public $persist;

    /**
     * DatabaseWriter constructor.
     * @param string $dsn driver:host=[hostname];dbname=[table]
     * @param string $username
     * @param string $password
     * @param string $table
     * @param null|callable $errorHandler Handles error in case something goes wrong without being noticed
     */
    public function __construct($dsn, $username, $password, $table, $errorHandler = null)
    {
        $rc = new \ReflectionClass(static::class);
        $parameters = $rc->getConstructor()->getParameters();

        foreach (func_get_args() as $k => $v) {
            $this->{$parameters[$k]->getName()} = $v;
        }

        $this->write(['created_at' => date('Y-m-d H:i:s')]);
        $this->writeExtraAttributes();
    }

    /**
     * Interface to allow [[Capture]] to record things
     * @param array $something Something to write with , it must be in the form of `[string $title => mixed $data]`
     * @see Capture::$requestLogVars
     */
    public function write(array $something)
    {
        $this->attributes = array_merge($this->attributes, $something);
    }

    /**
     * This will be triggered when script ends, to save request permanently
     * @see Capture::__construct
     * @see Capture::shutdown
     */
    public function persist()
    {
        $this->write(['updated_at' => date('Y-m-d H:i:s')]);
        $attributes = array_map(function ($v) {
            if (is_callable($v)) $v = call_user_func($v);
            if (is_array($v)) $v = json_encode($v, JSON_UNESCAPED_UNICODE);
            return $v;
        }, $this->attributes);

        $this->filterAttributes();

        $sql = "INSERT INTO `$this->table` SET ";
        $bindings = [];
        foreach ($attributes as $k => $v) {
            if (is_callable($v)) $v = call_user_func($v);
            $sql .= "`$k`=:$k,";
            $bindings[":$k"] = $v;
        }

        $sql = substr($sql, 0, -1);

        return $this->execute($sql, $bindings);
    }

    /**
     * Ensures all attributes exists in database
     */
    protected function filterAttributes()
    {
        $database = explode('dbname=', $this->dsn)[1];
        $stmt = $this->execute("SELECT `column_name` FROM information_schema.COLUMNS where `table_schema`='{$database}' and `table_name`='{$this->table}'");

        $this->attributes = array_intersect_key($this->attributes, array_column($stmt->fetchAll(), 0));
    }

    /**
     * @param string $sql
     * @param array $bindings
     * @return \PDOStatement
     */
    protected function execute(string $sql, array $bindings = [])
    {
        $pdo = $this->getPdo();
        if (0 != $pdo->errorCode()) return $this->error($pdo);

        $stmt = $pdo->prepare($sql);
        if (false === $stmt) {
            $this->error($pdo);
        } else if ($stmt->errorCode()) {
            $this->error($stmt);
        } else if (!$stmt->execute($bindings)) {
            $this->error($stmt);
        } else {
            return $stmt;
        }
    }

    /**
     * @param bool $final
     * @return null|\PDO
     */
    protected function getPdo($final = false)
    {
        static $pdo;
        if (!$pdo) $pdo = new \PDO($this->dsn, $this->username, $this->password);
        try {
            // In case `MySQL has gone away`
            $pdo->exec('SET NAMES utf8mb4');
        } catch (\PDOException $e) {
            if ($final) {
                $this->error($e);
            } else {
                $pdo = null;
                return $this->getPdo(true);
            }
        }
        return $pdo;
    }

    /**
     * @param \PDO|\PDOStatement|string $obj
     * @throws \Exception
     */
    protected function error($obj)
    {
        if (!is_callable($this->errorHandler)) return;
        if (is_object($obj)) {
            //SQLSTATE
            list ($code, $driverCode, $message) = $obj->errorInfo();
            $error = "SQLSTATE[$code]: $message.";
            if ($obj instanceof \PDOStatement) $error .= " SQL: $obj->queryString";
        } else {
            $error = $obj;
        }
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        call_user_func($this->errorHandler, $error . "\n" . Dumper::dump($trace));
    }

    protected function writeExtraAttributes()
    {
        $time = microtime(true);
        $this->write([
            'client_ip' => $_SERVER['X_REAL_IP'] ?? ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'request_token' => $_SERVER['HTTP_X_ACCESS_TOKEN'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'app_version' => $_SERVER['X_APP_VERSION'] ?? '',
            'response_code' => function () {
                $data = json_decode($this->attributes['response_body'], true);
                return $data['code'] ?? 0;
            },
            'time_elapsed_ms' => function () use ($time) {
                return intval((microtime(true) - $time) * 1000);
            }
        ]);
        return $this;
    }
}