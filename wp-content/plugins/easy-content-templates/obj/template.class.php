<?php

class ect_template {

    const pmk_shared        = "ect-template-is-public";
    const pmk_load_title    = "ect-load-default-title";
    const pmk_load_content  = "ect-load-default-content";
    const pmk_load_excerpt  = "ect-load-default-excerpt";

    static function exists($id){
        $check_post = get_post($id);
        if($check_post == null){
            return false;
        }
        if(!isset($check_post->post_type)){
            return false;
        }
        if($check_post->post_type != "ec-template"){
            return false;
        }
        return true;
    }

    static function get_template($id){
        $result = array(
            "success" => false,
            "message" => "Uninitialized error! This really shouldn't happen!",
            "data" => array(
                "id" => $id,
                "title" => "",
                "content" => "",
                "excerpt" => "",
            ),
        );
    
        if(!self::exists($id)){
            $result["message"] = "The template (ID {$id}) does not exist!";
            return $result;
        }

        $template = get_post($id);
        $result["success"] = true;
        $result["message"] = "Template loaded!";
        $result["data"]["title"] = $template->post_title;
        $result["data"]["content"] = $template->post_content;
        $result["data"]["excerpt"] = $template->post_excerpt;
        return $result;
    }

    static function is_shared($id){
        $option = get_post_meta($id, self::pmk_shared, true);
        $option = $option == "" ? false : $option;
        return $option;
    }
    
    static function load_title($id){
        $option = get_post_meta($id, self::pmk_load_title, true);
        $option = $option == "" ? true : $option;
        return $option;
    }
    
    static function load_content($id){
        $option = get_post_meta($id, self::pmk_load_content, true);
        $option = $option == "" ? true : $option;
        return $option;
    }
    
    static function load_excerpt($id){
        $option = get_post_meta($id, self::pmk_load_excerpt, true);
        $option = $option == "" ? true : $option;
        return $option;
    }

    static function register_templates(){
        $labels = array(
            "name"                  => _x("Templates", "plural name of template", "easy-content-templates"),
            "singular_name"         => _x("Template", "singular name of template", "easy-content-templates"),
            "menu_name"             => _x("Templates", "dashboard menu", "easy-content-templates"),
            "name_admin_bar"        => _x("Template", "admin bar menu", "easy-content-templates"),
            "all_items"             => __("All Templates", "easy-content-templates"),
            "add_new"               => _x("Add New", "add new for template", "easy-content-templates"),
            "add_new_item"          => __("Add New Template", "easy-content-templates"),
            "new_item"              => __("New Template", "easy-content-templates"),
            "edit_item"             => __("Edit Template", "easy-content-templates"),
            "new_item"              => __("New Template", "easy-content-templates"),
            "view_item"             => __("View Template", "easy-content-templates"),
            "search_items"          => __("Search Templates", "easy-content-templates"),
            "not_found"             => __("No template found.", "easy-content-templates"),
            "not_found_in_trash"    => __("No template found in Trash.", "easy-content-templates"),
            "parent_item_colon"     => __("Parent Templates:", "easy-content-templates"),
        );
        $supports = array(
            "title",
            "editor",
            "excerpt",
        );
        $arguments = array(
            "label"                 => _x("Templates", "plural name of template", "easy-content-templates"),
            "labels"                => $labels,
            "description"           => __("Enables the use of Templates", "easy-content-templates"),
            "public"                => false,
            "exclude_from_search"   => true,
            "publicly_queryable"    => false,
            "show_ui"               => true,
            "show_in_nav_menus"     => false,
            "show_in_menu"          => true,
            "show_in_admin_bar"     => true,
            "menu_icon"             => "dashicons-tickets-alt",
            "supports"              => $supports,
            "rewrite"               => false,
        );
        register_post_type("ec-template", $arguments);
    }

    static function ac_init(){
        self::register_templates();
    }

    static function ac_add_meta_boxes(){
        $post_types = get_post_types(array(), "objects");
        foreach($post_types as $post_type){
            if($post_type->show_ui){
                if($post_type->name == "ec-template"){
                    add_meta_box("mtb_ect_options", "Template Options", array("ect_template", "rn_metabox_options"), "ec-template", "side", "high");
                }else{
                    add_meta_box("mtb_ect_templates", "Easy Content Template", array("ect_template", "rn_metabox_allothers"), $post_type->name, "side", "high");
                }
            }
        }
    }

