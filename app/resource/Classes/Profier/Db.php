<?php
namespace Pentagonal\Profier;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Pentagonal\Profier\Exceptions\DBException;

class Db
{
    /**
     * List available driver for \Doctrine\DBAL
     *
     * @var array
     */
    protected $available_driver = [
        'pdo_mysql',
        'drizzle_pdo_mysql',
        'mysqli',
        'pdo_sqlite',
        'pdo_pgsql',
        'pdo_oci',
        'pdo_sqlsrv',
        'sqlsrv',
        'oci8',
        'sqlanywhere',
    ];

    /** ==============================================
     *
     * Take from Doctrine\DBAL\Connection; -> constant
     *
     *  ==============================================
     */

    /**
     * Constant for transaction isolation level READ UNCOMMITTED.
     */
    const TRANSACTION_READ_UNCOMMITTED = Connection::TRANSACTION_READ_UNCOMMITTED;

    /**
     * Constant for transaction isolation level READ COMMITTED.
     */
    const TRANSACTION_READ_COMMITTED = Connection::TRANSACTION_READ_COMMITTED;

    /**
     * Constant for transaction isolation level REPEATABLE READ.
     */
    const TRANSACTION_REPEATABLE_READ = Connection::TRANSACTION_REPEATABLE_READ;

    /**
     * Constant for transaction isolation level SERIALIZABLE.
     */
    const TRANSACTION_SERIALIZABLE = Connection::TRANSACTION_SERIALIZABLE;

    /**
     * Represents an array of int to be expanded by Doctrine SQL parsing.
     *
     * @var integer
     */
    const PARAM_INT_ARRAY = Connection::PARAM_INT_ARRAY;

    /**
     * Represents an array of strings to be expanded by Doctrine SQL parsing.
     *
     * @var integer
     */
    const PARAM_STR_ARRAY = Connection::PARAM_STR_ARRAY;

    /**
     * Offset by which PARAM_* constants are detected as arrays of the param type.
     *
     * @var integer
     */
    const ARRAY_PARAM_OFFSET = Connection::ARRAY_PARAM_OFFSET;

    private $config;

    /**
     * Record Database Connection of Doctrine
     *
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * Quote Identifier database
     *
     * @var string
     */
    private $quoteIdentifier = '';

    /**
     * Check Database driver available for Doctrine
     * and choose the best driver of sqlsrv an oci
     *
     * @param string $driverName
     * @return bool|string return lowercase an fix database river for \Doctrine\DBAL
     */
    public function driverAvailable($driverName)
    {
        if (is_string($driverName) && trim($driverName)) {
            $driverName = trim(strtolower($driverName));
            if (in_array($driverName, $this->available_driver)) {
                // switch to Doctrine fixed db
                switch ($driverName) {
                    case 'pdo_oci':
                        return 'oci8';
                    case 'pdo_sqlsrv':
                        return 'sqlsrv';
                }

                return $driverName;
            }
        }

        return false;
    }

    /**
     * Db constructor.
     *
     * @param array $config the database configuration
     * @throws \Pentagonal\Profier\Exceptions\DBException
     * @throws \Doctrine\DBAL\DBALException
     * @access private
     */
    private function __construct(array $config = [])
    {
        $this->config = new Collector($config);

        if (!is_string($this->config->get('dbname')) || trim($this->config->get('dbname')) == '') {
            throw new DBException(
                'Database name could not empty or must be as string',
                E_USER_ERROR
            );
        }

        if (!is_string($this->config->get('user')) || trim($this->config->get('user')) == '') {
            throw new DBException(
                'Database user could not empty or must be as string',
                E_USER_ERROR
            );
        }
        if (!is_string($this->config->retrieve('driver', 'pdo_mysql'))) {
            throw new DBException(
                'Invalid database driver!',
                E_USER_ERROR
            );
        }
        if (!$this->driverAvailable($this->config->retrieve('driver', 'pdo_mysql'))) {
            throw new DBException(
                sprintf(
                    'Invalid database driver! Database driver for %s is unavailable!',
                    (string) $this->config->retrieve('driver', 'pdo_mysql')
                ),
                E_USER_ERROR
            );
        }

        $this->config->set(
            'driver',
            $this->driverAvailable(
                $this->config->retrieve('db_driver', 'pdo_mysql')
            )
        );
        $port = $this->config->retrieve('port', 3306);
        if (!is_numeric($port) || !is_int(abs($port))) {
            throw new DBException(
                'Invalid Database Port , database port must be as integer',
                E_USER_ERROR
            );
        }
        $this->config->set('port', abs($port));
        $connectionParams = [
            'user' => $this->config->retrieve('user'),
            'password' => $this->config->retrieve('password'),
            'host' => $this->config->retrieve('host', 'localhost'),
            'driver' => $this->config->retrieve('driver', 'pdo_mysql'),
            'port' => $this->config->retrieve('port', 3306),
            'dbname' => $this->config->retrieve('dbname'),
        ];

        // cache connection
        $this->connection = DriverManager::getConnection($connectionParams);
    }

