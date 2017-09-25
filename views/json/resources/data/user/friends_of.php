<?php

$entity = \hypeJunction\Data\Router::getEntity('user');

$options = [
	'types' => 'user',
	'relationship' => 'friend',
	'relationship_guid' => $entity->guid,
	'inverse_relationship' => true,
];

$adapter = new \hypeJunction\Data\ElggListAdapter($options);
$data = $adapter->export();

echo json_encode($data);