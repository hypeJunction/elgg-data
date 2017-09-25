<?php

$options = [
	'types' => 'user',
];

$adapter = new \hypeJunction\Data\ElggListAdapter($options);
$data = $adapter->export();

echo json_encode($data);