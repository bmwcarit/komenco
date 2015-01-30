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

class Menu {
	protected $itemFactory;
	protected $menu = array();
	protected $raw_menu = array();
	protected $menuLoader;
	protected $config;
	protected $defaults = array(
		'root' => array(
			'childrenAttributes' => array(
				'class' => 'nav navbar-nav'
			)
		),
		'submenu' => array(
			'attributes' => array(
				'class' => 'dropdown'
			),
			'linkAttributes' => array(
				'class' => 'dropdown-toggle',
				'data-toggle' => 'dropdown'
			),
			'childrenAttributes' => array(
				'class' => 'dropdown-menu','role' => 'menu'
			)
		)
	);

	public function __construct($itemFactory, $menuLoader, $config = array()) {
		$this->itemFactory = $itemFactory;

		$this->config = $this->defaults;
		$this->setConfig($config, 'root');
		$this->setConfig($config, 'submenu');

		$this->menuLoader = $menuLoader;
	}

	private function setConfig($config, $section) {
		$this->setConfigAttributes($config, $section, 'attributes');
		$this->setConfigAttributes($config, $section, 'linkAttributes');
		$this->setConfigAttributes($config, $section, 'childrenAttributes');
	}

	private function setConfigAttributes($config, $section, $attributes) {
		if (isset($config[$section][$attributes])) {
			$this->config[$section][$attributes] =
										$config[$section][$attributes];
		}
	}

	public function getMenu() {
		if (!empty($this->menu))
			return $this->menu;

		$this->menu = $this->itemFactory->createItem('root');
		$this->menu->setChildrenAttributes(
								$this->config['root']['childrenAttributes']);

		foreach ($this->raw_menu as $itemData) {
			$item = $this->menuLoader->load($itemData);
			$this->menu->addChild($item);
		}

		return $this->menu;
	}

	protected function resetMenu() {
		if (!empty($this->menu)) {
			$this->menu = array();
		}
	}

	public function addItem($data) {
		$this->resetMenu();

		if (isset($data['children'])) {
			$data = $this->setSubMenuAttributes($data);
		}

		array_push($this->raw_menu, $data);
	}

	protected function setSubMenuAttributes($data) {
		$data['attributes'] = $this->config['submenu']['attributes'];
		$data['linkAttributes'] = $this->config['submenu']['linkAttributes'];
		$data['childrenAttributes'] =
								$this->config['submenu']['childrenAttributes'];
		$data['extras']['caret'] = true;

		return $data;
	}
}
