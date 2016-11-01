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
 * @since     06.09.16
 */

namespace Org_Heigl\Wp\AuthLdapTest;

use Org_Heigl\Wp\AuthLdap\Factory;
use Brain\Monkey\Functions;

class FactoryTest extends MonkeyTestCase
{

    /**
     * @dataProvider showPasswordFieldsProvider
     */
    public function testShowPasswordFields($return, $default, $expect)
    {
        $user = new \stdClass();
        $user->ID = 0;

        Functions::expect('get_user_meta')->with(0, 'authLDAP')->once()->andReturn($return);

        $this->assertEquals($expect, Factory::show_password_fields($default, $user));
    }

    public function showPasswordFieldsProvider()
    {
        return [
            [true, true, false],
            [true, false, false],
            [false, false, false],
            [false, true, true],

        ];
    }

    /**
     * @dataProvider showPasswordREturnsTrueWithoutUserObjectProvider
     */
    public function testShowPasswordReturnsTrueWithoutUserObject($default, $user)
    {
        $this->assertTrue(Factory::show_password_fields($default, $user));
    }

    public function showPasswordREturnsTrueWithoutUserObjectProvider()
    {
        return [
            [true, null],
            [true, ''],
            [true, 0],
            [false, null],
            [false, ''],
            [false, 0],

        ];
    }



    /**
     * @dataProvider showPasswordFieldsProvider
     */
    public function testAllowPasswordReset($return, $default, $expect)
    {
        Functions::expect('get_user_meta')->with(0, 'authLDAP')->once()->andReturn($return);

        $this->assertEquals($expect, Factory::allow_password_reset($default, 0));
    }

    /**
     * @dataProvider allowPasswordResetReturnsTrueWithoutUserObjectProvider
     */
    public function testAllowPasswordResetReturnsTrueWithoutUserObject($default, $user)
    {
        $this->assertTrue(Factory::allow_password_reset($default, $user));
    }

    public function allowPasswordResetReturnsTrueWithoutUserObjectProvider()
    {
        return [
            [true, null],
            [false, null],
        ];
    }

    /**
     * @dataProvider showPasswordFieldsProvider
     */
    public function testSendChangeEmail($return, $default, $expect)
    {
        Functions::expect('get_user_meta')->with(0, 'authLDAP')->once()->andReturn($return);

        $this->assertEquals($expect, Factory::send_change_email($default, ['ID' => 0], null));
    }
}
