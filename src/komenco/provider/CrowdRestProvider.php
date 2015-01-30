<?php namespace komenco\provider;
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

use Silex\ServiceProviderInterface;
use Silex\Application;
use GuzzleHttp\Client;

class CrowdRestProvider implements ServiceProviderInterface {
	public function boot(Application $app) {}

	public function register(Application $app) {
		$app['crowd'] = $app->share(function ($app) {
			$client = new Client([
				'base_url' => [
					'https://crowd.bmw-carit.de/crowd/rest/usermanagement/{version}/',
					['version' => 'latest']
				],
				'debug' => $app['debug'],
				'defaults' => [
					'headers' => [
						'Content-Type' => 'application/json',
						'Accept' => 'application/json'
					],
					'auth' => ['starterkit', 'test'],
					'exceptions' => false,
				]
			]);

			return $client;
		});
	}
}
