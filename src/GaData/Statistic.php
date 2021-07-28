<?php

namespace NeZnam\GaData;

class Statistic extends Instance {

    public function __construct()
    {
        $this->register_hook_callbacks();
    }

    protected function register_hook_callbacks()
    {
        ActionsFilters::add_action('admin_menu', $this, 'menu');
    }

    function menu() {
        add_options_page('Statistika', 'Statistika', 'manage_options', 'neznam_statistika', [$this, 'statistika']);
    }

    function statistika() {
        ?>
        <h1>Statistika</h1>
        <form action="" method="post">
        <label>Od</label><input type="date" name="od" value="<?php echo $_POST['od'] ?>">
        <label>Do</label><input type="date" name="do" value="<?php echo $_POST['do'] ?>">
            <button type="submit">Pošalji</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Ime i prezime</th>
                    <th>Korisničko ime</th>
                    <th>Broj članaka</th>
                    <th>Broj pregleda</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if (isset($_POST['od']) && $_POST['od']) {
            $authors = new \WP_User_Query([
                'role__in' => ['editor', 'author'],
                'number' => -1,
                'fields' => 'all'
            ]);
            foreach ($authors->get_results() as $author) {
                $q = new \WP_Query([
                    'posts_per_page' => -1,
                    'author' => $author->ID,
                    'date_query' => [
                        [
                            'after' => $_POST['od'],
                            'before' => $_POST['do']
                        ],
                        'inclusive' => true
                    ],
                    'fields' => 'ids',
                    'ignore_sticky_posts' => true,
                    'no_found_rows' => true,
                ]);
                $total_views = 0; $total_posts = 0;
                foreach ($q->posts as $id) {
                    $count = (int)get_post_meta($id, '_neznam_ga_pageviews', true);
                    $total_views += $count;
                    $total_posts++;
                }
              ?><tr>
                    <td><?php echo $author->first_name ?> <?php echo $author->last_name ?> </td>
                <td><?php echo $author->user_login ?></td>
                <td><?php echo $total_posts ?></td>
                <td><?php echo $total_views ?></td>
                </tr><?php
            }

            }
            ?>
            </tbody>
        </table>
        <?php
    }
}
