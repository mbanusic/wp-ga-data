<?php
namespace NeZnam\GaData;

class Elastic extends Instance
{

    public function __construct()
    {
        $this->register_hook_callbacks();
    }

    protected function register_hook_callbacks()
    {
        ActionsFilters::add_filter('ep_searchable_post_types', $this,'nocno_es_post_types');
        ActionsFilters::add_filter('ep_indexable_post_types', $this,'nocno_es_post_types');
        ActionsFilters::add_filter('ep_admin_wp_query_integration',$this, 'nocno_elastic');
        ActionsFilters::add_filter('ep_ajax_wp_query_integration', $this,'nocno_elastic');
        ActionsFilters::add_filter('ep_formatted_args',$this, 'nocno_set_to_exact', 10, 2);
        ActionsFilters::add_action('plugins_loaded', $this,'load_my_elasticpress_feature', 99);
        //ActionsFilters::add_action('ep_indexable_post_status', $this, 'post_status', 10, 1);
    }

    function load_my_elasticpress_feature()
    {
        \ElasticPress\Features::factory()->register_feature(
            new CoAuthorsPlus()
        );
    }

    function nocno_set_to_exact($formatted_args, $args)
    {
        if (!empty($formatted_args['query']['bool']['should'])) {
//Create/change the bool query from should to must
            $formatted_args['query']['bool']['must'] = $formatted_args['query']['bool']['should'];
//Add the operator AND to the new bool query
            $formatted_args['query']['bool']['must'][0]['multi_match']['operator'] = 'AND';
//Erase the old should query
            unset($formatted_args['query']['bool']['should']);
//Erase the phrase matching (or not, if you don't want it)
            unset($formatted_args["query"]["bool"]["must"][0]["multi_match"]["type"]);
        }

        return $formatted_args;
    }

    function nocno_es_post_types($post_types)
    {
        $post_types['attachment'] = 'attachment';
        return $post_types;
    }

    function nocno_elastic($value)
    {
        return true;
    }
}

