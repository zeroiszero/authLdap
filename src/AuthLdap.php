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

final class AuthLdap
{
    /** @var DebuggerInterface  */
    protected $debugger;

    /** @var OptionsInterface */
    protected $options;

    /** @var  LdapServerCollection */
    protected $ldaps;

    public function __construct(OptionsInterface $options, LdapServerCollection $ldaps, DebuggerInterface $debugger)
    {
        $this->debugger = $debugger;
        $this->options  = $options;
        $this->ldaps    = $ldaps;
    }

    /**
     * This method authenticates a user using either the LDAP or, if LDAP is not
     * available, the local database
     * For this we store the hashed passwords in the WP_Database to ensure working
     * conditions even without an LDAP-Connection
     *
     * @param null|WP_User|WP_Error
     * @param string  $username
     * @param string  $password
     * @param boolean $already_md5
     *
     * @return boolean true, if login was successfull or false, if it wasn't
     * @conf  boolean authLDAP true, if authLDAP should be used, false if not. Defaults to false
     * @conf  string authLDAPFilter LDAP filter to use to find correct user, defaults to '(uid=%s)'
     * @conf  string authLDAPNameAttr LDAP attribute containing user (display) name, defaults to 'name'
     * @conf  string authLDAPSecName LDAP attribute containing second name, defaults to ''
     * @conf  string authLDAPMailAttr LDAP attribute containing user e-mail, defaults to 'mail'
     * @conf  string authLDAPUidAttr LDAP attribute containing user id (the username we log on with), defaults to 'uid'
     * @conf  string authLDAPWebAttr LDAP attribute containing user website, defaults to ''
     * @conf  string authLDAPDefaultRole default role for authenticated user, defaults to ''
     * @conf  boolean authLDAPGroupEnable true, if we try to map LDAP groups to Wordpress roles
     * @conf  boolean authLDAPGroupOverUser true, if LDAP Groups have precedence over existing user roles
     */
    public function login($user, $username, $password, $already_md5 = false)
    {
        // don't do anything when authLDAP is disabled
        if (! $this->get_option('Enabled')) {
            $this->debugger->debug('LDAP disabled in AuthLDAP plugin options (use the first option in the AuthLDAP options to enable it)');

            return $user;
        }

        // If the user has already been authenticated (only in that case we get a
        // WP_User-Object as $user) we skip LDAP-authentication and simply return
        // the existing user-object
        if ($user instanceof WP_User) {
            $this->debugger->debug(sprintf(
                'User %s has already been authenticated - skipping LDAP-Authentication',
                $user->get('nickname')));

            return $user;
        }

        $this->debugger->debug("User '$username' logging in");

        if ($username == 'admin') {
            $this->debugger->debug('Doing nothing for possible local user admin');

            return $user;
        }

        global $wpdb, $error;
        try {
            $authLDAP              = $this->get_option('Enabled');
            $authLDAPFilter        = $this->get_option('Filter');
            $authLDAPNameAttr      = $this->get_option('NameAttr');
            $authLDAPSecName       = $this->get_option('SecName');
            $authLDAPMailAttr      = $this->get_option('MailAttr');
            $authLDAPUidAttr       = $this->get_option('UidAttr');
            $authLDAPWebAttr       = $this->get_option('WebAttr');
            $authLDAPDefaultRole   = $this->get_option('DefaultRole');
            $authLDAPGroupEnable   = $this->get_option('GroupEnable');
            $authLDAPGroupOverUser = $this->get_option('GroupOverUser');

            if (! $username) {
                $this->debugger->debug('Username not supplied: return false');

                return false;
            }

            if (! $password) {
                $this->debugger->debug('Password not supplied: return false');
                $error = __('<strong>Error</strong>: The password field is empty.');

                return false;
            }
            // First check for valid values and set appropriate defaults
            if (! $authLDAPFilter) {
                $authLDAPFilter = '(uid=%s)';
            }
            if (! $authLDAPNameAttr) {
                $authLDAPNameAttr = 'name';
            }
            if (! $authLDAPMailAttr) {
                $authLDAPMailAttr = 'mail';
            }
            if (! $authLDAPUidAttr) {
                $authLDAPUidAttr = 'uid';
            }

            // If already_md5 is TRUE, then we're getting the user/password from the cookie. As we don't want to store LDAP passwords in any
            // form, we've already replaced the password with the hashed username and LDAP_COOKIE_MARKER
            if ($already_md5) {
                if ($password == md5($username) . md5($ldapCookieMarker)) {
                    $this->debugger->debug('cookie authentication');

                    return true;
                }
            }

            // No cookie, so have to authenticate them via LDAP
            $result = false;
            try {
                $this->debugger->debug('about to do LDAP authentication');
                $result = $this->ldap->Authenticate($username,
                    $password, $authLDAPFilter);
            } catch (Exception $e) {
                $this->debugger->debug('LDAP authentication failed with exception: ' . $e->getMessage());

                return false;
            }

            // Rebind with the default credentials after the user has been loged in
            // Otherwise the credentials of the user trying to login will be used
            // This fixes #55
            $this->ldap->bind();

            if (true !== $result) {
                $this->debugger->debug('LDAP authentication failed');

                // TODO what to return? WP_User object, true, false, even an WP_Error object... all seem to fall back to normal wp user authentication
                return;
            }

            $this->debugger->debug('LDAP authentication successfull');
            $attributes = array_values(
                array_filter(
                    array(
                        $authLDAPNameAttr,
                        $authLDAPSecName,
                        $authLDAPMailAttr,
                        $authLDAPWebAttr,
                        $authLDAPUidAttr
                    )
                )
            );

            try {
                $attribs = $this->ldap->search(
                    sprintf($authLDAPFilter, $username),
                    $attributes
                );
                // First get all the relevant group informations so we can see if
                // whether have been changes in group association of the user
                if (! isset($attribs[0]['dn'])) {
                    $this->debugger->debug('could not get user attributes from LDAP');
                    throw new UnexpectedValueException('dn has not been returned');
                }
                if (! isset($attribs[0][strtolower($authLDAPUidAttr)][0])) {
                    $this->debugger->debug('could not get user attributes from LDAP');
                    throw new UnexpectedValueException('The user-ID attribute has not been returned');

                }

                $dn      = $attribs[0]['dn'];
                $realuid = $attribs[0][strtolower($authLDAPUidAttr)][0];
            } catch (Exception $e) {
                $this->debugger->debug('Exception getting LDAP user: ' . $e->getMessage());

                return false;
            }

            $uid  = $this->get_uid($realuid);
            $role = '';

            // we only need this if either LDAP groups are disabled or
            // if the WordPress role of the user overrides LDAP groups
            if (! $authLDAPGroupEnable || ! $authLDAPGroupOverUser) {
                $role = $this->user_role($uid);
            }

            // do LDAP group mapping if needed
            // (if LDAP groups override worpress user role, $role is still empty)
            if (empty($role) && $authLDAPGroupEnable) {
                $role = $this->groupmap($realuid, $dn);
                $this->debugger->debug('role from group mapping: ' . $role);
            }

            // if we don't have a role yet, use default role
            if (empty($role) && ! empty($authLDAPDefaultRole)) {
                $this->debugger->debug('no role yet, set default role');
                $role = $authLDAPDefaultRole;
            }

            if (empty($role)) {
                // Sorry, but you are not in any group that is allowed access
                trigger_error('no group found');
                $this->debugger->debug('user is not in any group that is allowed access');

                return false;
            } else {
                $roles = new WP_Roles();
                // not sure if this is needed, but it can't hurt
                if (! $roles->is_role($role)) {
                    trigger_error('no group found');
                    $this->debugger->debug('role is invalid');

                    return false;
                }
            }

            // from here on, the user has access!
            // now, lets update some user details
            $user_info               = array();
            $user_info['user_login'] = $realuid;
            $user_info['role']       = $role;
            $user_info['user_email'] = '';

            // first name
            if (isset($attribs[0][strtolower($authLDAPNameAttr)][0])) {
                $user_info['first_name'] = $attribs[0][strtolower($authLDAPNameAttr)][0];
            }

            // last name
            if (isset($attribs[0][strtolower($authLDAPSecName)][0])) {
                $user_info['last_name'] = $attribs[0][strtolower($authLDAPSecName)][0];
            }

            // mail address
            if (isset($attribs[0][strtolower($authLDAPMailAttr)][0])) {
                $user_info['user_email'] = $attribs[0][strtolower($authLDAPMailAttr)][0];
            }

            // website
            if (isset($attribs[0][strtolower($authLDAPWebAttr)][0])) {
                $user_info['user_url'] = $attribs[0][strtolower($authLDAPWebAttr)][0];
            }

            // display name, nickname, nicename
            if (array_key_exists('first_name', $user_info)) {
                $user_info['display_name']  = $user_info['first_name'];
                $user_info['nickname']      = $user_info['first_name'];
                $user_info['user_nicename'] = sanitize_title_with_dashes($user_info['first_name']);
                if (array_key_exists('last_name', $user_info)) {
                    $user_info['display_name'] .= ' ' . $user_info['last_name'];
                    $user_info['nickname'] .= ' ' . $user_info['last_name'];
                    $user_info['user_nicename'] .= '_' . sanitize_title_with_dashes($user_info['last_name']);
                }
            }

            // optionally store the password into the wordpress database
            if ($this->get_option('CachePW')) {
                // Password will be hashed inside wp_update_user or wp_insert_user
                $user_info['user_pass'] = $password;
            } else {
                // clear the password
                $user_info['user_pass'] = '';
            }

            // add uid if user exists
            if ($uid) {
                // found user in the database
                $this->debugger->debug('The LDAP user has an entry in the WP-Database');
                $user_info['ID'] = $uid;
                unset ($user_info['display_name'], $user_info['nickname']);
                $userid = wp_update_user($user_info);
            } else {
                // new wordpress account will be created
                $this->debugger->debug('The LDAP user does not have an entry in the WP-Database, a new WP account will be created');

                $userid = wp_insert_user($user_info);
            }

            // if the user exists, wp_insert_user will update the existing user record
            if (is_wp_error($userid)) {
                $this->debugger->debug('Error creating user : ' . $userid->get_error_message());
                trigger_error('Error creating user: ' . $userid->get_error_message());

                return $userid;
            }

            $this->debugger->debug('user id = ' . $userid);

            // flag the user as an ldap user so we can hide the password fields in the user profile
            update_user_meta($userid, 'authLDAP', true);

            // return a user object upon positive authorization
            return new WP_User($userid);
        } catch (Exception $e) {
            $this->debugger->debug($e->getMessage() . '. Exception thrown in line ' . $e->getLine());
            trigger_error($e->getMessage() . '. Exception thrown in line ' . $e->getLine());
        }
    }

