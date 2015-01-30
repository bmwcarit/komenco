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
use Knp\Menu\Loader\ArrayLoader;
use Knp\Menu\Integration\Silex\KnpMenuServiceProvider;
use Knp\Menu\Matcher\Voter\RouteVoter;
use Knp\Menu\Matcher\Matcher;

class MenuProvider implements ServiceProviderInterface {
	public function boot(Application $app) {
		$app['compiled_menu'] = function($app) {
			return $app['menu']->getMenu();
		};

		$app['knp_menu.menus'] = array('main' =>'compiled_menu');
	}

	public function register(Application $app) {
		$app->register(new KnpMenuServiceProvider());
		$app['knp_menu.default_renderer'] = 'twig';
		$app['knp_menu.template'] = 'menu.twig';

		$app['route.voter'] = $app->share(function (Application $app) {
			$voter = new RouteVoter();
			$voter->setRequest($app['request']);

			return $voter;
		});

		$app['knp_menu.matcher.configure'] =
				$app->protect(function (Matcher $matcher) use ($app) {
					$matcher->addVoter($app['route.voter']);
				});

		$app['menu'] = new Menu($app['knp_menu.factory'],
							new ArrayLoader($app['knp_menu.factory']));
	}
}