    static function ac_admin_enqueue_scripts($hook){
        if($hook == "post-new.php" || $hook == "post.php"){
            global $post;
            if($post->post_type != "ec-template"){
                wp_enqueue_script("ect-templates", ecturl(array("js", "templates.js")), array("jquery"), false, true);
                wp_enqueue_style("ect-templates", ecturl(array("css", "templates.css")));
            }
        }
    }

    static function ac_manage_posts_custom_column($column_name) {
        global $post;
        switch ($column_name) {
            case "template_owner":
                $user = get_user_by("id", $post->post_author);
                echo $user->display_name;
                break;
        }
    }

    static function ac_save_post($post_id){
        // return if doing autosave
        if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
            return;
        }
        
        // return if template doesn't exist
        $current_post = get_post($post_id);
        if($current_post == null){
            return;
        }

        // return if this is just a revision
        if(wp_is_post_revision($post_id)){
            return;
        }
        
        // return if not a template
        if($current_post->post_type != "ec-template"){
            return;
        }
        
        // return if this is a NEW template
        if($current_post->post_status == "auto-draft"){
            return;
        }
        
        // return if no permission to edit a template
        if (!current_user_can("edit_post", $post_id)) {
            return;
        }

        // and save
        update_post_meta($post_id, self::pmk_shared, isset($_POST['ect_share']) && $_POST['ect_share'] == 1 ? 1 : 0);
        update_post_meta($post_id, self::pmk_load_title, isset($_POST['ect_load_title']) && $_POST['ect_load_title'] == 1 ? 1 : 0);
        update_post_meta($post_id, self::pmk_load_content, isset($_POST['ect_load_content']) && $_POST['ect_load_content'] == 1 ? 1 : 0);
        update_post_meta($post_id, self::pmk_load_excerpt, isset($_POST['ect_load_excerpt']) && $_POST['ect_load_excerpt'] == 1 ? 1 : 0);
    }

    static function aj_get_template(){
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Content-type: application/json");
        echo json_encode(self::get_template(isset($_POST["template_id"]) ? intval($_POST["template_id"]) : 0));
        exit;
    }

    static function fl_menu_order($menu){
        $templates_menu_key = -1;
        $templates_menu = "";
        foreach($menu as $menu_key => $menu_item){
            if($menu_item == "edit.php?post_type=ec-template"){
                $templates_menu_key = $menu_key;
                $templates_menu = $menu_item;
            }
        }
        
        if($templates_menu_key != -1){
            unset($menu[$templates_menu_key]);
            $new_menu = array();
            foreach($menu as $menu_item){
                if($menu_item == "edit.php"){
                    $new_menu[] = $templates_menu;
                }
                $new_menu[] = $menu_item;
            }
            $menu = array_values($new_menu);
        }

        return $menu;
    }
    
    static function fl_manage_ec_template_posts_columns($columns){
        unset($columns["date"]);
        if(current_user_can("edit_others_posts")){
            $columns["template_owner"] = "Template Owner";
        }
        return $columns;
    }

    static function fl_pre_get_posts($query){
        if(isset($query->query["post_type"])){
            if($query->query["post_type"] == "ec-template"){
                $query->set("orderby", "menu_order");
                $query->set("order", "ASC");
            }
        }
    }
    
    static function rn_metabox_options($post){
        include ectdir(array("rn", "metabox-template.php"));
    }
    
    static function rn_metabox_allothers($post){
        include ectdir(array("rn", "metabox-all-others.php"));
    }
}

add_action("init", array("ect_template", "ac_init"));
add_action("manage_posts_custom_column", array("ect_template", "ac_manage_posts_custom_column"));
add_action("add_meta_boxes", array("ect_template", "ac_add_meta_boxes"), 99999999);
add_action("admin_enqueue_scripts", array("ect_template", "ac_admin_enqueue_scripts"), 10, 1);
add_action("save_post", array("ect_template", "ac_save_post"));
add_action("wp_ajax_ect_get_template", array("ect_template", "aj_get_template"));
add_filter("custom_menu_order", "__return_true");
add_filter("menu_order", array("ect_template", "fl_menu_order"), 99999999);
add_filter("manage_ec-template_posts_columns", array("ect_template", "fl_manage_ec_template_posts_columns"));
add_filter("pre_get_posts", array("ect_template", "fl_pre_get_posts"));
