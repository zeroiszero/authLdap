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

abstract class AbstractOption
{
    const MAIN = 'main';
    const AUTHENTICATE = 'authenticate';
    const AUTHORIZE = 'authorize';
    const ADVANCED  = 'advanced';

    protected $value;

    protected $section = '';

    protected $description = [];

    protected $fieldtype = 'text';

    protected $placeholder = '';

    protected $label = '';

    public function __construct($value)
    {
        $this->setValue($value);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getOptions()
    {
        return '';
    }

    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getFormField()
    {
        switch ($this->fieldtype) {
            case 'checkbox':
                return sprintf(
                    '<input type="hidden" name="%1$s" value="0"/><input type="checkbox" name="%1$s" value="1"%2$s/>',
                    $this->getName(),
                    (((bool) $this->getValue) ? ' checked="checked"' : '')
                );
            case 'select':
                return sprintf(
                    '<select name="%1$s" size="1">%2$s</select>',
                    $this->getName(),
                    $this->getOptions()
                );
            case 'text':
            default:
        }

        return sprintf(
            '<input type="text" name="%1$s" size="80" value="%2$s" placeholder="%3$s"/>',
            $this->getName(),
            $this->getValue(),
            $this->getPlaceholder()
        );

    }

    public function getName()
    {
        $classname = str_replace('Org_Heigl\\Wp\\AuthLdap\\Option\\', '', get_class($this));
        $classname = explode('\\', $classname);
        $classname = array_map(function ($entry) {
            return lcfirst($entry);
        }, $classname);

        return 'authldap_' . implode('_', $classname);
    }

    public function register()
    {
        register_setting('authldap_' . $this->getSection(), $this->getName());
    }

}
