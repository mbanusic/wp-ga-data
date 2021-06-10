<?php

namespace NeZnam\GaData;

class Admin extends Instance {

    public function __construct()
    {
        $this->register_hook_callbacks();
    }

    protected function register_hook_callbacks()
    {
        ActionsFilters::add_filter('manage_posts_columns', $this,'columns_head');
        ActionsFilters::add_action('manage_posts_custom_column', $this,'columns_content', 10, 2);
    }

    function columns_head($columns)
    {
        $columns['neznam_ga_data'] = 'GA pageviews';
        return $columns;
    }

    function columns_content($column_name, $post_ID)
    {
        if ($column_name == 'neznam_ga_data') {
            echo intval(get_post_meta($post_ID, '_neznam_ga_pageviews', true));
        }
    }


}
