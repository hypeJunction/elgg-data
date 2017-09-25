<?php

namespace hypeJunction\Data;

class Extender {

	/**
	 * Add entity specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggEntity */

		$type = $entity->type;
		$subtype = $entity->getSubtype();

		$icon_sizes = array_keys((array) elgg_get_icon_sizes($type, $subtype));

		$return['location'] = $entity->getLocation();
		$return['latitude'] = $entity->getLatitude();
		$return['longitude'] = $entity->getLongitude();

		$return['status'] = $entity->status;

		$return['read_access'] = [
			'id' => $entity->access_id,
			'label' => get_readable_access_level($entity->access_id),
		];

		$return['_links']['icons'] = [];

		foreach ($icon_sizes as $icon_size) {
			$return['_links']['icons'][$icon_size] = $entity->getIconURL($icon_size);
		}

		$tag_names = elgg_get_registered_tag_metadata_names();
		foreach ($tag_names as $tag_name) {
			$return[$tag_name] = (array) $entity->$tag_name;
		}

		if ($entity instanceof \ElggFile) {
			$return['_links']['download'] = elgg_get_download_url($entity);
			$return['_links']['inline'] = elgg_get_inline_url($entity);
		}

		return $return;
	}

	/**
	 * Add permissions
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addPermissions($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggEntity */

		$type = $entity->type;
		$subtype = $entity->getSubtype();

		$return['_permissions']['edit'] = $entity->canEdit();
		$return['_permissions']['comment'] = $entity->canComment();

		$registered = get_registered_entity_types();

		foreach ($registered as $type => $subtypes) {
			if ($subtypes) {
				foreach ($subtypes as $subtype) {
					$return['_permissions']['write'][$type][$subtype] = $entity->canWriteToContainer(0, $type, $subtype);
				}
			} else {
				$return['_permissions']['write'][$type] = $entity->canWriteToContainer(0, $type);
			}
		}


		$annotations = [
			'likes',
		];

		foreach ($annotations as $annotation) {
			$return['_permissions']['annotate'][$annotation] = $entity->canAnnotate(0, $annotation);
		}

		return $return;
	}

	/**
	 * Add user specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "user"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addUserData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggUser */

		$fields = (array) elgg_get_config('profile_fields');
		foreach ($fields as $field => $field_type) {
			if (isset($return[$field])) {
				continue;
			}

			$return[$field] = $entity->$field;
		}

		$return['_counters']['friends'] = $entity->getFriends(['count' => true]);
		$return['_links']['friends'] = elgg_http_add_url_query_elements("user/friends", [
			'guid' => $entity->guid,
		]);
		$return['_links']['friends_of'] = elgg_http_add_url_query_elements("user/friends_of", [
			'guid' => $entity->guid,
		]);

		$user_guid = elgg_get_logged_in_user_guid();
		$return['_relationships']['friend'] = $entity->isFriendsWith($user_guid);
		$return['_relationships']['friend_of'] = $entity->isFriendOf($user_guid);

		return $return;
	}

	/**
	 * Add group specific data
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "group"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addGroupData($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggGroup */

		$fields = (array) elgg_get_config('group');
		foreach ($fields as $field => $field_type) {
			if (isset($return[$field])) {
				continue;
			}

			$return[$field] = $entity->$field;
		}

		$return['content_access_mode'] = $entity->getContentAccessMode();
		$return['membership'] = $entity->membership;
		$return['group_acl'] = $entity->group_acl;

		$return['_counters']['members'] = $entity->getFriends(['count' => true]);
		$return['_links']['members'] = elgg_http_add_url_query_elements("group/members", [
			'guid' => $entity->guid,
		]);

		$user = elgg_get_logged_in_user_entity();
		$return['_relationships']['member'] = $entity->isMember($user);

		return $return;
	}

	/**
	 * Add counters
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addCounters($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		/* @var $entity \ElggEntity */

		$type = $entity->type;
		$subtype = $entity->getSubtype();

		if ($subtype == 'discussion') {
			$return['_counters']['comments'] = elgg_get_entities([
				'types' => 'object',
				'subtypes' => 'discussion_reply',
				'count' => true,
				'container_guids' => $entity->guid,
			]);
		} else {
			$return['_counters']['comments'] = $entity->countComments();
		}

		if (elgg_is_active_plugin('likes')) {
			$return['_counters']['likes'] = (int) $entity->countAnnotations('likes');
		}

		return $return;
	}

	/**
	 * Add data links to entity export
	 *
	 * @param string $hook   "adapter:entity"
	 * @param string $type   "all"
	 * @param array  $return Data
	 * @param array  $params Hook params
	 *
	 * @return array
	 */
	public static function addDataLinks($hook, $type, $return, $params) {

		$entity = elgg_extract('entity', $params);
		$type = $entity->type;
		$subtype = $entity->getSubtype();

		if ($entity->owner_guid) {
			$return['_links']['owner'] = elgg_http_add_url_query_elements("data/entity", [
				'guid' => $entity->owner_guid,
			]);
		} else {
			$return['_links']['owner'] = false;
		}

		if ($entity->container_guid) {
			$return['_links']['container'] = elgg_http_add_url_query_elements("data/entity", [
				'guid' => $entity->container_guid,
			]);
		} else {
			$return['_links']['container'] = false;
		}

		$return['_links']['comments'] = elgg_http_add_url_query_elements("data/comments", [
			'guid' => $entity->guid,
		]);

		if (elgg_is_active_plugin('likes')) {
			$likable = (bool) elgg_trigger_plugin_hook('likes:is_likable', "$type:$subtype", [], false);
			if ($likable) {
				$return['_links']['likes'] = elgg_http_add_url_query_elements("data/likes", [
					'guid' => $entity->guid,
				]);
			} else {
				$return['_links']['likes'] = false;
			}
		}

		return $return;
	}
}