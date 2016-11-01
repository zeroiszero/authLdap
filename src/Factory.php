<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright Andreas Heigl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT-License
 * @since     04.09.2016
 * @link      http://github.com/heiglandreas/authLDAP
 */

namespace Org_Heigl\Wp\AuthLdap;

use Org_Heigl\Wp\AuthLdap\Admin\Options;

class Factory
{
    /**
     * @param      $user
     * @param      $username
     * @param      $password
     * @param bool $already_md5
     *
     * @return WP_User|WP_Error
     */
    public static function login($user, $username, $password, $already_md5 = false)
    {
        try {
            $result = false;
            $options  = (new OptionFactory())->getOptionObject();
            $debugger = new Debugger();
            $debugger->enable($options->get('debug'));
            $ldap   = LdapServerCollectionFactory::create($options, $debugger);
            $plugin = new AuthLdap($options, $ldap, $debugger);
            $plugin->init();
            $adminOpts = new Options($options);
            $adminOpts->init();
        } catch(\Exception $e) {
           return false;
        }
    }

    /**
     * This function disables the password-change fields in the users preferences.
     *
     * It does not make sense to authenticate via LDAP and then allow the user to
     * change the password only in the wordpress database. And changing the password
     * LDAP-wide can not be the scope of Wordpress!
     *
     * Whether the user is an LDAP-User or not is determined using the authLDAP-Flag
     * of the users meta-informations
     *
     * @param boolean $return
     * @param WP_User $user
     *
     * @conf boolean authLDAP
     * @return bool false, if the user whose prefs are viewed is an LDAP-User, true if
     * he isn't
     */
    public static function show_password_fields($return, $user)
    {
        if (! $user) {
            return true;
        }

        if (get_user_meta($user->ID, 'authLDAP')) {
            return false;
        }

        return $return;
    }

    /**
     * This function disables the password reset for a user.
     *
     * It does not make sense to authenticate via LDAP and then allow the user to
     * reset the password only in the wordpress database. And changing the password
     * LDAP-wide can not be the scope of Wordpress!
     *
     * Whether the user is an LDAP-User or not is determined using the authLDAP-Flag
     * of the users meta-informations
     *
     * @param bool $return
     * @param int  $userid The ID of the user whose password might be reset
     *
     * @author chaplina (https://github.com/chaplina)
     * @return false, if the user is an LDAP-User, true if he isn't
     */
    public static function allow_password_reset($return, $userid)
    {
        if (! (isset($userid))) {
            return true;
        }

        if (get_user_meta($userid, 'authLDAP')) {
            return false;
        }

        return $return;
    }

    /**
     * Do not send an email after changing the password or the email of the user!
     *
     * @param boolean $result      The initial resturn value
     * @param array   $user        The old userdata
     * @param array   $newUserData The changed userdata
     *
     * @return bool
     */
    public static function send_change_email($result, $user, $newUserData)
    {
        if (get_user_meta($user['ID'], 'authLDAP')) {
            return false;
        }

        return $result;
    }
}