    /**
     * Instance Application
     *
     * @param array   $config database configuration
     * @return object $this \Pentagonal\Tenor\Db
     */
    public static function create(array $config)
    {
        return new Db($config);
    }

    /**
     * @param string $constant constant to call via \Doctrine\DBAL\Connection
     * @property string $constant
     *
     * @return mixed
     */
    public static function getConstant($constant)
    {
        if (defined('\Doctrine\DBAL\Connection::'.$constant)) {
            /** @noinspection PhpUndefinedFieldInspection */
            return Connection::$constant;
        }

        return null;
    }

    /**
     * Connection start connect
     *
     * @return object $this  \Pentagonal\Db
     */
    public function connect()
    {
        $this->connection->connect();
        return $this;
    }

    /**
     * Trimming table for safe usage
     *
     * @param mixed $table
     * @return mixed
     */
    public function trimSelector($table)
    {
        if (empty($this->quote_identifier)) {
            $this->connect();
            $identifier = $this->connection->quoteIdentifier('z');
            $this->quoteIdentifier = substr($identifier, 0, 1);
        }

        if (is_array($table)) {
            return array_map([$this, 'trimSelector'], $table);
        } elseif (is_object($table)) {
            foreach (get_object_vars($table) as $key => $value) {
                $table->{$key} = $this->trimSelector($value);
            }

            return $table;
        }

        if (is_string($table)) {
            $tableArray = explode('.', $table);
            foreach ($tableArray as $key => $value) {
                $tableArray[$key] = trim(
                    trim(
                        trim($value),
                        $this->quoteIdentifier
                    )
                );
            }
            $table = implode('.', $tableArray);
        }

        return $table;
    }

    /**
     * Alternative multi variable type quoted identifier
     *
     * @param mixed $quoteStr
     * @return mixed
     */
    public function quoteIdentifierAlt($quoteStr)
    {
        $quoteStr = $this->trimSelector($quoteStr);
        if (is_array($quoteStr)) {
            foreach ($quoteStr as $key => $value) {
                $quoteStr[$key] = $this->quoteIdentifierAlt($value);
            }
            return $quoteStr;
        } elseif (is_object($quoteStr)) {
            foreach (get_object_vars($quoteStr) as $key => $value) {
                $quoteStr->{$key} = $this->quoteIdentifierAlt($value);
            }
            return $quoteStr;
        }

        return $this->connection->quoteIdentifier($quoteStr);
    }

    /**
     * Prefix CallBack
     *
     * @access private
     * @param  string $table the table
     * @return string
     */
    private function prefixTableCallback($table)
    {
        $prefix = $this->config->get('db_prefix');
        if (!empty($prefix) && is_string($prefix) && trim($prefix)) {
            $table = (strpos($table, $prefix) === 0)
                ? $table
                : $prefix.$table;
        }

        return $table;
    }

    /**
     * Prefixing table with predefined table prefix on configuration
     *
     * @param mixed $table
     * @param bool  $use_identifier
     * @return array|null|string
     */
    public function prefixTable($table, $use_identifier = false)
    {
        if (empty($this->quote_identifier)) {
            $this->connect();
            $identifier = $this->connection->quoteIdentifier('z');
            $this->quoteIdentifier = substr($identifier, 0, 1);
        }
        $prefix = $this->config->get('db_prefix');

        if (is_array($table)) {
            foreach ($table as $key => $value) {
                $table[$key] = $this->prefixTable($value, $use_identifier);
            }
            return $table;
        }

        if (is_object($table)) {
            foreach (get_object_vars($table) as $key => $value) {
                $table->{$key} = $this->prefixTable($value, $use_identifier);
            }

            return $table;
        }

        if (!is_string($table)) {
            return null;
        }

        if (strpos($table, $this->quoteIdentifier) !== false) {
            $use_identifier = true;
        }

        if (!empty($prefix) && is_string($prefix) && trim($prefix)) {
            $tableArray = explode('.', $table);
            $tableArray    = $this->trimSelector($tableArray);
            if (count($tableArray) > 1) {
                if ($tableArray[0] == $this->config->get('db_name')) {
                    $tableArray[1] = $this->prefixTableCallback($tableArray);
                }
                if ($use_identifier) {
                    return $this->quoteIdentifier
                    . implode("{$this->quoteIdentifier}.{$this->quoteIdentifier}", $tableArray)
                    . $this->quoteIdentifier;
                } else {
                    return implode(".", $tableArray);
                }
            } else {
                $table = $this->prefixTableCallback($tableArray[0]);
            }
        }

        return $use_identifier
            ? $this->quoteIdentifier.$table.$this->quoteIdentifier
            : $table;
    }

