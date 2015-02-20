<?php  namespace komenco\util\openid;
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

use SilexOpauth\Security\OpauthUserProviderInterface;
use SilexOpauth\Security\OpauthResult;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Silex\Application;
use database\UserQuery;
use database\GroupQuery;

class OpenIDUserProvider implements OpauthUserProviderInterface,
									UserProviderInterface {
	protected $app;

	public function __construct(Application $app) {
		$this->app = $app;
	}

	public function loadUserByOpauthResult(OpauthResult $result) {
		$username = $result->getNickname();

		$user = UserQuery::create()->filterByKey($username)
									->findOneOrCreate();
		if ($user->isNew()) {
			$this->createUser($user,
								$result->getProvider() . ':' .
									$result->getUid() . ':' . $result->getNickname(),
								$result->getEmail(),
								$result->getName());
		}

		return $this->loadUserByUsername($username);
	}

	private function createUser($user, $uid, $email, $name) {
		$user->setOpenid($uid);
		if(strpos($name, ' ')) {
			$user->setFirstname(explode(' ', $name)[0]);
			$user->setLastname(explode(' ', $name)[1]);
		} else {
			$user->setFirstname($name);
			$user->setLastname(' ');
		}
		$user->setEmail($email);
		$user->setActive(true);
		$this->addGroups($user);

		$user->save();
	}

	private function addGroups($user) {
		$user->addGroup($this->getGroup('ROLE_USER'));
	}

	private function getGroup($groupname) {
		return GroupQuery::create()->filterByName($groupname)
									->findOneOrCreate();
	}

	public function loadUserByUsername($username) {
		return new OpenIDUser($username);
	}

	public function refreshUser(UserInterface $user) {
		return $this->loadUserByUsername($user->getUsername());
	}

	public function supportsClass($class) {
		return $class === 'komenco\util\openid\OpenIDUser';
	}
}
