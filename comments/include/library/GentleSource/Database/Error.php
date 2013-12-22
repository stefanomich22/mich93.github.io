<?php
class GentleSource_Database_Error extends PEAR_Error
{
    /**
     * MDB2_Error constructor.
     *
     * @param   mixed    error code, or string with error message.
     * @param   int     what 'error mode' to operate in
     * @param   int     what error level to use for $mode & PEAR_ERROR_TRIGGER
     * @param   smixed   additional debug info, such as the last query
     */
    public function __construct($code = null, $mode = PEAR_ERROR_RETURN, $level = E_USER_NOTICE, $debuginfo = null)
    {
        $db = GentleSource_Database::instance();
        $this->PEAR_Error('GentleSource Database Error: ' . implode("\n", $db->errorMessage($code)), $code, $mode, $level, $debuginfo);
    }
}