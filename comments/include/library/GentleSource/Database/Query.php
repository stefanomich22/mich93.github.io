<?php
require 'PEAR.php';

class GentleSource_Database_Query
{
    /**
     * Query
     */
    private $query = '';

    /**
     * Result
     */
    private $result = null;

    /**
     * Database connection
     */
    private $connection = null;

    /**
     * Messages
     */
    private $messages = array();

    /**
     * Constructor
     */
    public function __construct($query, $options)
    {
        $this->query = str_replace('?', '%s', $query);
        $this->connection = $options['connection'];
    }

    /**
     * Execute query
     */
    public function execute($data)
    {
        $args = array();

        foreach ($data as $value)
        {
            if (is_int($value)) {
                $new_value = (int) $value;
            } else {
                $new_value = '\'' . mysql_real_escape_string($value, $this->connection) . '\'';
            }

            $args[] = $new_value;
        }

        $this->query = vsprintf($this->query, $args);

        if (!$this->result = mysql_query($this->query)) {
            $this->messages[] = array('message' => mysql_error(), 'backtrace' => debug_backtrace());
            $err =& self::raiseError(null, null, null, mysql_error());
            return $err;
        }

        return $this;
    }

    /**
     * Fetch table row
     */
    public function fetchRow()
    {
        return mysql_fetch_assoc($this->result);
    }

    /**
     * Affected rows
     */
    public function affectedRows()
    {
        return mysql_affected_rows($this->connection);
    }

    /**
     * Get error messages
     */
    public function getMessage()
    {
        return $this->messages;
    }

    /**
     * Get debug info
     */
    public function getDebugInfo()
    {
        return $this->messages;
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