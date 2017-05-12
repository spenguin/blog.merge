<?php

require_once(plugin_dir_path(__FILE__) . 'functions.php');

function generate_html(){

    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    $custom_posts_names = array();
    $custom_posts_labels = array();

    $args = array(
        'public'    => true,
        '_builtin'  => false
    );

    $output = 'objects';

    $operator = 'and';

    $post_types = get_post_types($args, $output, $operator);

    foreach($post_types as $post_type){

        $custom_posts_names[] = $post_type->name;
        $custom_posts_labels[] = $post_type->labels->singular_name;

    }


?>

    <div class="wrap">

        <h2 align="center">Export Data from you Site</h2>

        <div id="WtiLikePostOptions" class="postbox">

            <div class="inside">

                <form id="infoForm" method="post">

                    <table class="form-table">

                        <tr>

                            <th>Select a Post Type to Extract Data:</th>

                            <td>

                                <label><input type="radio" name="post-type" value="any" required="required" /> All Types (pages, posts, and custom post types)</label><br/>
                                <label><input type="radio" name="post-type" value="page" required="required" /> Pages</label><br/>
                                <label><input type="radio" name="post-type" value="post" required="required" /> Posts</label><br/>

<?php

                                if(!empty($custom_posts_names) && !empty($custom_posts_labels)){
                                    for( $i = 0; $i < count($custom_posts_names); $i++ ){
                                        echo '<label><input type="radio" name="post-type" value="'. $custom_posts_names[$i] . '" required="required" /> ' . $custom_posts_labels[$i] . ' Posts</label><br>';
                                    }
                                }
?>

                            </td>

                        </tr>

                        <tr>

                            <th>Additional Data:</th>

                            <td>

                                <label><input type="checkbox" name="additional-data[]" value="url" /> URLs</label><br/>
                                <label><input type="checkbox" name="additional-data[]" value="title" /> Titles</label><br/>
                                <label><input type="checkbox" name="additional-data[]" value="category" /> Categories</label><br/>

                            </td>

                        </tr>

                        <tr>

                            <th>Export Type:</th>

                            <td>

                                <label><input type="radio" name="export-type" value="text" required="required" /> CSV</label><br/>
                                <label><input type="radio" name="export-type" value="here" required="required" /> Output here</label><br/>

                            </td>

                        </tr>

                        <tr>

                            <td></td><td><input type="submit" name="export" class="button button-primary" value="Export"/></td>

                        </tr>

                    </table>


                </form>

            </div>

        </div>

        <h4 align="right">Developed by: <a href="http://AtlasGondal.com/?utm_source=self&utm_medium=wp&utm_campaign=plugin&utm_term=export-url" target="_blank">Atlas Gondal</a></h4>

    </div>



<?php

    if (isset($_POST['export'])) {

        if (!empty($_POST['post-type']) && !empty($_POST['export-type']) && !empty($_POST['additional-data']) ) {

            $post_type = $_POST['post-type'];
            $export_type = $_POST['export-type'];
            $additional_data = $_POST['additional-data'];

            if($additional_data == ''){
                echo "Sorry, you missed export type, Please <strong>Select Export Type</strong> and try again! :)";
                exit;
            }

            $selected_post_type = get_selected_post_type($post_type, $custom_posts_names);



            generate_output($selected_post_type, $export_type, $additional_data);

        }

    }

}

generate_html();

