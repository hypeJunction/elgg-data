<?php

$entity = \hypeJunction\Data\Router::getEntity();

$adapter = new \hypeJunction\Data\ElggEntityAdapter($entity);
$data = $adapter->export();

echo json_encode($data);