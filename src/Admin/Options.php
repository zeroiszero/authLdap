<?php
/**
 * Copyright (c) Andreas Heigl<andreas@heigl.org>
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
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

namespace Org_Heigl\Wp\AuthLdap\Admin;

class Options
{
    protected $option;

    public function __construct(\Org_Heigl\Wp\AuthLdap\Options $options)
    {
        $this->option = $options;
    }

    public function get_post($name, $default = '')
    {
        return isset($_POST[$name]) ? $_POST[$name] : $default;
    }

    public function init()
    {
        add_settings_section('authldap_main', 'Main', function () {
            echo 'Main Settings';
        }, 'authldap_main');
        add_settings_section('authldap_authenticate', 'Authenticate',
            function () {
                echo 'Define your authentication settings here.';
            }, 'authldap_authenticate');
        add_settings_section('authldap_authorize', 'Authorize', function () {
            echo 'Define your authorization settings here.';
        }, 'authldap_authorize');
        add_settings_section('authldap_advanced', 'Advanced', function () {
            echo 'Some advanced settings';
        }, 'authldap_advanced');

        /* @var \Org_Heigl\Wp\AuthLdap\Option\AbstractOption $option */
        foreach ($this->option as $option) {
            register_setting('authldap_' . $option->getSection(), $option->getName());
            add_settings_field(
                $option->getName(),
                $option->getLabel(),
                function () use ($option) {
                    echo $option->getFormField() . '<p class="description">' . implode('</p><p class="description">', $option->getDescription()) . '</p>';
                },
                'authldap_' . $option->getSection(),
                'authldap_' . $option->getSection()
            );
        }
