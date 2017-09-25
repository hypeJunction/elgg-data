<?php

namespace hypeJunction\Data;

use Elgg\Http\ResponseBuilder;
use Elgg\Logger;

class Router {

	/**
	 * Route /data
	 *
	 * @param array $segments URL segments
	 *
	 * @return ResponseBuilder
	 */
	public static function route($segments) {

		_elgg_services()->logger->disable();

		elgg_set_viewtype('json');

		// We don't want Ajax API to wrap our responses
		_elgg_services()->request->headers->remove('X-Requested-With');

		$log_level = _elgg_services()->logger->getLevel();

		elgg_set_http_header('Content-Type: application/json');

		$resource = implode('/', $segments);

		try {
			if (!elgg_view_exists("resources/data/$resource")) {
				throw new HttpException('Unknown resource', ELGG_HTTP_NOT_IMPLEMENTED);
			}

			$json = elgg_view_resource("data/$resource");
			if (!$json) {
				$json = json_encode(new \stdClass());
			}

			$result = json_decode($json, true);

			$response = [
				'status' => ELGG_HTTP_OK,
				'message' => 'OK',
				'result' => $result,
			];
		} catch (\Exception $ex) {
			$status = $ex->getCode() ? : ELGG_HTTP_INTERNAL_SERVER_ERROR;
			$response = [
				'status' => $status,
				'message' => $ex->getMessage(),
				'result' => new \stdClass(),
			];

			if ($log_level) {
				$response['exception'] = $ex->getTrace();
			}
		}

		$response['system_messages'] = _elgg_services()->systemMessages->dumpRegister();

		if ($log_level) {
			$response['log'] = array_filter(_elgg_services()->logger->enable(), function ($e) use ($log_level) {
				return $e['level'] >= $log_level;
			});
		}

		return elgg_ok_response(json_encode($response));
	}

	/**
	 * Load entity from guid input
	 *
	 * @param string $type    Entity type
	 * @param string $subtype Entity subtype
	 *
	 * @return \ElggEntity
	 * @throws HttpException
	 */
	public static function getEntity($type = null, $subtype = null) {
		$guid = get_input('guid');
		if (!elgg_entity_exists($guid)) {
			throw new \hypeJunction\Data\HttpException('Entity does not exist', ELGG_HTTP_NOT_FOUND);
		}

		$entity = get_entity($guid);
		if (!elgg_instanceof($entity, $type, $subtype)) {
			throw new \hypeJunction\Data\HttpException('Entity is not accessible', ELGG_HTTP_FORBIDDEN);
		}

		$public_subtypes = get_registered_entity_types($entity->type);
		if (!empty($public_subtypes) && !in_array($entity->getSubtype(), $public_subtypes)) {
			throw new \hypeJunction\Data\HttpException("\"{$entity->getSubtype()}\" is not a public subtype", ELGG_HTTP_FORBIDDEN);
		}

		return $entity;
	}
}