    /**
     * Private callback default returning of self::compileBindsQuestionMark()
     *
     * @access private
     * @param string $sql
     * @param bool   $is_fail
     * @return \stdClass
     */
    private function defaultReturnCompile($sql, $is_fail = false)
    {
        $std = new \stdClass();
        $std->fail = $is_fail;
        $std->sql  = $sql;
        return $std;
    }

    /**
     * Compile Bindings
     *     Take From CI 3 Database Query Builder, default string Binding use Question mark ( ? )
     *
     * @param   string $sql   sql statement
     * @param   array  $binds array of bind data
     * @return  object $sql  \stdClass()
     */
    public function compileBindsQuestionMark($sql, $binds = null)
    {
        if (empty($binds) || strpos($sql, '?') === false) {
            return $this->defaultReturnCompile($sql);
        } elseif (! is_array($binds)) {
            $binds = [$binds];
            $bind_count = 1;
        } else {
            // Make sure we're using numeric keys
            $binds = array_values($binds);
            $bind_count = count($binds);
        }

        // Make sure not to replace a chunk inside a string that happens to match the bind marker
        if ($c_s = preg_match_all("/'[^']*'/i", $sql, $matches)) {
            $c_s = preg_match_all(
                '/\?/i', # regex
                str_replace(
                    $matches[0],
                    str_replace('?', str_repeat(' ', 1), $matches[0]),
                    $sql,
                    $c_s
                ),
                $matches, # matches
                PREG_OFFSET_CAPTURE
            );

            // Bind values' count must match the count of markers in the query
            if ($bind_count !== $c_s) {
                return $this->defaultReturnCompile($sql, true);
            }
        } elseif (($c_s = preg_match_all('/\?/i', $sql, $matches, PREG_OFFSET_CAPTURE)) !== $bind_count) {
            return $this->defaultReturnCompile($sql);
        }

        do {
            $c_s--;
            $escaped_value = is_int($binds[$c_s]) ? $binds[$c_s] : $this->quote($binds[$c_s]);
            if (is_array($escaped_value)) {
                $escaped_value = '('.implode(',', $escaped_value).')';
            }
            $sql = substr_replace($sql, $escaped_value, $matches[0][$c_s][1], 1);
        } while ($c_s !== 0);

        return $this->defaultReturnCompile($sql);
    }

    /**
     * Query using binding optionals statements
     *
     * @uses   \Pentagonal\Db\compileBindsQuestionMark()
     * @param  string $sql
     * @param  mixed  $statement array|string|null
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Pentagonal\Profier\Exceptions\DBException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function queryBinds($sql, $statement = null)
    {
        $this->connect();
        $sql = $this->compileBindsQuestionMark($sql, $statement);
        if ($sql->fail) {
            throw new DBException(
                sprintf(
                    'Invalid statement binding count with sql query : %s',
                    $sql->sql
                ),
                E_USER_WARNING
            );
        }

        return $this->connection->query($sql->sql);
    }

    /**
     * @param  string $sql the sql statement query
     * @param  mixed  $statement array|string|null query binding
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws \Pentagonal\Profier\Exceptions\DBException
     */
    public function queryBind($sql, $statement = null)
    {
        return $this->queryBinds($sql, $statement);
    }

    /**
     * Quote string for insert into SQL execution
     *
     * @param mixed $quoteStr value to quote
     * @param mixed $param_type
     * @return mixed
     */
    public function quote($quoteStr, $param_type = null)
    {
        if (is_array($quoteStr)) {
            foreach ($quoteStr as $key => $value) {
                $quoteStr[$key] = $this->quote($value, $param_type);
            }
            return $quoteStr;
        } elseif (is_object($quoteStr)) {
            foreach (get_object_vars($quoteStr) as $key => $value) {
                $quoteStr->{$key} = $this->quote($value, $param_type);
            }
            return $quoteStr;
        }

        return $this->connection->quote($quoteStr, $param_type);
    }

    /**
     * Magic Method __call - calling arguments
     *
     * @param string $name method object
     * @param array  $arguments the arguments list
     * @return mixed
     * @throws \Pentagonal\Profier\Exceptions\DbException
     */
    public function __call($name, $arguments)
    {
        // Doing connect
        $this->connect();

        // check if method exists on connection (\Doctrine\DBAL\Connection)!
        if (method_exists($this->connection, $name)) {
            return call_user_func_array([$this->connection, $name], $arguments);
        }
        throw new DBException(
            sprintf(
                "Call to undefined Method %s",
                $name
            ),
            E_USER_ERROR
        );
    }
}
