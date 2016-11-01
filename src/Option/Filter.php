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
 * @since     25.10.2016
 * @link      http://github.com/heiglandreas/authLDAP
 */

namespace Org_Heigl\Wp\AuthLdap\Option;

class Filter extends AbstractOption
{
    protected $section = self::AUTHENTICATE;

    protected $fieldtype = 'text';

    protected $label = 'User-Filter';

    protected $description = [
        'Please provide a valid filter that can be used for querying the <abbr title="Lightweight Directory Access Protocol">LDAP</abbr> for the correct user. For more information on this feature have a look at <a href="http://andreas.heigl.org/cat/dev/wp/authldap">http://andreas.heigl.org/cat/dev/wp/authldap</a>',
        'This field <strong>should</strong> include the string <code>%s</code> that will be replaced with the username provided during log-in',
        'If you leave this field empty it defaults to <strong>(uid=%s)</strong>',
    ];

    protected $placeholder = '(uid=%s)';
}
