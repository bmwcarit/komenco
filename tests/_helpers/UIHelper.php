<?php namespace helpers;
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

use komenco\ConfiguredApplication;

class UIHelper extends \Codeception\Module {

	protected $app;
	protected $webDriver;

	public function _cleanup() {
		$this->app = new ConfiguredApplication();
		$this->app->boot();

		$this->webDriver = $this->getModule('WebDriver');
	}

	public function _before(\Codeception\TestCase $test) {
		parent::_before($test);

		/////////////////////////////////////
		// initialize your database here
		/////////////////////////////////////
	}

	public function login($username, $password, $checkText = 'Home') {
		$this->webDriver->amOnPage('/');
		$this->webDriver->seeInCurrentUrl('login');
		$this->webDriver->see('Welcome to ' . $this->app['config']['name']);
		$this->webDriver->click('Login');
		$this->webDriver->see('SIMPLEID');
		$this->webDriver->see('Secure login using HTTPS');
		$this->webDriver->fillField('name', $username);
		$this->webDriver->fillField('pass', $password);
		$this->webDriver->click('Log in');
		$this->webDriver->click('OK');
		$this->webDriver->see($checkText);
	}

	public function logout($username) {
		$this->webDriver->click($username);
		$this->webDriver->see('Profile');
		$this->webDriver->see('Logout');
		$this->webDriver->click('Logout');
		$this->webDriver->seeInCurrentUrl('login');
		$this->webDriver->see('Welcome to ' . $this->app['config']['name']);
	}
}
