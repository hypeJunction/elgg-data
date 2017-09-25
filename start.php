<?php

/**
 * elgg-data
 *
 * Provides JSON endpoints for retrieving entities and lists
 *
 * @author    Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2017, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

use hypeJunction\Data\Extender;
use hypeJunction\Data\Router;

elgg_register_event_handler('init', 'system', function () {

	elgg_register_page_handler('data', [Router::class, 'route']);

	elgg_register_plugin_hook_handler('adapter:entity', 'all', [Extender::class, 'addData']);
	elgg_register_plugin_hook_handler('adapter:entity', 'all', [Extender::class, 'addPermissions']);
	elgg_register_plugin_hook_handler('adapter:entity', 'all', [Extender::class, 'addCounters']);
	elgg_register_plugin_hook_handler('adapter:entity', 'all', [Extender::class, 'addDataLinks']);

	elgg_register_plugin_hook_handler('adapter:entity', 'user', [Extender::class, 'addUserData']);
	elgg_register_plugin_hook_handler('adapter:entity', 'group', [Extender::class, 'addGroupData']);
});