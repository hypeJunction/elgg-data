require(['elgg'], function(elgg) {

    elgg.register_hook_handler('ajax_request_data', 'all', function(hook, type, params, data) {
        data.__context = {
            page_owner_guid: elgg.data.context.page_owner ? elgg.data.context.page_owner.guid : 0,
            context_stack: elgg.data.context.context_stack,
            input: elgg.data.context.input,
            mac: elgg.data.context.mac,
            ts: elgg.security.token.__elgg_ts,
            token: elgg.security.token.__elgg_token
        }
    });

});