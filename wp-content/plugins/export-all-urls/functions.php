<?php
/**
 * Created by PhpStorm.
 * User: Atlas_Gondal
 * Date: 4/9/2016
 * Time: 9:01 AM
 */

function get_selected_post_type($post_type, $custom_posts_names){

    switch ($post_type){

        case "any":

            $type = "any";
            break;

        case "page":

            $type = "page";
            break;

        case "post":

            $type = "post";
            break;

        default:

            for( $i = 0; $i < count($custom_posts_names); $i++ ){

                if ($post_type == $custom_posts_names[$i] ){

                    $type = $custom_posts_names[$i];

                }

            }

    }

    return $type;


}


function IsChecked($name,$value)
{
    foreach($name as $data)
    {
        if($data == $value)
        {
            return true;
        }
    }

    return false;
}


/**
 * @param $selected_post_type
 * @param $export_type
 * @param $additional_data
 */
function generate_output($selected_post_type, $export_type, $additional_data){

    $html = array();
    $counter = 0;

    if ($export_type == "here") {
        $line_break = "<br/>";
    }
    else {
        $line_break = "";
    }



    $posts_query = new WP_Query( array(
        'post_type' => $selected_post_type,
        'posts_per_page' => '-1',
        'post_status' => 'publish',
        'orderby' => 'title',
        'order'   => 'ASC'
    ) );


    if (IsChecked($additional_data, 'url')) {

        while ( $posts_query->have_posts() ):

            $posts_query->the_post();
            $html['url'][$counter] .= get_permalink().$line_break;
            $counter++;

        endwhile;

        $counter = 0;

    }

    if (IsChecked($additional_data, 'title')) {

        while ( $posts_query->have_posts() ):

            $posts_query->the_post();
            $html['title'][$counter] .= get_the_title().$line_break;
            $counter++;

        endwhile;

        $counter = 0;

    }

    if (IsChecked($additional_data, 'category')) {

        while ( $posts_query->have_posts() ):

            $categories = '';
            $posts_query->the_post();
            $cats = get_the_category();
            foreach($cats as $index => $cat){

                $categories .= ($index == 0 ? $cat->name : ", ".$cat->name);

            }
			
            $html['category'][$counter] .= $categories.$line_break;
			
			$counter++;

        endwhile;

        $counter = 0;

    }
    export_data($html, $export_type);

    wp_reset_postdata();
}

function export_data($urls, $export_type){

    $upload_dir = $_SERVER['DOCUMENT_ROOT']."/wp-content/uploads/";
    $file_path = wp_upload_dir();
    $file_path = $file_path['baseurl'];

    $count = 0;
    foreach($urls as $item){
        $count = count($item);
    }


    switch ($export_type){

        case "text":

            $file_name = 'Exported_Data.CSV';
            $data = '';
            $headers = array();

            $file = $upload_dir.$file_name;
            $myfile = fopen($file, "w") or die("Unable to create a file on your server!");
			fprintf( $myfile, "\xEF\xBB\xBF");
            
			($urls['title']) ? $headers[] = 'Title' : " ";
            ($urls['url']) ? $headers[] = 'URLs' : " ";
            ($urls['category']) ? $headers[] = 'Categories' : " ";

            fputcsv($myfile, $headers);

            for( $i = 0; $i < $count; $i++ ){
                $data = array(
                    ($urls['title']) ? $urls['title'][$i] : "",
                    ($urls['url']) ? $urls['url'][$i] : "",
                    ($urls['category']) ? $urls['category'][$i] : ""
                );

                fputcsv($myfile, $data);
            }

            fclose($myfile);

            echo "<div class='updated'>Data exported successfully! <a href='".$file_path."/".$file_name."' target='_blank'><strong>Click here</strong></a> to Download.</div>";

            break;

        case "here":


            echo "<h1><strong>Below is a list of Exported Data:</strong></h1>";
            echo "<table class='form-table'>";
            echo "<tr><th>ID</th>";

            echo ($urls['title']) ? "<th>Title</th>" : "";
            echo ($urls['url']) ? "<th>URL</th>" : "";
            echo ($urls['category']) ? "<th>Categories</th>" : "";


            echo "</tr>";

            for( $i = 0; $i < $count; $i++ ){

                $id = $i + 1;
                echo "<tr><td>".$id."</td>";
                echo ($urls['title']) ? "<td>".$urls['title'][$i]."</td>" : "";
                echo ($urls['url']) ? "<td>".$urls['url'][$i]."</td>" : "";
                echo ($urls['category']) ? "<td>".$urls['category'][$i]."</td>" : "";
                echo "</tr>";
            }

            echo "</table>";

            break;

        default:

            echo "Sorry, you missed export type, Please <strong>Select Export Type</strong> and try again! :)";
            break;


    }



}