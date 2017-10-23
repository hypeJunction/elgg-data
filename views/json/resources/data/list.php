<?php

$public_metadata = [
	'location',
	'status',
	'briefdescription',
	'excerpt',
];

$public_metadata = array_merge($public_metadata, (array) elgg_get_registered_tag_metadata_names());

$public_metadata = elgg_trigger_plugin_hook('public_metadata', 'search', [], $public_metadata);

$options = [
	'types' => get_input('types'),
	'subtypes' => get_input('subtypes'),
	'owner_guids' => get_input('owner_guids'),
	'container_guids' => get_input('container_guids'),
];

$metadata = get_input('metadata');
if (is_array($metadata)) {
	foreach ($metadata as $name => $value) {
		if (!in_array($name, $public_metadata)) {
			throw new \hypeJunction\Data\HttpException("'$name' is not public metadata", ELGG_HTTP_FORBIDDEN);
		}
		$options['metadata_name_value_pairs'][] = [
			'name' => $name,
			'value' => $value,
		];
	}
}

$options['query'] = get_input('query');
$options['sort'] = get_input('sort');

$adapter = new \hypeJunction\Data\ElggListAdapter($options);
$data = $adapter->export();

echo json_encode($data);