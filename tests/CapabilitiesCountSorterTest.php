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
 * @since     05.09.2016
 * @link      http://github.com/heiglandreas/authLDAP
 */

namespace Org_Heigl\Wp\AuthLdapTest;

use Org_Heigl\Wp\AuthLdap\CapabilitiesCountSorter;

class CapabilitiesCountSorterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $a
     * @param $b
     * @param $expected
     *
     * @dataProvider sorterProvider
     */
    public function testSorter($a, $b, $expected)
    {
        $sorter = new CapabilitiesCountSorter();
        $this->assertEquals($expected, $sorter($a, $b));
    }

    public function sorterProvider()
    {
        return [
            [['capabilities' => [1]], ['capabilities' => [1]], 0],
            [['capabilities' => [1,2]], ['capabilities' => [2]], -1],
            [['capabilities' => [1]], ['capabilities' => [1,2]], 1],
        ];
    }
}
