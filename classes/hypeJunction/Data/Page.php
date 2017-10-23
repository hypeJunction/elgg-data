<?php

namespace hypeJunction\Data;

class Page {

	/**
	 * Store page-related information in the client-site data
	 * This information will be used to restore context
	 * and validate request signature, when /data endpoints are
	 * accessed
	 *
	 * @param string $hook   "elgg.data"
	 * @param string $type   "page"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function captureContext($hook, $type, $return, $params) {

		$page_owner_guid = 0;
		$page_owner_export = null;
		$page_owner = elgg_get_page_owner_entity();
		if ($page_owner) {
			$page_owner_guid = (int) $page_owner->guid;
			$adapter = new ElggEntityAdapter($page_owner);
			$page_owner_export = $adapter->export();
		}

		$logged_in_user_guid = 0;
		$logged_in_user_export = null;
		$logged_in_user = elgg_get_logged_in_user_entity();
		if ($logged_in_user) {
			$logged_in_user_guid = (int) $logged_in_user->guid;
			$adapter = new ElggEntityAdapter($logged_in_user);
			$logged_in_user_export = $adapter->export();
		}

		$contexts = elgg_get_context_stack();
		$input = (array) elgg_get_config("input");

		$data = serialize([$logged_in_user_guid, $page_owner_guid, $contexts, $input]);
		$mac = elgg_build_hmac($data)->getToken();

		$return['context'] = [
			'user' => $logged_in_user_export,
			'page_owner' => $page_owner_export,
			'context_stack' => $contexts,
			'input' => $input,
			'mac' => $mac,
		];

		return $return;
	}

	/**
	 * Prevent unsigned requests to data endpoints
	 * @return bool
	 * @throws \InvalidParameterException
	 */
	public static function restoreContext() {

		$logged_in_user_guid = (int) elgg_get_logged_in_user_guid();

		$context = (array) get_input('__context', []);
		$page_owner_guid = (int) elgg_extract('page_owner_guid', $context);
		$contexts = (array) elgg_extract('context_stack', $context);
		$input = (array) elgg_extract('input', $context, []);
		$signature = elgg_extract('mac', $context);
		$ts = elgg_extract('ts', $context);
		$token = elgg_extract('token', $context);

		$data = serialize([$logged_in_user_guid, $page_owner_guid, $contexts, $input]);
		$mac = elgg_build_hmac($data);

		if (!$mac->matchesToken($signature)) {
			throw new \InvalidParameterException("Request signature is invalid");
		}

		if (!validate_action_token(false, $token, $ts)) {
			throw new \InvalidParameterException("Request token is invalid");
		}

		elgg_set_context_stack($contexts);
		elgg_set_config("input", $input);
		elgg_set_page_owner_guid($page_owner_guid);

		return true;
	}
}