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

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

use Propel\Runtime\Propel;

use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

class SetupDatabaseExtension extends \Codeception\Platform\Extension {
	protected $propelApp;
	protected $propelOutput;

	public function __construct($config, $options) {
		parent::__construct($config, $options);

		$this->propelLoadApplication();
		$this->propelOutput = new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG);
	}

	static $events = array(
		'suite.before' => 'beforeSuite',
		'test.after' => 'afterTest'
	);

	function beforeSuite(\Codeception\Event\SuiteEvent $e) {
		// build sql
		$this->propelSqlBuild();

		// build the model
		$this->propelModelBuild();

		// create configuration
		$this->propelConfigConvert();

		// clear the databas sql
		$this->propelSqlInsert();
	}

	function afterTest(\Codeception\Event\TestEvent $e) {
		// clear the databas sql
		$this->propelSqlInsert();
	}

	protected function propelLoadApplication() {
		$finder = new Finder();
		$finder->files()->name('*.php')->in(__DIR__.'/../../vendor/propel/propel/src/Propel/Generator/Command')->depth(0);

		$this->propelApp = new Application('Propel', Propel::VERSION);

		foreach ($finder as $file) {
			$ns = '\\Propel\\Generator\\Command';
			$r  = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
			if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
				$this->propelApp->add($r->newInstance());
			}
		}
	}

	protected function propelSqlBuild() {
		$command = $this->propelApp->find('sql:build');
		$in = new ArrayInput(array(
			'command'		=> 'sql:build',
			'--verbose'		=> 'vvv',
			'--overwrite'	=> true,
		));
		$command->run($in, $this->propelOutput);
	}

	protected function propelModelBuild() {
		$command = $this->propelApp->find('model:build');
		$in = new ArrayInput(array(
			'command'		=> 'model:build',
			'--verbose'		=> 'vvv',
		));
		$command->run($in, $this->propelOutput);
	}

	protected function propelConfigConvert() {
		$command = $this->propelApp->find('config:convert');
		$in = new ArrayInput(array(
			'command'		=> 'config:convert',
			'--verbose'		=> 'vvv',
		));
		$command->run($in, $this->propelOutput);
	}

	protected function propelSqlInsert() {
		$command = $this->propelApp->find('sql:insert');
		$in = new ArrayInput(array(
			'command'		=> 'sql:insert',
			'--verbose'		=> 'vvv',
		));
		$command->run($in, $this->propelOutput);
	}
}

?>
