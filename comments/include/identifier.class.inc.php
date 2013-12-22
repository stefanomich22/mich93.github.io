<?php

/**
 * GentleSource Comment Script - identifier.class.inc.php
 *
 * (C) Ralf Stadtaus http://www.gentlesource.com/
 */


//require_once 'database.class.inc.php';




/**
 * Handle comments
 */
class c5t_identifier
{

    /**
     * Get identifier data
     *
     * @access public
     */
    function get($id)
    {
        $sql = "SELECT  identifier_id,
                        identifier_value,
                        identifier_name,
                        identifier_name AS page_title,
                        identifier_url,
                        identifier_url AS page_url,
                        identifier_allow_comment AS page_allow_comment,
                        identifier_moderate_comment

                FROM    " . C5T_IDENTIFIER_TABLE . "

                WHERE   identifier_id = ?";
        if ($db = c5t_database::query($sql, array($id))) {
            $res = $db->fetchRow();
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'error', $res->getBacktrace());
                system_debug::add_message('SQL Statement', $sql, 'error');
                return false;
            }

            if (sizeof($res) > 0) {
                if ($res['identifier_name'] != '') {
                    $res['identifier_output'] = $res['identifier_name'];
                } else {
                    $res['identifier_output'] = $res['identifier_value'];
                }
                return $res;
            }
        }

    }

    /**
     * Delete identifier
     *
     * @access public
     */
    function delete($id)
    {
        global $c5t;

        // Delete comments first
        $where = " comment_identifier_id = ?";
        $data = array($id);
        if ($res = c5t_database::delete(C5T_COMMENT_TABLE, $where, $data)) {
            // Delete identifer
            $where = " identifier_id = ?";
            $data = array($id);
            if ($res = c5t_database::delete(C5T_IDENTIFIER_TABLE, $where, $data)) {
                return true;
            }
        }
    }

    /**
     * Write identifier name to database
     *
     * @access public
     */
    function put($id)
    {
        global $c5t;

        // Write into identifier table
        $data = array('identifier_name' => $c5t['_post']['identifier_name']);
        array_walk($data, 'c5t_entity_input');
        $where = "identifier_id = ?";
        $where_data = array($id);
        if (c5t_database::update('identifier', $data, $where, $where_data)) {
            return true;
        }
    }

    /**
     * Add rating
     *
     * @access public
     */
    function add_rating($identifier_id, $rating_value)
    {
        if ((int) $rating_value <= 0) {
            return false;
        }

        global $c5t;

        $sql = "UPDATE  " . C5T_IDENTIFIER_TABLE . "
                SET     identifier_rating_number = identifier_rating_number + 1,
                        identifier_rating_value = identifier_rating_value + ?
                WHERE   identifier_id = ?";
        $values = array($rating_value, $identifier_id);
        if ($res = c5t_database::query($sql, $values)) {
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'system');
                return false;
            }
            return $res;
        }
    }

    /**
     * Retract rating
     *
     * @access public
     */
    function retract_rating($identifier_id, $rating_value)
    {
        if ((int) $rating_value <= 0) {
            return false;
        }
        global $c5t;

        $sql = "UPDATE  " . C5T_IDENTIFIER_TABLE . "
                SET     identifier_rating_number = identifier_rating_number - 1,
                        identifier_rating_value = identifier_rating_value - ?
                WHERE   identifier_id = ?";
        $values = array($rating_value, $identifier_id);
        if ($res = c5t_database::query($sql, $values)) {
            if (PEAR::isError($res)) {
                system_debug::add_message($res->getMessage(), $res->getDebugInfo(), 'system');
                return false;
            }
            return $res;
        }
    }

    /**
     * Change identifier
     *
     * @access public
     */
    function change($id)
    {
        global $c5t;

        $url = parse_url($c5t['_post']['identifier']);

        $identifier = $url['path'];
        if (isset($url['query'])) {
             $identifier .= '?' . $url['query'];
        }

        $data = array(
            'identifier_value' => $identifier,
            'identifier_hash'  => md5($identifier),
            'identifier_url'  => $c5t['_post']['identifier']
            );

        $where = "identifier_id = ?";
        $where_data = array($id);
        if (c5t_database::update('identifier', $data, $where, $where_data)) {
            return true;
        }
    }




}
