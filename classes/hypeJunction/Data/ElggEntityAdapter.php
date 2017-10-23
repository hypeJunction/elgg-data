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

		$viewtype = elgg_get_viewtype();
		elgg_set_viewtype('default');

		$data = (array) $this->entity->toObject();

		$type = $this->entity->type;
		$subtype = $this->entity->getSubtype();

		$params['entity'] = $this->entity;

		$data = elgg_trigger_plugin_hook('adapter:entity', "$type:$subtype", $params, $data);
		$data = elgg_trigger_plugin_hook('adapter:entity', $type, $params, $data);

		$expand = function($elem) use ($params, &$expand) {
			if ($elem instanceof \ElggEntity) {
				$adapter = new ElggEntityAdapter($elem);
				return $adapter->export($params);
			} else if (is_array($elem)) {
				foreach ($elem as $key => $value) {
					$elem[$key] = $expand($value);
				}
			}

			return $elem;
		};

		elgg_set_viewtype($viewtype);

		return $expand($data);
	}
}