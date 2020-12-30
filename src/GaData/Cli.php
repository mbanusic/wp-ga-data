<?php

namespace NeZnam\GaData;

class Cli extends \WP_CLI_Command {

    private $analytics;

    private static $_instance = null;

    public function __construct() {
        \WP_CLI::add_command( 'neznam_ga_data_plugin', $this );
    }

    public static function instance () {
        $classname = get_called_class();
        if ( is_null( self::$_instance ) )
            self::$_instance = new $classname();
        return self::$_instance;
    }


    public function initAnalytics() {
        if ($this->analytics) {
            return;
        }
        $KEY_FILE_LOCATION = __DIR__ . '/client_secrets.json';

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("Neznam Data Report");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->analytics = new \Google_Service_AnalyticsReporting($client);
    }

    public function startReport($args, $assoc_args) {
        $start = (int)$assoc_args['start'];
        $end = (int)$assoc_args['end'] + 1;
        if ($start > $end) {
            \WP_CLI::error('wrong interval');
            return;
        }
        $this->initAnalytics();
        for ($day = $start; $day < $end; $day++) {
            $d = strtotime('-'.$day.'day');
            $date = date('Y-m-d', strtotime('-'.$day.'day'));
            \WP_CLI::line('date: '.$date);
            for ($page = 1; $page < 100; $page++) {
                $posts = new \WP_Query([
                    'posts_per_page' => 5,
                    'paged' => $page,
                    'ignore_sticky_posts' => true,
                    'orderby' => 'date',
                    'year' => date('Y', $d),
                    'monthnum' => date('m', $d),
                    'day' => date('d', $d),
                    'post_status' => 'publish',
                    'post_type' => ['post'],
                    'no_found_rows' => true,
                ]);
                $out = [];
                if ($posts->have_posts()) {
                    while ($posts->have_posts()) {
                        $posts->the_post();
                        $home = home_url();
                        // special case for this client, TODO: move to options
                        $link = str_replace($home, '/', get_permalink($posts->post)) . 'index.php';
                        $out[] = [
                            'id' => $posts->post->ID,
                            'link' => $link,
                        ];
                    }
                    if (count($out)) {
                        $this->getReport($out, $date);
                    }
                    else {
                        break;
                    }
                } else {
                    break;
                }
            }
        }
    }

    /**
     * @param \WP_Post $post
     * @return int
     */
    public function getReport($posts, $date)
    {
        // TODO: move to options
        $VIEW_ID = "30995282";

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($date);
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $pageviews = new \Google_Service_AnalyticsReporting_Metric();
        $pageviews->setExpression("ga:pageviews");
        $pageviews->setAlias("pageviews");

        $requests = [];
        foreach ($posts as $post) {
            $filters = [];

            $filter = new \Google_Service_AnalyticsReporting_DimensionFilter();
            $filter->setOperator('EXACT');
            $filter->setDimensionName('ga:pagePath');
            $filter->setExpressions($post['link']);
            $filters[] = $filter;

            $dimensions = new \Google_Service_AnalyticsReporting_Dimension();
            $dimensions->setName('ga:pagePath');

            $dimensionClause = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
            $dimensionClause->setFilters($filters);

            // Create the ReportRequest object.
            $request = new \Google_Service_AnalyticsReporting_ReportRequest();
            $request->setViewId($VIEW_ID);
            $request->setDateRanges($dateRange);
            $request->setMetrics(array($pageviews));
            $request->setDimensionFilterClauses([$dimensionClause]);
            $request->setDimensions([$dimensions]);
            $requests[] = $request;
        }

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);
        $reports = $this->analytics->reports->batchGet($body);
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[$reportIndex];
            $rows = $report->getData()->getRows();
                $row = $rows[0];
                if ($row) {
                    $dimensions = $row->getDimensions();
                    $link = $dimensions[0];
                    $metrics = $row->getMetrics();
                    $values = $metrics[0]->getValues();
                    $count = $values[0];
                    foreach ($posts as $post) {
                        if ($post['link'] === $link) {
                            update_post_meta($post['id'], '_neznam_ga_pageviews', $count);
                            break;
                        }
                    }
                }
        }

    }
}
