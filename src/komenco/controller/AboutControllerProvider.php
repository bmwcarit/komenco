<?php namespace komenco\controller;
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

use Silex\Application;
use Silex\ControllerProviderInterface;

class AboutControllerProvider implements ControllerProviderInterface{
	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];

		$app['about.template'] = 'about.twig';
		$app['about.packages.file'] = './vendor/composer/installed.json';
		$app['about.composer.file'] = './composer.json';
		$app['about.version.file'] = './res-gen/version.json';

		$controllers->get('/', function (Application $app) {
			$packages = $this->loadJsonData($app, $app['about.packages.file']);
			$composer = $this->loadJsonData($app, $app['about.composer.file']);
			$version = $this->loadJsonData($app, $app['about.version.file']);
			return $app->render($app['about.template'],
								array('packages' => $packages,
									'composer' => $composer,
									'version' => $version));
		})->bind('about');

		return $controllers;
	}

	private function loadJsonData(Application $app, $fileName) {
		$data = array();
		if (file_exists($fileName)) {
			$file = file_get_contents($fileName);
			$data = json_decode($file);
		} else {
			$app->log('Could not find file at ' . $fileName);
		}

		return $data;
	}
}
