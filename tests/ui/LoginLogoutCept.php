<?php
/*
 * Copyright (C) 2015, BMW Car IT GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

$scenario->group('general');

$I = new UIGuy($scenario);
$I->amOnPage('/');
$I->expectTo('be on komenco login page.');
$I->seeInCurrentUrl('login');
$I->see('Welcome to komenco');

$I->amGoingTo('log in.');
$I->click('Login');

$I->expectTo('be on the SimpleID login page.');
$I->see('SIMPLEID 0.9.1');
$I->see('Secure login using HTTPS');

$I->amGoingTo('log in as Bob Schnitzler');
$I->fillField('name', 'bob');
$I->fillField('pass', 'bob');
$I->click('Log in');
$I->click('OK');

$I->expectTo('be on the Komenco home page.');
$I->see('Home');

$I->amGoingTo('logout');
$I->click('Bob');
$I->see('Profile');
$I->see('Logout');

$I->click('Logout');
$I->expectTo('be on komenco login page.');
$I->seeInCurrentUrl('login');
$I->see('Welcome to komenco');

?>
