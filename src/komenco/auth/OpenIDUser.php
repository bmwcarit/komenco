<?php namespace komenco\auth;
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

require_once __DIR__ .  '/../../..' . '/vendor/autoload.php';

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use database\UserQuery;

class OpenIDUser implements AdvancedUserInterface {
	private $username;
	private $firstname;
	private $lastname;
	private $email;
	private $enabled;
	private $accountNonExpired;
	private $credentialsNonExpired;
	private $accountNonLocked;
	private $roles;

	public function __construct($username) {
		$user = UserQuery::create()->findOneByKey($username);
		if ($user == null) {
			throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
		}

		$this->username = $user->getKey();
		$this->firstname = $user->getFirstName();
		$this->lastname = $user->getLastName();
		$this->email = $user->getEmail();
		$this->enabled = $user->getActive();
		$this->accountNonExpired = true;
		$this->credentialsNonExpired = true;
		$this->accountNonLocked = true;

		$roles = array();
		foreach ($user->getGroups() as $group) {
			array_push($roles, $group->getName());
		}
		$this->roles = array_unique($roles);
	}

	public function eraseCredentials() {
		// do nothing
	}

	public function getEmail() {
		return $this->email;
	}

	public function getPassword() {
		return NULL;
	}

	public function getRoles() {
		return $this->roles;
	}

	public function getSalt() {
		return NULL;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getFirstName() {
		return $this->firstname;
	}

	public function getLastName() {
		return $this->lastname;
	}

	public function getFullName() {
		return $this->getFirstName() . ' ' . $this->getLastName();
	}

	public function isAccountNonExpired() {
		return $this->accountNonExpired;
	}

	public function isAccountNonLocked() {
		return $this->accountNonLocked;
	}

	public function isCredentialsNonExpired() {
		return $this->credentialsNonExpired;
	}

	public function isEnabled() {
		return $this->enabled;
	}

}