//        register_setting('authldap_main', 'authLdap_enabled');
//        register_setting('authldap_main', 'authLdap_groupEnable');
//        register_setting('authldap_main', 'authLdap_defaultRole');
//        register_setting('authldap_main', 'authLdap_uri');
//        register_setting('authldap_advanced', 'authLdap_cachePw');
//        register_setting('authldap_advanced', 'authLdap_debug');
//        register_setting('authldap_authenticate', 'authLdap_filter');
//        register_setting('authldap_authenticate', 'authLdap_attr_name');
//        register_setting('authldap_authenticate', 'authLdap_attr_secName');
//        register_setting('authldap_authenticate', 'authLdap_attr_uid');
//        register_setting('authldap_authenticate', 'authLdap_attr_mail');
//        register_setting('authldap_authenticate', 'authLdap_attr_web');
//        register_setting('authldap_authorize', 'authLdap_groupFilter');
//        register_setting('authldap_authorize', 'authLdap_attr_group');
//        register_setting('authldap_authorize', 'authLdap_groupSeparator');
//        register_setting('authldap_authorize', 'authLdap_groupOverUser');
//        register_setting('authldap_authorize', 'authLdap_groups');
//
//        add_settings_field('authLdap_enabled', 'Enable Authentication via LDAP',
//            function () {
//                $setting = (bool) get_option('authLdap_enabled');
//                echo sprintf(
//                    '<input type="checkbox" name="authLdap_enabled" %s value="1"/>',
//                    ($setting ? ' checked="checked"' : '')
//                );
//            }, 'authldap_main', 'authldap_main', ['Foo bar']);
//
//        add_settings_field('authLdap_groupEnable', 'Use the LDAP-Groups to set roles',
//            function () {
//                $setting = (bool) get_option('authLdap_groupEnable');
//                echo sprintf(
//                    '<input type="checkbox" name="authLdap_groupEnable" %s value="1"/>',
//                    ($setting ? ' checked="checked"' : '')
//                );
//            }, 'authldap_main', 'authldap_main', ['Foo bar']);
//
//        add_settings_field('authLdap_defaultRole', 'What role shall be assigned to newly created users',
//            function () {
//                $setting = get_option('authLdap_defaultRole');
//                $roles = [
//                    '<option value=""' . ($setting == ''?' selected="selected"':'') . '>None (deactivate)</option>',
//                ];
//                $allRoles = new \WP_Roles();
//                foreach ($allRoles->get_names() as $role => $values) {
//                    $is_selected = '';
//                    if ($setting == $role) {
//                        $is_selected = ' selected="selected"';
//                    }
//                    $roles[] = sprintf(
//                        '<option value="%1$s"%2$s>%3$s</option>',
//                        $role,
//                        $is_selected,
//                        $values
//                    );
//                }
//                echo sprintf(
//                    '<select name="authLdap_defaultRole" size="1"/>%1$s</select>',
//                    implode("\n", $roles)
//                );
//            }, 'authldap_main', 'authldap_main', ['Foo bar']);
//
//        add_settings_field('authLdap_uri', 'LDAP-Uri',
//            function () {
//                $setting = get_option('authLdap_uri');
//                echo sprintf(
//                    '<input type="text" name="authLdap_uri" value="%1$s" size="80" placeholder="ldaps://dn=admin,dc=example,dc=com:password@ldap.example.com/dc=example,dc=com"/>
//                       <p>The <abbr title="Uniform Ressource Identifier">URI</abbr>
//                            for connecting to the LDAP-Server. This usualy takes the form
//                            <var>&lt;scheme&gt;://&lt;user&gt;:&lt;password&gt;@&lt;server&gt;/&lt;path&gt;</var>
//                            according to RFC 1738.</p>
//                       <p class="description">
//                            In this case it schould be something like
//                            <var>ldap://uid=adminuser,dc=example,c=com:secret@ldap.example.com/dc=basePath,dc=example,c=com</var>.
//                       </p>
//                       <p class="description">
//                            If your LDAP accepts anonymous login, you can ommit the user and
//                            password-Part of the URI
//                       </p>
//                       <p class="description">
//                            scheme can be one of "ldap", "ldaps" or "ldap-tls" (which will start a TLS-session)
//                       </p>
//                    ',
//                    $setting
//                );
//            }, 'authldap_main', 'authldap_main');
//
//        add_settings_field('authLdap_filter', 'User-Filter',
//            function () {
//                $setting = get_option('authLdap_filter');
//                echo sprintf(
//                    '<input type="text" name="authLdap_filter" value="%1$s" size="80" placeholder="(uid=%%s)"/>
//                    <p class="description">
//                            Please provide a valid filter that can be used for querying the
//                            <abbr title="Lightweight Directory Access Protocol">LDAP</abbr>
//                            for the correct user. For more information on this
//                            feature have a look at <a href="http://andreas.heigl.org/cat/dev/wp/authldap">http://andreas.heigl.org/cat/dev/wp/authldap</a>
//                        </p>
//                        <p class="description">
//                            This field <strong>should</strong> include the string <code>%%s</code>
//                            that will be replaced with the username provided during log-in
//                        </p>
//                        <p class="description">
//                            If you leave this field empty it defaults to <strong>(uid=%%s)</strong>
//                        </p>
//                    ',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_attr_name', 'Name-Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_name');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_name" value="%1$s" size="80" placeholder="name"/>
//                    <p class="description">
//                        Which Attribute from the LDAP contains the Full or the First name
//                        of the user trying to log in.
//                    </p>
//                    <p class="description">
//                        This defaults to <strong>name</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_attr_secName', 'Second Name Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_secName');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_secName" value="%1$s" size="80" placeholder=""/>
//                    <p class="description">
//                        If the above Name-Attribute only contains the First Name of the
//                        user you can here specify an Attribute that contains the second name.
//                    </p>
//                    <p class="description">
//                        This field is empty by default
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_attr_uid', 'User-ID Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_uid');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_uid" value="%1$s" size="80" placeholder="uid"/>
//                    <p class="description">
//                    Please give the Attribute, that is used to identify the user. This
//                        should be the same as you used in the above <em>Filter</em>-Option
//                    </p>
//                    <p class="description">
//                        This field defaults to <strong>uid</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_attr_mail', 'Mail Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_mail');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_mail" value="%1$s" size="80" placeholder="mail"/>
//                    <p class="description">
//                        Which Attribute holds the eMail-Address of the user?
//                    </p>
//                    <p class="description">
//                        If more than one eMail-Address are stored in the LDAP, only the first given is used
//                    </p>
//                    <p class="description">
//                        This field defaults to <strong>mail</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_attr_web', 'Web Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_web');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_web" value="%1$s" size="80" placeholder=""/>
//                    <p class="description">
//                        If your users have a personal page (URI) stored in the LDAP, it can
//                            be provided here.
//                        </p>
//                        <p class="description">
//                            This field is empty by default
//                        </p>',
//                    $setting
//                );
//            }, 'authldap_authenticate', 'authldap_authenticate');
//
//        add_settings_field('authLdap_groupFilter', 'Group Filter',
//            function () {
//                $setting = get_option('authLdap_groupFilter');
//                echo sprintf(
//                    '<input type="text" name="authLdap_groupFilter" value="%1$s" size="80" placeholder="(&(objectClass=posixGroup)(memberUid=%%s))"/>
//                    <p class="description">
//                        Here you can add the filter for selecting groups for ther
//                        currentlly logged in user
//                    </p>
//                    <p class="description">
//                        The Filter should contain the string <code>%%s</code> which will be replaced by
//                        the login-name of the currently logged in user
//                    </p>
//                    <p class="description">
//                        Alternatively the string <code>%%dn%%</code> will be replaced by the
//                        DN of the currently logged in user. This can be helpfull if
//                        group-memberships are defined with DNs rather than UIDs
//                    </p>
//                    <p class="description">This field defaults to
//                        <strong>(&amp;(objectClass=posixGroup)(memberUid=%%s))</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authorize', 'authldap_authorize');
//
//        add_settings_field('authLdap_attr_group', 'Group-Attribute',
//            function () {
//                $setting = get_option('authLdap_attr_group');
//                echo sprintf(
//                    '<input type="text" name="authLdap_attr_group" value="%1$s" size="80" placeholder="gidNumber"/>
//                    <p class="description">
//                        This is the attribute that defines the Group-ID that can be matched
//                        against the Groups defined further down
//                    </p>
//                    <p class="description">
//                        This field defaults to <strong>gidNumber</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authorize', 'authldap_authorize');
//
//        add_settings_field('authLdap_groupSeparator', 'Group-Separator',
//            function () {
//                $setting = get_option('authLdap_groupSeparator');
//                echo sprintf(
//                    '<input type="text" name="authLdap_groupSeparator" value="%1$s" size="80" placeholder=","/>
//                    <p class="description">
//                        This attribute defines the separator used for the Group-IDs listed in the
//                        Groups defined further down. This is useful if the value of Group-Attribute
//                        listed above can contain a comma (for example, when using the memberof attribute)
//                    </p>
//                    <p class="description">
//                        This field defaults to <strong>, (comma)</strong>
//                    </p>',
//                    $setting
//                );
//            }, 'authldap_authorize', 'authldap_authorize');
//
//        add_settings_field('authLdap_groupOverUser', 'LDAP Groups override role of existing users?',
//            function () {
//                $setting = (bool) get_option('authLdap_groupOverUser');
//                echo sprintf(
//                    '<input type="hidden" name="authLdap_groupOverUser" value="0"/>
//                    <input type="checkbox" name="authLdap_groupOverUser" value="1"%1$s/>
//                    <p class="description">
//                        If role determined by LDAP Group differs from existing Wordpress User\'s role, use LDAP Group.
//                    </p>',
//                    (($setting)?' checked="checked"':'')
//                );
//            }, 'authldap_authorize', 'authldap_authorize');

    }

    public function options_panel()
    { $this->init();
        $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'main';

        if (! in_Array($active_tab, ['main', 'authenticate', 'authorize', 'advanced'])) {
            $active_tab = 'main';
        }
        echo '<div class="wrap">
            <h2>authLDAP-Options</h2>'
                . settings_errors()
               . '<h2 class="nav-tab-wrapper">  
            <a href="?page=authldap&tab=main" class="nav-tab ' . (($active_tab == 'main')?'nav-tab-active' :'') . '">Main</a>  
            <a href="?page=authldap&tab=authenticate" class="nav-tab ' . (($active_tab == 'authenticate')?'nav-tab-active' :'') . '">Authenticate</a>   
            <a href="?page=authldap&tab=authorize" class="nav-tab ' . (($active_tab == 'authorize')?'nav-tab-active' :'') . '">Authorize</a>   
            <a href="?page=authldap&tab=advanced" class="nav-tab ' . (($active_tab == 'advanced')?'nav-tab-active' :'') . '">Advanced</a>   
        </h2>  
                <form action="options.php" method="POST">';

        settings_fields('authldap_' . $active_tab);
        do_settings_sections('authldap_' . $active_tab);
        submit_button();

        echo '</form>
            </div>';
        return;
        // inclusde style sheet
        wp_enqueue_style('authLdap-style',
            plugin_dir_url(__FILE__) . 'authLdap.css');

        if (($_SERVER['REQUEST_METHOD'] == 'POST') && array_key_exists('ldapOptionsSave',
                $_POST)
        ) {
            $this->option->set('enabled',        $this->get_post('authLDAPAuth', false));
            $this->option->set('cachePW',        $this->get_post('authLDAPCachePW', false));
            $this->option->set('uRI',            $this->get_post('authLDAPURI'));
            $this->option->set('startTLS',       $this->get_post('authLDAPStartTLS', false));
            $this->option->set('filter',         $this->get_post('authLDAPFilter'));
            $this->option->set('nameAttr',       $this->get_post('authLDAPNameAttr'));
            $this->option->set('secName',        $this->get_post('authLDAPSecName'));
            $this->option->set('uidAttr',        $this->get_post('authLDAPUidAttr'));
            $this->option->set('mailAttr',       $this->get_post('authLDAPMailAttr'));
            $this->option->set('webAttr',        $this->get_post('authLDAPWebAttr'));
            $this->option->set('groups',         $this->get_post('authLDAPGroups', array()));
            $this->option->set('groupSeparator', $this->get_post('authLDAPGroupSeparator', ','));
            $this->option->set('debug',          $this->get_post('authLDAPDebug', false));
            $this->option->set('groupAttr',      $this->get_post('authLDAPGroupAttr'));
            $this->option->set('groupFilter',    $this->get_post('authLDAPGroupFilter'));
            $this->option->set('defaultRole',    $this->get_post('authLDAPDefaultRole'));
            $this->option->set('groupEnable',    $this->get_post('authLDAPGroupEnable', false));
            $this->option->set('groupOverUser',  $this->get_post('authLDAPGroupOverUser', false));

            try {
                $this->option->store();
                echo "<div class='updated'><p>Saved Options!</p></div>";
            } catch (\Exception $e) {
                echo "<div class='error'><p>Could not save Options!</p></div>";
            }
        }

        // Do some initialization for the admin-view
        $authLDAP               = $this->option->get('Enabled');
        $authLDAPCachePW        = $this->option->get('CachePW');
        $authLDAPURI            = $this->option->get('URI');
        $authLDAPStartTLS       = $this->option->get('StartTLS');
        $authLDAPFilter         = $this->option->get('Filter');
        $authLDAPNameAttr       = $this->option->get('NameAttr');
        $authLDAPSecName        = $this->option->get('SecName');
        $authLDAPMailAttr       = $this->option->get('MailAttr');
        $authLDAPUidAttr        = $this->option->get('UidAttr');
        $authLDAPWebAttr        = $this->option->get('WebAttr');
        $authLDAPGroups         = $this->option->get('Groups');
        $authLDAPGroupSeparator = $this->option->get('GroupSeparator');
        $authLDAPDebug          = $this->option->get('Debug');
        $authLDAPGroupAttr      = $this->option->get('GroupAttr');
        $authLDAPGroupFilter    = $this->option->get('GroupFilter');
        $authLDAPDefaultRole    = $this->option->get('DefaultRole');
        $authLDAPGroupEnable    = $this->option->get('GroupEnable');
        $authLDAPGroupOverUser  = $this->option->get('GroupOverUser');

        $tChecked              = ($authLDAP) ? ' checked="checked"' : '';
        $tDebugChecked         = ($authLDAPDebug) ? ' checked="checked"' : '';
        $tPWChecked            = ($authLDAPCachePW) ? ' checked="checked"' : '';
        $tGroupChecked         = ($authLDAPGroupEnable) ? ' checked="checked"' : '';
        $tGroupOverUserChecked = ($authLDAPGroupOverUser) ? ' checked="checked"' : '';
        $tStartTLSChecked      = ($authLDAPStartTLS) ? ' checked="checked"' : '';

        $roles = new \WP_Roles();

        $action = $_SERVER['REQUEST_URI'];
        if (! extension_loaded('ldap')) {
            echo '<div class="warning">The LDAP-Extension is not available on your '
                 . 'WebServer. Therefore Everything you can alter here does not '
                 . 'make any sense!</div>';
        }

        include dirname(__FILE__) . '/../../view/admin.phtml';
    }
}
