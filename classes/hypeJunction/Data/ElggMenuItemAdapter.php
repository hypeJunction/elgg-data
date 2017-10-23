<?php

namespace hypeJunction\Data;

class ElggMenuItemAdapter {

	/**
	 * @var \ElggMenuItem
	 */
	private $item;

	/**
	 * @var string
	 */
	private $menu_name;

	/**
	 * Entity constructor.
	 *
	 * @param \ElggMenuItem $item      Menu item
	 * @param string        $menu_name Menu Name
	 */
	public function __construct(\ElggMenuItem $item, $menu_name) {
		$this->item = $item;
		$this->menu_name = $menu_name;
	}

	/**
	 * Export
	 *
	 * @param array $params Export params
	 *
	 * @return array
	 */
	public function export(array $params = []) {
		$data = [
			'name' => $this->item->getName(),
			'priority' => $this->item->getPriority(),
			'selected' => $this->item->getSelected(),
			'itemClass' => $this->item->getItemClass(),
			'linkClass' => $this->item->getLinkClass(),
			'deps' => $this->item->getDeps(),
			'parent' => $this->item->getParentName(),
			'children' => $this->item->getChildren(),
		];

		$values = $this->item->getValues();

		$data = array_merge($values, $data);

		$children = $this->item->getChildren();
		foreach ($children as $child) {
			$adapter = new self($child, $this->menu_name);
			$data['children'][] = $adapter->export($params);
		}

		$data = elgg_trigger_plugin_hook('adapter:menu_item', "menu:$this->menu_name", $params, $data);

		return $data;
	}
}