    /**
     * Get user's user id
     * Returns null if username not found
     *
     * @param string $username username
     * @param        string    user id, null if not found
     */
    public function get_uid($username)
    {
        global $wpdb;

        // find out whether the user is already present in the database
        $uid = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->users} WHERE user_login = %s",
                $username
            )
        );
        if ($uid) {
            $this->debugger->debug("Existing user, uid = {$uid}");

            return $uid;
        } else {
            return null;
        }
    }

    /**
     * Get the user's current role
     * Returns empty string if not found.
     *
     * @param int $uid wordpress user id
     *
     * @return string role, empty if none found
     */
    public function user_role($uid)
    {
        global $wpdb;

        if (! $uid) {
            return '';
        }

        $meta_value = $wpdb->get_var("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}capabilities' AND user_id = {$uid}");

        if (! $meta_value) {
            return '';
        }

        $capabilities = unserialize($meta_value);
        $roles        = is_array($capabilities) ? array_keys($capabilities) : array('');
        $role         = $roles[0];

        $this->debugger->debug("Existing user's role: {$role}");

        return $role;
    }

    /**
     * Get LDAP groups for user and map to role
     *
     * @param string $username
     * @param string $dn
     *
     * @return string role, empty string if no mapping found, first found role otherwise
     * @conf array authLDAPGroups, associative array, role => ldap_group
     * @conf string authLDAPGroupAttr, ldap attribute that holds name of group
     * @conf string authLDAPGroupFilter, LDAP filter to find groups. can contain %s and %dn% placeholders
     */
    public function groupmap($username, $dn)
    {
        $authLDAPGroups         = $this->sort_roles_by_capabilities(
            $this->get_option('Groups')
        );
        $authLDAPGroupAttr      = $this->options->get('GroupAttr');
        $authLDAPGroupFilter    = $this->options->get('GroupFilter');
        $authLDAPGroupSeparator = $this->options->get('GroupSeparator');
        if (! $authLDAPGroupAttr) {
            $authLDAPGroupAttr = 'gidNumber';
        }
        if (! $authLDAPGroupFilter) {
            $authLDAPGroupFilter = '(&(objectClass=posixGroup)(memberUid=%s))';
        }
        if (! $authLDAPGroupSeparator) {
            $authLDAPGroupSeparator = ',';
        }

        if (! is_array($authLDAPGroups) || count(array_filter(array_values($authLDAPGroups))) == 0) {
            $this->debugger->debug('No group names defined');

            return '';
        }

        try {
            // To allow searches based on the DN instead of the uid, we replace the
            // string %dn% with the users DN.
            $authLDAPGroupFilter = str_replace('%dn%', $dn,
                $authLDAPGroupFilter);
            $this->debugger->debug('Group Filter: ' . json_encode($authLDAPGroupFilter));
            $groups = $this->ldap->search(sprintf($authLDAPGroupFilter,
                $username), array($authLDAPGroupAttr));
        } catch (Exception $e) {
            $this->debugger->debug('Exception getting LDAP group attributes: ' . $e->getMessage());

            return '';
        }

        $grp = array();
        for ($i = 0; $i < $groups ['count']; $i ++) {
            for ($k = 0; $k < $groups[$i][strtolower($authLDAPGroupAttr)]['count']; $k ++) {
                $grp[] = $groups[$i][strtolower($authLDAPGroupAttr)][$k];
            }
        }

        $this->debugger->debug('LDAP groups: ' . json_encode($grp));

        // Check whether the user is member of one of the groups that are
        // allowed acces to the blog. If the user is not member of one of
        // The groups throw her out! ;-)
        // If the user is member of more than one group only the first one
        // will be taken into account!

        $role = '';
        foreach ($authLDAPGroups as $key => $val) {
            $currentGroup = explode($authLDAPGroupSeparator, $val);
            // Remove whitespaces around the group-ID
            $currentGroup = array_map('trim', $currentGroup);
            if (0 < count(array_intersect($currentGroup, $grp))) {
                $role = $key;
                break;
            }
        }

        $this->debugger->debug("Role from LDAP group: {$role}");

        return $role;
    }

    /**
     * Sort the given roles by number of capabilities
     *
     * @param array $roles
     *
     * @return array
     */
    public function sort_roles_by_capabilities($roles)
    {
        global $wpdb;
        $myRoles = get_option($wpdb->get_blog_prefix() . 'user_roles');

        $this->debugger->debug(print_r($roles, true));
        $sorter = new CapabilitiesCountSorter();
        uasort($myRoles, $sorter);

        $return = array();

        foreach ($myRoles as $key => $role) {
            if (isset($roles[$key])) {
                $return[$key] = $roles[$key];
            }
        }

        $this->debugger->debug(print_r($return, true));

        return $return;
    }

    /**
     * Load AuthLDAP Options
     * Sets and stores defaults if options are not up to date
     */
    public function load_options($reload = false)
    {
     // Ausgegliedert nach Options
    }

    /**
     * Get an individual option
     */
    public function get_option($optionname)
    {
        $options = $this->load_options();
        if (isset($options[$optionname])) {
            return $options[$optionname];
        } else {
            $this->debugger->debug('option name invalid: ' . $optionname);

            return null;
        }
    }

    /**
     * Set new options
     */
    public function set_options($new_options = array())
    {
        // initialize the options with what we currently have
        $options = $this->load_options();

        // set the new options supplied
        foreach ($new_options as $key => $value) {
            $options[$key] = $value;
        }

        // store options
        if (update_option('authLDAPOptions', $options)) {
            // reload the option cache
            $this->load_options(true);

            return true;
        } else {
            // could not set options
            return false;
        }
    }
}