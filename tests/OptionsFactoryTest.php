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
 * @since     06.09.2016
 * @link      http://github.com/heiglandreas/authLDAP
 */

namespace Org_Heigl\Wp\AuthLdapTest;

use Brain\Monkey\Functions;
use Org_Heigl\Wp\AuthLdap\OptionFactory;

class OptionsFactoryTest extends MonkeyTestCase
{
    public function testSimpleFactoryInvocation()
    {
        Functions::expect('get_option')->once()->andReturn([
            'Version' => 1
        ]);

        $factory = new OptionFactory();

        $options = $factory->getOptionObject();
        $this->assertInstanceOf('Org_Heigl\\Wp\\AuthLdap\\Options', $options);

        $this->assertAttributeEquals(['version' => 1], 'options', $options);
    }

    public function testOptionConversionV1ToV2()
    {
        Functions::expect('get_option')->withArgs(['authLDAPOptions', []])->andReturn([]);
        Functions::expect('get_option')->withArgs(['authLDAP', null])->andReturn(true);
        Functions::expect('get_option')->withArgs(['authLDAPCachePW', null])->andReturn(true);
        Functions::expect('get_option')->withArgs(['authLDAPURI', null])->andReturn('ldap:test:test@ldap.example.com/dc=example,dc=com');
        Functions::expect('get_option')->withArgs(['authLDAPFilter', null])->andReturn('uid=%s');
        Functions::expect('get_option')->withArgs(['authLDAPNameAttr', null])->andReturn('cn');
        Functions::expect('get_option')->withArgs(['authLDAPSecName', null])->andReturn('');
        Functions::expect('get_option')->withArgs(['authLDAPUidAttr', null])->andReturn('uid');
        Functions::expect('get_option')->withArgs(['authLDAPMailAttr', null])->andReturn('mail');
        Functions::expect('get_option')->withArgs(['authLDAPWebAttr', null])->andReturn('web');
        Functions::expect('get_option')->withArgs(['authLDAPGroups', null])->andReturn([]);
        Functions::expect('get_option')->withArgs(['authLDAPDebug', null])->andReturn(true);
        Functions::expect('get_option')->withArgs(['authLDAPGroupAttr', null])->andReturn('cn');
        Functions::expect('get_option')->withArgs(['authLDAPGroupFilter', null])->andReturn('cn=%s');
        Functions::expect('get_option')->withArgs(['authLDAPDefaultRole', null])->andReturn('supporter');
        Functions::expect('get_option')->withArgs(['authLDAPGroupEnable', null])->andReturn(true);
        Functions::expect('get_option')->withArgs(['authLDAPGroupOverUser', null])->andReturn(true);

        Functions::expect('delete_option')->once()->withArgs(['authLDAP']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPCachePW']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPURI']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPFilter']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPNameAttr']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPSecName']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPUidAttr']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPMailAttr']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPWebAttr']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPGroups']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPDebug']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPGroupAttr']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPGroupFilter']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPDefaultRole']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPGroupEnable']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPGroupOverUser']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPCookieMarker']);
        Functions::expect('delete_option')->once()->withArgs(['authLDAPCookierMarker']);

        Functions::expect('update_option')->once()->withArgs([
            'authLDAPOptions',
            [ 'Enabled'       => true,
              'CachePW'       => true,
              'URI'           => 'ldap:test:test@ldap.example.com/dc=example,dc=com',
              'Filter'        => 'uid=%s',
              'NameAttr'      => 'cn',
              'SecName'       => '',
              'UidAttr'       => 'uid',
              'MailAttr'      => 'mail',
              'WebAttr'       => 'web',
              'Groups'        => array(),
              'Debug'         => true,
              'GroupAttr'     => 'cn',
              'GroupFilter'   => 'cn=%s',
              'DefaultRole'   => 'supporter',
              'GroupEnable'   => true,
              'GroupOverUser' => true,
              'Version'       => 1,
            ]
        ]);

        $factory = new OptionFactory();

        $options = $factory->getOptionObject();
        $this->assertInstanceOf('Org_Heigl\\Wp\\AuthLdap\\Options', $options);

        $this->assertAttributeEquals([
            'version' => 1,
            'enabled' => true,
            'cachePW' => true,
            'uRI' => 'ldap:test:test@ldap.example.com/dc=example,dc=com',
            'filter' => 'uid=%s',
            'nameAttr' => 'cn',
            'secName' => '',
            'uidAttr' => 'uid',
            'mailAttr' => 'mail',
            'webAttr' => 'web',
            'groups' => Array (),
            'debug' => true,
            'groupAttr' => 'cn',
            'groupFilter' => 'cn=%s',
            'defaultRole' => 'supporter',
            'groupEnable' => true,
            'groupOverUser' => true,
        ], 'options', $options);

    }
}
