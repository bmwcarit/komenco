<?php namespace komenco;
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
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\RememberMeServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use SilexAssetic\AsseticServiceProvider;
use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Cache\FilesystemCache;
use Assetic\Filter\LessphpFilter;
use SilexOpauth\Security\OpauthSilexProvider;
use Propel\Silex\PropelServiceProvider;
use komenco\auth\OpenIDUserProvider;
use komenco\provider\CrowdRestProvider;
use komenco\provider\MenuProvider;
use komenco\ui\AboutProvider;
use komenco\ui\UserProfileControllerProvider;
use Igorw\Silex\ConfigServiceProvider;

class ConfiguredApplication extends Application {
	use Application\TwigTrait;
	use Application\SecurityTrait;
	use Application\UrlGeneratorTrait;
	use Application\MonologTrait;
	use Application\FormTrait;

	protected $basedir;

	public function __construct() {
		parent::__construct();

		$this->toRoot = '/../..';
		$this->basedir = __DIR__ . $this->toRoot;
		$this->appdir = '.';

		$this->loadConfiguration();
		$this['debug'] = $this['config']['debug'];

		$this->register(new TranslationServiceProvider(), array(
			'locale_fallbacks' => array($this['config']['locale']),
		));

		$this->register(new UrlGeneratorServiceProvider());
		$this->register(new CrowdRestProvider());

		$this->register(new MonologServiceProvider(), array(
			'monolog.logfile' => $this->appdir . '/' . $this['config']['logfile'],
		));

		$this->register(new PropelServiceProvider(), array(
			'propel.config_file' =>
						$this->appdir . '/src-gen/config/config.php'
		));

		$this->registerTwig();
		$this->registerSecurity();
		$this->registerAssetic();
		$this->register(new FormServiceProvider());
		$this->register(new MenuProvider());

		$this->mount('/about', new AboutProvider());
		$this->mount('/user_profile', new UserProfileControllerProvider());
	}

	private function loadConfiguration() {
		$this->register(new ConfigServiceProvider(__DIR__ . '/DefaultConfig.php',
													array(), null, 'config'));

		# load custom configuration from json
		if(getenv('APP_ENVIRONMENT')) {
			$env = getenv('APP_ENVIRONMENT');
			$this->register(new ConfigServiceProvider(
									$this->appdir . "/config/$env.json",
									array(),
									null,
									'config'));
		} else if(file_exists($this->appdir . '/config/default.json')) {
			$this->register(new ConfigServiceProvider(
									$this->appdir . "/config/default.json",
									array(),
									null,
									'config'));
		}

		# save openid server url in global variables
		$GLOBALS['openid_server_url'] = $this['config']['openid_server_url'];
	}

	private function registerAssetic() {
		$app = $this;

		$app->register(new AsseticServiceProvider());
		$app['assetic.path_to_web'] = $this->appdir . '/web';
		$app['assetic.options'] = array(
			'formulae_cache_dir' => $this->appdir . '/web/assetic/cache',
			'debug' => $app['debug'],
			'auto_dump_assets' => $app['debug']
		);

		$app['assetic.filter_manager'] = $app->share(
			$app->extend('assetic.filter_manager', function($fm, $app) {
				$fm->set('lessphp', new LessphpFilter(
					$this->appdir . '/vendor/bin/lessc'
				));

				return $fm;
			})
		);

		$app['assetic.asset_manager'] = $app->share(
			$app->extend('assetic.asset_manager', function($am, $app) {
				$am->set('styles', new AssetCache(
					new AssetCollection(
						array(
							new FileAsset($this->appdir .
									'/resources/styles/main.less')
						),
						array($app['assetic.filter_manager']->get('lessphp'))
					),
					new FilesystemCache(
							$app['assetic.options']['formulae_cache_dir'])
				));
				$am->get('styles')->setTargetPath('css/styles.css');

				$am->set('scripts', new AssetCache(
					new AssetCollection(array(
						new FileAsset($this->appdir .
								'/vendor/components/jquery/jquery.js'
						),
						new FileAsset($this->appdir .
								'/vendor/twbs/bootstrap/dist/js/bootstrap.js'
						),
						new FileAsset($this->appdir .
								'/vendor/nostalgiaz/bootstrap-switch/dist/js/bootstrap-switch.js'
						)
					)),
					new FilesystemCache($app['assetic.options']['formulae_cache_dir'])
				));
				$am->get('scripts')->setTargetPath('js/script.js');

				$logo = $this->basedir . '/' . $this['config']['logo'];
				if(file_exists($this->appdir . '/' . $this['config']['logo'])) {
					$logo = $this->appdir . '/' . $this['config']['logo'];
				}
				$am->set('logo', new FileAsset($logo));
				$am->get('logo')->setTargetPath('images/logo.png');

				$companylogo = $this->basedir . '/' . $this['config']['companylogo'];
				if(file_exists($this->appdir . '/' . $this['config']['companylogo'])) {
					$companylogo = $this->appdir . '/' . $this['config']['companylogo'];
				}
				$am->set('companylogo', new FileAsset($companylogo));
				$am->get('companylogo')->setTargetPath('images/companylogo.png');

				return $am;
			})
		);
	}

	private function registerTwig() {
		$app = $this;

		$this->register(new TwigServiceProvider(), array(
			'twig.path' => array (
				$this->appdir . '/views',
				$this->basedir . '/views',
			),
			'twig.options' => array(
				'cache' => false,
				'debug' => $app['debug'],
				'auto_reload' => true
			),
			'twig.form.templates' => array(
				'form/komenco_layout.twig'
			)
		));

		$this['twig'] = $this->share($this->extend('twig',
			function($twig, $app) {
				$twig->addGlobal('login_url', 'login/openid');
				$twig->addGlobal('app_conf', array(
					'name' => $this['config']['name'],
					'logo' => 'logo.png'
				));

				$twig->addFunction(new \Twig_SimpleFunction('user', 
					function() use ($app) {
							return $app['security']->getToken()->getUser();
					}
				));

				$twig->addFilter(new \Twig_SimpleFilter('href',
					function($string) {
						if (is_null($string)) {
							return '';
						}

						return '<a href="' . $string . '">' . $string . '</a>';
					}
				));

				return $twig;
			}
		));
	}

	private function registerSecurity() {
		$app = $this;

		$this->register(new SessionServiceProvider());
		$this->register(new OpauthSilexProvider(new OpenIDUserProvider()));
		$this->register(new SecurityServiceProvider());
		$this->register(new RememberMeServiceProvider());
		$this['security.firewalls'] = array(
			'login' => array(
				'pattern' => '^/login$',
			),
			'testing' => array(
				'pattern' => '^/dbtest$',
			),
			'default' => array(
				'pattern' => '^/',
				'opauth' => array(
					'opauth' => [
						'security_salt' => 'thisisnotneededforopenid',
						'callback_url' => 'opauth',
						'Strategy' => [
							'OpenID' => array(
								'identifier_form' => $this->basedir . '/openid_login.php'
							)
						]
					]
				),
				'logout' => array('logout_path' => '/logout'),
				'remember_me' => array(
					'key' => 'mApTKbC2G6W7UemqaMkYBd4w2KLwGbzuTcyrHbnknH657t5x',
					'lifetime' => 60*60*3,
					'always_remember_me' => true,
				),
				'users' => $this->share(function () use ($app) {
					return new OpenIDUserProvider($app);
				}),
			)
		);
	}
}
