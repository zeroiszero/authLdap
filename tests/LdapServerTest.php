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

namespace Org_Heigl\Wp\AuthLdapTest;

use phpmock\mockery\PHPMockery;
use Org_Heigl\Wp\AuthLdap\LdapServer;

class LdapServerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testCreatingLdapServerObjectFailsWithUsernameOnly()
    {
        new LdapServer('scheme://user@server/path');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testCreatingLdapServerObjectFailsWithUnknownScheme()
    {
        new LdapServer('scheme://user:pass@server/path');
    }

    /** @dataProvider creatingLdapServerObjectProvider */
    public function testCreatingLdapServerObject($uri, $ldapUri, $user, $password, $baseDn, $tls)
    {
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->once()->withArgs([$ldapUri])->andReturn('foo');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->once()->withArgs(['foo', LDAP_OPT_PROTOCOL_VERSION, 3]);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->once()->with('foo');
        $args = ['foo'];
        if (null !== $user) {
            $args[] = $user;
            $args[] = $password;
        }
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->once()->withArgs($args)->andReturn(true);
        if ($tls) {
            PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_start_tls')->once()->with('foo');
        }

        $server = new LdapServer($uri);
        $this->assertAttributeEquals($user, 'defaultUser', $server);
        $this->assertAttributeEquals($password, 'defaultPassword', $server);
        $this->assertAttributeEquals('foo', 'connection', $server);
        $this->assertAttributeEquals($baseDn, 'baseDn', $server);
    }

    public function creatingLdapServerObjectProvider()
    {
        return [
            ['ldap://ldap.example.com/path', 'ldap://ldap.example.com', null, null, 'path', false],
            ['ldap://user:pass@ldap.example.com/path', 'ldap://ldap.example.com', 'user', 'pass', 'path', false],
            ['ldap://user%20:pa%20ss@ldap.example.com/path', 'ldap://ldap.example.com', 'user ', 'pa ss', 'path', false],
            ['ldaps://user%20:pa%20ss@ldap.example.com/path', 'ldaps://ldap.example.com', 'user ', 'pa ss', 'path', false],
            ['ldap://user%20:pa%20ss@ldap.example.com:369/path', 'ldap://ldap.example.com:369', 'user ', 'pa ss', 'path', false],
            ['ldap+tls://user%20:pa%20ss@ldap.example.com/path', 'ldap://ldap.example.com', 'user ', 'pa ss', 'path', true],
        ];
    }

    /** @expectedException \UnexpectedValueException */
    public function testBindingWithMissingPasword()
    {
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->once()->withArgs(['ldap://example.com'])->andReturn('foo');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->once()->withArgs(['foo', LDAP_OPT_PROTOCOL_VERSION, 3]);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->once()->withArgs(['foo']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->once()->withArgs(['foo'])->andReturn(true);

        $server = new LdapServer('ldap://example.com/dc=test');
        $server->bind('foo');
    }

    public function testSearchingREturnsNothing()
    {
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->once()->withArgs(['ldap://example.com'])->andReturn('foo');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->once()->withArgs(['foo', LDAP_OPT_PROTOCOL_VERSION, 3]);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->once()->withArgs(['foo']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->once()->withArgs(['foo'])->andReturn(true);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_search')->once()->withArgs(['foo', 'dc=test', 'filter', ['+']])->andReturn('bar');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_count_entries')->once()->withArgs(['foo', 'bar'])->andReturn(0);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_free_result')->once()->withArgs(['bar']);

        $server = new LdapServer('ldap://example.com/dc=test');
        $this->assertEquals([], $server->search('filter'));
    }

    public function testSearchingReturnsSingleEntry()
    {
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->once()->withArgs(['ldap://example.com'])->andReturn('foo');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->once()->withArgs(['foo', LDAP_OPT_PROTOCOL_VERSION, 3]);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->once()->withArgs(['foo']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->once()->withArgs(['foo'])->andReturn(true);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_search')->once()->withArgs(['foo', 'dc=test', 'filter', ['+']])->andReturn('bar');
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_count_entries')->once()->withArgs(['foo', 'bar'])->andReturn(1);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_get_entries')->once()->withArgs(['foo', 'bar'])->andReturn(['foo']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_free_result')->once()->withArgs(['bar']);

        $server = new LdapServer('ldap://example.com/dc=test');
        $this->assertEquals(['foo'], $server->search('filter'));

    }

}
