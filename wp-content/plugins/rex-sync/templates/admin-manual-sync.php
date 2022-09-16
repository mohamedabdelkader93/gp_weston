<?php
use Rex\Sync\Loader;

$settings = Loader::get_settings();

?>
<div class="wrap rsc-wrap">
    <h1 class="rsc__title"><?php _e('Manual Sync', 'rex-sync') ?></h1>

    <?php
    \Rex\Sync\Helper::display_errors(Loader::$errors);
    \Rex\Sync\Helper::display_messages(Loader::$messages);
    ?>

    <div class="rsc__content">
        <div class="container-fluid rsc-settings">
            <div class="row">
                <div class="col-8">
                    <p>&nbsp;</p>
                    <button type="button" class="button-primary" id="rex-manual-sync"><?php _e('Download listings to queues', 'rex-sync') ?></button>
                    <p>This will download all listings from Rex and add into Queues. Existing listings on WordPress will be updated.</p>
                    <p>DO NOT refresh browser or navigate browser, the process will be stopped.</p>
                </div>
                <div class="col-2"></div>
            </div>
            <div class="row">
                <div id="preview" style="display: block; padding: 20px;border: 1px solid lightgray; max-height: 600px; overflow: auto">
                    <div id="results" ></div>
                    <div id="results-loading" style="display: none">loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($){
        var $body = $('body');
        var $el_results = $('#results');
        var $el_results_loading = $('#results-loading');
        var listing_data = false;
        var current_listing_position = 0;

        $body.on('click', '#rex-manual-sync', function (e) {
            e.preventDefault();

            $(this).attr('disabled', 'disabled');

            reset_data();
            start_log();
            start_download_listings();
        });

        function reset_data() {
            listing_data = false;
            current_listing_position = 0;
        }

        function start_log(){
            $el_results.empty();
            $el_results_loading.show();
        }
        function end_log(){
            $el_results_loading.hide();
        }
        function add_log_message(msg, scroll = false) {
            var $p = $('<p></p>').html(msg);
            $el_results.append($p);

            if(scroll){
                $p.get(0).scrollIntoView(true);
            }
        }

        function start_download_listings(){

            // start download listings

            add_log_message('Start download listings');
            
            $.post(ajaxurl, {
                'action': 'rsc_download_listings'
            }, function(data){
                if(data.total){
                    add_log_message('Total listings: ' + data.total);
                    add_log_message('All listings have been added to queues.');
                    end_log();
                    // listing_data = data;
                    // process_sync_listings();
                }else{
                    add_log_message('Cannot download listings');
                    end_log();
                }

            }, "json");
        }

        function process_sync_listings(){
            if(current_listing_position < listing_data.total){
                $.post(ajaxurl, {
                    'action': 'rsc_sync_single_listing',
                    'row_id': listing_data.data[current_listing_position]
                }, function(data){
                    add_log_message((current_listing_position + 1) + ". " + data, true);
                    current_listing_position ++;
                    setTimeout(process_sync_listings, 10);
                });
            }else{
                add_log_message('Done.', true);
                end_log();
            }
        }

    })(jQuery);
</script>