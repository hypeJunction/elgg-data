<?php

namespace hypeJunction\Data;

use hypeJunction\Lists\GroupList;
use hypeJunction\Lists\ObjectList;
use hypeJunction\Lists\UserList;

class ElggListAdapter {

	const MAX_ITEMS = 100;

	/**
	 * @var array
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param array $options ege* options
	 */
	public function __construct(array $options = []) {
		$this->options = $options;
	}

	/**
	 * Export a list
	 *
	 * @param array $params Export params
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function export(array $params = []) {

		$viewtype = elgg_get_viewtype();
		elgg_set_viewtype('default');

		$options = $this->prepare($params);

		$batch = new \ElggBatch('elgg_get_entities_from_attributes', $options);

		$data = [
			'count' => (int) $batch->count(),
			'limit' => $options['limit'],
			'offset' => $options['offset'],
			'items' => [],
			'_related' => [],
		];

		if (elgg_get_config('debug')) {
			$data['options'] = $options;
		}

		foreach ($batch as $entity) {
			$adapter = new ElggEntityAdapter($entity);
			$data['items'][] = $adapter->export($params);

			if ($owner = $entity->getOwnerEntity()) {
				if (!isset($data['_related'][$owner->guid])) {
					$adapter = new ElggEntityAdapter($owner);
					$data['_related'][$owner->guid] = $adapter->export($params);
				}
			}

			if ($container = $entity->getContainerEntity()) {
				if (!isset($data['_related'][$container->guid])) {
					$adapter = new ElggEntityAdapter($container);
					$data['_related'][$container->guid] = $adapter->export($params);
				}
			}
		}

		$data['_related'] = array_values($data['_related']);

		$url = current_page_url();
		$url = substr($url, strlen(elgg_get_site_url()));
		if ($data['count'] && $options['offset'] > 0) {
			$prev_offset = $options['offset'] - $options['limit'];
			if ($prev_offset < 0) {
				$prev_offset = 0;
			}

			$data['_links']['prev'] = elgg_http_add_url_query_elements($url, [
				'offset' => $prev_offset,
			]);
		} else {
			$data['_links']['prev'] = false;
		}

		if ($data['count'] > $options['limit'] + $options['offset']) {
			$next_offset = $options['offset'] + $options['limit'];
			$data['_links']['next'] = elgg_http_add_url_query_elements($url, [
				'offset' => $next_offset,
			]);
		} else {
			$data['_links']['next'] = false;
		}

		elgg_set_viewtype($viewtype);

		return $data;
	}

	/**
	 * Prepare options with search, sort and filter
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	protected function prepare(array $options = []) {
		$singulars = ['type', 'subtype', 'guid', 'owner_guid', 'container_guid', 'site_guid'];
		$options = _elgg_normalize_plural_options_array($this->options, $singulars);

		$options['types'] = (array) $options['types'];
		$options['subtypes'] = (array) $options['subtypes'];

		if (sizeof($options['types']) !== 1) {
			throw new \Exception('List export can only be performed with exactly 1 entity type set');
		}

		$type = $options['types'][0];
		$public_subtypes = get_registered_entity_types($type);

		if (empty($options['subtypes'])) {
			$options['subtypes'] = $public_subtypes;
		}

		foreach ($options['subtypes'] as $subtype) {
			if (!in_array($subtype, $public_subtypes)) {
				throw new \Exception("$subtype is not a public subtype");
			}
		}

		if (!isset($options['limit'])) {
			$options['limit'] = (int) get_input('limit', elgg_get_config('default_limit'));
		}
		if ($options['limit'] > self::MAX_ITEMS) {
			$options['limit'] = self::MAX_ITEMS;
		}

		if (!isset($options['offset'])) {
			$options['offset'] = (int) get_input('offset', 0);
		}

		unset($options['count']);

		if (!elgg_is_active_plugin('hypeLists')) {
			return $options;
		}

		if (!in_array($type, ['user', 'object', 'group'])) {
			return $options;
		}

		switch ($type) {
			case 'user' :
				$list = new UserList();
				break;

			case 'object' :
				$list = new ObjectList();
				break;

			case 'group' :
				$list = new GroupList();
				break;
		}

		$filter = elgg_extract('filter', $options, get_input('filter'));
		$query = elgg_extract('query', $options, get_input('query'));
		$sort = elgg_extract('sort', $options, get_input('sort', 'time_created::asc'));
		$target = elgg_extract('filter_target', $options);
		unset($options['filter_target']);
		if (is_numeric($target)) {
			$target = get_entity($target);
		}

		if (!$target instanceof \ElggEntity) {
			$target = null;
		}

		list($sort_field, $sort_direction) = explode('::', $sort);

		$list->setOptions($options)
			->addSort($sort_field, $sort_direction)
			->addFilter($filter, $target)
			->setQuery($query);

		return $list->getOptions();
	}
}