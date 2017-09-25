<?php

namespace hypeJunction\Data;

class ElggEntityAdapter {

	/**
	 * @var \ElggEntity
	 */
	private $entity;

	/**
	 * Entity constructor.
	 *
	 * @param \ElggEntity $entity Entity
	 */
	public function __construct(\ElggEntity $entity) {
		$this->entity = $entity;
	}

	/**
	 * Export an entity
	 *
	 * @param array $params Export params
	 *
	 * @return array
	 */
	public function export(array $params = []) {
		$data = (array) $this->entity->toObject();

		$type = $this->entity->type;
		$subtype = $this->entity->getSubtype();

		$params['entity'] = $this->entity;

		$data = elgg_trigger_plugin_hook('adapter:entity', "$type:$subtype", $params, $data);
		$data = elgg_trigger_plugin_hook('adapter:entity', $type, $params, $data);

		return $data;
	}
}