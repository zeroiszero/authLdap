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

namespace Org_Heigl\Wp\AuthLdap;

class OptionFactory
{
    public function getOptionObject()
    {
        // the current version for options
        $option_version_plugin = 1;

        $options = get_option('authLDAPOptions', array());

        // check if option version has changed (or if it's there at all)
        if (! isset($options['Version']) || ($options['Version'] != $option_version_plugin)) {
            // defaults for all options
            $options_default = array(
                'Enabled'       => false,
                'CachePW'       => false,
                'URI'           => '',
                'Filter'        => '',
                // '(uid=%s)'
                'NameAttr'      => '',
                // 'name'
                'SecName'       => '',
                'UidAttr'       => '',
                // 'uid'
                'MailAttr'      => '',
                // 'mail'
                'WebAttr'       => '',
                'Groups'        => array(),
                'Debug'         => false,
                'GroupAttr'     => '',
                // 'gidNumber'
                'GroupFilter'   => '',
                // '(&(objectClass=posixGroup)(memberUid=%s))'
                'DefaultRole'   => '',
                'GroupEnable'   => true,
                'GroupOverUser' => true,
                'Version'       => $option_version_plugin,
            );

            // check if we got a version
            if (! isset($options['Version'])) {
                // we just changed to the new option format
                // read old options, then delete them
                $old_option_new_option = array(
                    'authLDAP'              => 'Enabled',
                    'authLDAPCachePW'       => 'CachePW',
                    'authLDAPURI'           => 'URI',
                    'authLDAPFilter'        => 'Filter',
                    'authLDAPNameAttr'      => 'NameAttr',
                    'authLDAPSecName'       => 'SecName',
                    'authLDAPUidAttr'       => 'UidAttr',
                    'authLDAPMailAttr'      => 'MailAttr',
                    'authLDAPWebAttr'       => 'WebAttr',
                    'authLDAPGroups'        => 'Groups',
                    'authLDAPDebug'         => 'Debug',
                    'authLDAPGroupAttr'     => 'GroupAttr',
                    'authLDAPGroupFilter'   => 'GroupFilter',
                    'authLDAPDefaultRole'   => 'DefaultRole',
                    'authLDAPGroupEnable'   => 'GroupEnable',
                    'authLDAPGroupOverUser' => 'GroupOverUser',
                );
                foreach ($old_option_new_option as $old_option => $new_option) {
                    $value = get_option($old_option, null);
                    if (! is_null($value)) {
                        $options[$new_option] = $value;
                    }
                    delete_option($old_option);
                }
                delete_option('authLDAPCookieMarker');
                delete_option('authLDAPCookierMarker');
            }

            // set default for all options that are missing
            foreach ($options_default as $key => $default) {
                if (! isset($options[$key])) {
                    $options[$key] = $default;
                }
            }

            // set new version and save
            $options['Version'] = $option_version_plugin;
            update_option('authLDAPOptions', $options);
        }

        $opts = new Options();

        $mapper = [
            'NameAttr' => 'Attr\\Name',
            'SecName'  => 'Attr\\SecName',
            'UidAttr'  => 'Attr\\Uid',
            'MailAttr' => 'Attr\\Mail',
            'WebAttr'  => 'Attr\\Web',
            'GroupAttr'=> 'Attr\\Group',
        ];
        foreach ($options as $key => $value) {
            $classPrefix = "\\Org_Heigl\\Wp\\AuthLdap\\Option\\";
            $className = implode('\\', array_map(function ($item) {
                return ucfirst($item);
            }, explode('_', $key)));

            if (isset($mapper[$className])) {
                $className = $mapper[$className];
            }

            $className = $classPrefix . $className;

            $opts->set($key, new $className($value));
        }

        return $opts;
    }
}
