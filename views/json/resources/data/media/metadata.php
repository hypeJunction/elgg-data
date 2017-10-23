<?php

if (!elgg_is_active_plugin('hypeScraper')) {
	throw new RuntimeException('Scraper library is not found');
}

$data = hypeapps_scrape(get_input('url'));
if (!$data) {
	throw new RuntimeException('Unable to scrape the resource');
}

echo json_encode($data);