<?php

require_once 'GentleSource/Database/Query.php';
require_once 'GentleSource/Database/Error.php';

class GentleSource_Database
{
    /**
     * Instance
     */
    private static $instance;

    /**
     * Database connection link
     */
    private $connection = null;

    /**
     * Error messages
     */
    private $messages = array();

    /**
     * Query
     */
    private $query = '';

    /**
     * Get instance
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }

    /**
     * Connect to database server and select database
     */
    public static function connect($dsn, $options = array())
    {
        $db = self::instance();

        if (!$db->connection = mysql_connect($dsn['hostspec'], $dsn['username'], $dsn['password'])) {
            $db->messages[] = array('message' => mysql_error(), 'backtrace' => debug_backtrace());
            $err =& self::raiseError(null, null, null, mysql_error());
            return $err;
        } else {
            if (!mysql_select_db($dsn['database'], $db->connection )) {
                $db->messages[] = array('message' => mysql_error(), 'backtrace' => debug_backtrace());
                $err =& self::raiseError(null, null, null, mysql_error());
                return $err;
            }
        }
        return $db;
    }

    /**
     * Disconnect from database
     */
    public function disconnect()
    {
        return mysql_close($this->connection);
    }

    /**
     * Prepare sql statement
     */
    public function prepare($query)
    {
        return new GentleSource_Database_Query($query, $options = array('connection' => $this->connection));
    }

    /**
     * Database query
     */
    public function query($query, $data = array())
    {
        $result = $this->prepare($query);
        return $result->execute($data);
    }

    /**
     * Quote smart
     */
    public function quoteSmart($value)
    {
        return mysql_real_escape_string($value, $this->connection);
    }

    /**
     * Escape
     */
    public function escape($value)
    {
        return mysql_real_escape_string($value, $this->connection);
    }

    /**
     * Set character set
     */
    public function setCharset($charset)
    {
        return mysql_set_charset($charset, $this->connection );
    }

    /**
     * Get error messages
     */
    public function getMessage()
    {
        return $this->messages;
    }

    /**
     * Get error messages
     */
    public function errorMessage()
    {
        return $this->messages;
    }

    /**
     * Get debug info
     */
    public function getDebugInfo()
    {
        return implode("\n", $this->messages);
    }

    /**
     * Get backtrace
     */
    public function getBacktrace()
    {
        return debug_backtrace();
    }

    /**
     * This method is used to communicate an error and invoke error
     * callbacks etc.  Basically a wrapper for PEAR::raiseError
     * without the message string.
     *
     * @param   mixed   int error code
     *
     * @param   int     error mode, see PEAR_Error docs
     *
     * @param   mixed   If error mode is PEAR_ERROR_TRIGGER, this is the
     *                 error level (E_USER_NOTICE etc).  If error mode is
     *                 PEAR_ERROR_CALLBACK, this is the callback function,
     *                 either as a function name, or as an array of an
     *                 object and method name.  For other error modes this
     *                 parameter is ignored.
     *
     * @param   string  Extra debug information.  Defaults to the last
     *                 query and native error code.
     *
     * @param   object  a PEAR error object
     *
     * @return PEAR_Error instance of a PEAR Error object
     *
     * @access  private
     * @see     PEAR_Error
     */
    private static function &raiseError($code = null, $mode = null, $options = null, $userinfo = null)
    {
        $err =& PEAR::raiseError(null, $code, $mode, $options, $userinfo, 'GentleSource_Database_Error', true);
        return $err;
    }
}