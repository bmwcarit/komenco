<?php  namespace komenco\auth;
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

use SilexOpauth\Security\OpauthUserProviderInterface;
use SilexOpauth\Security\OpauthResult;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use database\UserQuery;
use database\GroupQuery;

class CrowdIDUserProvider implements OpauthUserProviderInterface,
									UserProviderInterface {
	protected $app;
	protected $crowd;

	public function __construct(Application $app) {
		$this->app = $app;
		$this->crowd = $app['crowd'];
	}
	
	public function loadUserByOpauthResult(OpauthResult $result) {
		$this->app['monolog']->addDebug(__METHOD__);
		$this->app['monolog']->addDebug($result->serialize());
		
		$username = $result->getNickname();
		
		$user = UserQuery::create()->filterByKey($username)
									->findOneOrCreate();
		if ($user->isNew()) {
			$this->createUser($user,
								$result->getProvider() . ':' .
									$result->getUid(),
								$result->getEmail());
		}
		
		return $this->loadUserByUsername($username);
	}

	private function createUser($user, $uid,  $email) {
		$response = $this->crowd->get('user',
										['query' => [
											'username' => $user->getKey()
										]]);
		
		if ($response->getStatusCode() == Response::HTTP_NOT_FOUND) {
			throw new UsernameNotFoundException(
									sprintf('Username "%s" does not exist.',
												$user->getKey()));
		} else if ($response->getStatusCode() == Response::HTTP_FORBIDDEN) {
			$this->app['monolog']->addDebug('Crowd denied access, please ' .
											'make sure the local server is ' .
											'allowed the access the crowd ' .
											'application ');
			throw new AuthenticationServiceException('Access was denied' .
														' by crowd');
		}

		$json = $response->json();

		$user->setOpenid($uid);
		$user->setFirstname($json['first-name']);
		$user->setLastname($json['last-name']);
		$user->setEmail($email);
		
		if($json['active'] == 'true')
			$user->setActive(true);

		$this->addGroups($user);
		$user->save();
	}
	
	private function addGroups($user) {
		$response = $this->crowd->get('group/user/nested',
							['query' => [
								'groupname' => 'crowd-administrators',
								'username' => $user->getKey()
							]]);
		
		if ($response->getStatusCode() == Response::HTTP_OK) {
			$user->addGroup($this->getGroup('ROLE_USER'));
			$user->addGroup($this->getGroup('ROLE_ADMIN'));
		}
	}
	
	private function getGroup($groupname) {
		return GroupQuery::create()->filterByName($groupname)
									->findOneOrCreate();
	}

	public function loadUserByUsername($username) {
		$this->app['monolog']->addDebug(__METHOD__);

		return new CrowdIDUser($username);
	}

	public function refreshUser(UserInterface $user) {
		$this->app['monolog']->addDebug(__METHOD__);

		return $this->loadUserByUsername($user->getUsername());
	}

	public function supportsClass($class) {
		$this->app['monolog']->addDebug(__METHOD__);
		return $class === 'komenco\auth\CrowdIDUser';
	}
}
