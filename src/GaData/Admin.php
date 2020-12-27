<?php

namespace NeZnam\GaData;

class Admin extends Instance {
    private $analytics;


    public function initAnalytics() {
        $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Neznam Data Report");
        $client->setAuthConfig($KEY_FILE_LOCATION);
        $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
        $this->analytics = new Google_Service_AnalyticsReporting($client);
    }

    public function startReport() {
        $posts = new \WP_Query([
            'posts_per_page' => 100,
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
            'orderby' => 'date',
            'date_query' => [
                'before' => '-1day'
            ],
            'post_status' => 'publish',
            'post_type' => 'publish',
        ]);
        while ($posts->have_posts()) {
            $posts->the_post();
            $data = $this->getReport($posts->post);
            //update_post_meta($posts->post->ID, '_neznam_ga_pageviews', $data);
        }
    }

    /**
     * @param \WP_Post $post
     * @return int
     */
    public function getReport($post)
    {
        $VIEW_ID = "30995282";

        // Create the DateRange object.
        $dateRange = new \Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate(date('Y-m-d', strtotime($post->post_date)));
        $dateRange->setEndDate("today");

        // Create the Metrics object.
        $pageviews = new \Google_Service_AnalyticsReporting_Metric();
        $pageviews->setExpression("ga:pageviews");
        $pageviews->setAlias("pageviews");

        $filter = new \Google_Service_AnalyticsReporting_DimensionFilter();
        $filter->setOperator('==');
        $filter->setDimensionName('gaPath');
        $filter->setExpressions(get_permalink($post));

        $dimension = new \Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimension->setFilters([$filter]);


        // Create the ReportRequest object.
        $request = new \Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($VIEW_ID);
        $request->setDateRanges($dateRange);
        $request->setMetrics(array($pageviews));
        $request->setDimensionFilterClauses([$dimension]);

        $body = new \Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests(array($request));
        $reports = $this->analytics->reports->batchGet($body);
        for ($reportIndex = 0; $reportIndex < count($reports); $reportIndex++) {
            $report = $reports[$reportIndex];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();

            for ($rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[$rowIndex];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    print($dimensionHeaders[$i] . ": " . $dimensions[$i] . "\n");
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        print($entry->getName() . ": " . $values[$k] . "\n");
                    }
                }
            }

        }
    }

    public function __construct()
    {
    }

    protected function register_hook_callbacks()
    {
        // TODO: Implement register_hook_callbacks() method.
    }
}
