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

use Org_Heigl\Wp\AuthLdap\LdapServer;
use Org_Heigl\Wp\AuthLdap\LdapServerCollection;

class LdapServerCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatingCollection()
    {
        $collection = new LdapServerCollection();

        $this->assertAttributeEquals([], 'collection', $collection);
    }

    public function testAddingToANdRemovingFromCollectionWorksAsExpected()
    {
        $collection = new LdapServerCollection();

        $server = \Mockery::mock(LdapServer::class);

        $collection->addServer($server);
        $this->assertAttributeEquals([$server], 'collection', $collection);
        $collection->removeServer($server);
        $this->assertAttributeEquals([], 'collection', $collection);

    }

    public function testADdingSameServerTwiceDoesntWork()
    {
        $collection = new LdapServerCollection();

        $server = \Mockery::mock(LdapServer::class);
        $server1 = \Mockery::mock(LdapServer::class);

        $collection->addServer($server);
        $this->assertAttributeEquals([$server], 'collection', $collection);
        $collection->addServer($server);
        $this->assertAttributeEquals([$server], 'collection', $collection);
        $collection->removeServer($server1);
        $this->assertAttributeEquals([$server], 'collection', $collection);
        $collection->removeServer($server);
        $this->assertAttributeEquals([], 'collection', $collection);


    }

    public function testIterator()
    {
        $collection = new LdapServerCollection();

        $server1 = \Mockery::mock(LdapServer::class);
        $server2 = \Mockery::mock(LdapServer::class);

        $collection->addServer($server1);
        $collection->addServer($server2);

        $this->assertEquals(2, $collection->count());

        $collection->rewind();
        $this->assertTrue($collection->valid());
        $this->assertSame($server1, $collection->current());
        $this->assertEquals(0, $collection->key());
        $collection->next();
        $this->assertTrue($collection->valid());
        $this->assertSame($server2, $collection->current());
        $this->assertEquals(1, $collection->key());
        $collection->next();
        $this->assertFalse($collection->valid());
    }


}
