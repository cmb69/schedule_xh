<?php

/**
 * The model class.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Schedule
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2012-2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Schedule_XH
 */

/**
 * The model class.
 *
 * @category CMSimple_XH
 * @package  Schedule
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Schedule_XH
 */
class Schedule_Model
{
    /**
     * Returns the name of currently logged in user; <var>false</var> if the
     * visitor is not logged in.
     *
     * @return string
     */
    public function currentUser()
    {
        if (session_id() == '') {
            session_start();
        }
        if (isset($_SESSION['username'])) {
            $user = $_SESSION['username'];
        } elseif (isset($_SESSION['Name'])) {
            $user = $_SESSION['Name'];
        } else {
            $user = null; // TODO: should be false
        }
        return $user;
    }

}

?>
