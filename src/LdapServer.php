<?php
/**
 * Copyright (c) Andreas Heigl
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
 * LIBILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright ©2013-2016 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @since     06.09.16
 */

namespace Org_Heigl\Wp\AuthLdap;

class LdapServer
{
    protected $connection;

    protected $baseDn = null;

    protected $defaultUser = null;

    protected $defaultPassword;

    public function __construct($uri)
    {

        $parts = parse_url($uri);
        $tls = false;

        if (isset($parts['user']) && ! isset($parts['pass'])) {
            throw new \UnexpectedValueException('When providing a user there also needs to be a password');
        }

        if (! in_array($parts['scheme'], ['ldap', 'ldaps', 'ldap+tls'])) {
            throw new \UnexpectedValueException(sprintf(
                'The scheme "%1$s" is unknown',
                $parts['scheme']
            ));
        }

        if (isset($parts['user'])) {
            $this->defaultUser = urldecode($parts['user']);
        }
        if (isset($parts['pass'])) {
            $this->defaultPassword = urldecode($parts['pass']);
        }
        $this->baseDn = substr(urldecode($parts['path']), 1);

        if ($parts['scheme'] === 'ldap+tls') {
            $tls = true;
            $parts['scheme'] = 'ldap';
        }
        $ldapUri = $parts['scheme'] . '://' . $parts['host'];

        if (isset($parts['port'])) {
            $ldapUri .= ':' . $parts['port'];
        }

        $this->connection = ldap_connect($ldapUri);
        if (! $this->connection) {
            throw new \UnexpectedValueException(sprintf(
                'Could not validate %s',
                $ldapUri
            ));
        }
        if ($tls) {
            ldap_start_tls($this->connection);
        }
        ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);

        $success = $this->bind($this->defaultUser, $this->defaultPassword);
        if (! $success) {
            throw new \UnexpectedValueException(sprintf(
                'Could not bind to server with user %s',
                $this->defaultUser
            ));
        }
    }

    public function bind($dn = null, $password = null)
    {
        if ($dn && ! $password) {
            throw new \UnexpectedValueException('When providing a user there also needs to be a password');
        }

        if ($dn && $password) {
            return ldap_bind($this->connection, $dn, $password);
        }

        return ldap_bind($this->connection);
    }

    public function search($filter, $attributes = ['+'], $baseDn = null)
    {
        if (! $baseDn) {
            $baseDn = $this->baseDn;
        }

        $result = ldap_search($this->connection, $baseDn, $filter, $attributes);

        if (ldap_count_entries($this->connection, $result) < 1) {
            ldap_free_result($result);
            return [];
        }

        $values = ldap_get_entries($this->connection, $result);

        ldap_free_result($result);

        return $values;
    }

    public function __destruct()
    {
        if (null !== $this->connection) {
            ldap_close($this->connection);
        }
    }

}