<?php
/**
 * Copyright (c)2013-2013 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
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
 * LIBILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  wdvCompass
 * @author    Andreas Heigl<andreas@heigl.org>
 * @copyright ©2013-2016 Andreas Heigl/wdv Gesellschaft für Medien & Kommunikation mbH & Co. OHG
 * @license   http://www.opesource.org/licenses/mit-license.php MIT-License
 * @version   0.0
 * @since     07.09.16
 */

namespace Org_Heigl\Wp\AuthLdapTest;

use Org_Heigl\Wp\AuthLdap\Debugger;
use Org_Heigl\Wp\AuthLdap\LdapServerCollectionFactory;
use Org_Heigl\Wp\AuthLdap\Options;
use phpmock\mockery\PHPMockery;

class LdapServerCollectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testFactoryWorks()
    {
        $option = \Mockery::mock(Options::class);
        $option->shouldReceive('get')->once()->withArgs(['URI'])->andReturn('ldap://test/foo,ldap://foo/test');
        $option->shouldReceive('get')->once()->withArgs(['URISeparator'])->andReturn(',');

        $debugger = \Mockery::mock(Debugger::class);

        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->twice()->withAnyArgs()->andReturnValues(['foo', 'bar']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->twice()->withAnyArgs();
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->twice()->withAnyArgs()->andReturn(true);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->twice()->withAnyArgs();

        $collection = LdapServerCollectionFactory::create($option, $debugger);

        $this->assertEquals(2, $collection->count());
    }

    public function testFactoryWorksWithUnknownSeparator()
    {
        $option = \Mockery::mock(Options::class);
        $option->shouldReceive('get')->once()->withArgs(['URI'])->andReturn('ldap://test/foo ldap://foo/test');
        $option->shouldReceive('get')->once()->withArgs(['URISeparator'])->andReturn(null);

        $debugger = \Mockery::mock(Debugger::class);

        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_connect')->twice()->withAnyArgs()->andReturnValues(['foo', 'bar']);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_set_option')->twice()->withAnyArgs();
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_bind')->twice()->withAnyArgs()->andReturn(true);
        PHPMockery::mock('Org_Heigl\\Wp\\AuthLdap', 'ldap_close')->twice()->withAnyArgs();

        $collection = LdapServerCollectionFactory::create($option, $debugger);

        $this->assertEquals(2, $collection->count());
    }
}
