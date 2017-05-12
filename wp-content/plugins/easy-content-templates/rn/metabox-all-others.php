<?php
$templates_own = get_posts(array(
    "author" => get_current_user_id(),
    "numberposts" => -1,
    "post_type" => "ec-template",
    "post_status" => "publish",
    "orderby" => "title",
    "order" => "ASC",
));
$exclusions = array();
foreach($templates_own as $template_own){
    $exclusions[] = $template_own->ID;
}
$templates_shared = get_posts(array(
    "exclude" => $exclusions,
    "meta_key" => ect_template::pmk_shared,
    "meta_value" => 1,
    "numberposts" => -1,
    "post_type" => "ec-template",
    "post_status" => "publish",
    "orderby" => "title",
    "order" => "ASC",
));
if(empty($templates_own) && empty($templates_shared)){
?>
<p>You haven't defined any templates yet.</p>
<?php
}else{
?>
<p class="template-selection">
    <select id="ddl_ect_mtb_template_id">
        <option value="0">-- select template --</option>
<?php
    if(!empty($templates_own)){
?>
        <optgroup label="Your own templates">
<?php
        foreach($templates_own as $template){
?>
            <option value="<?php echo $template->ID; ?>" data-dlt="<?php echo ect_template::load_title($template->ID) ? "1" : "0"; ?>" data-dlc="<?php echo ect_template::load_content($template->ID) ? "1" : "0"; ?>" data-dle="<?php echo ect_template::load_excerpt($template->ID) ? "1" : "0"; ?>"><?php echo $template->post_title; ?></option>
<?php
        }
?>
        </optgroup>
<?php
    }
    if(!empty($templates_shared)){
?>
        <optgroup label="Templates shared with you">
<?php
        foreach($templates_shared as $template){
?>
            <option value="<?php echo $template->ID; ?>" data-dlt="<?php echo ect_template::load_title($template->ID) ? "1" : "0"; ?>" data-dlc="<?php echo ect_template::load_content($template->ID) ? "1" : "0"; ?>" data-dle="<?php echo ect_template::load_excerpt($template->ID) ? "1" : "0"; ?>"><?php echo $template->post_title; ?></option>
<?php
        }
?>
        </optgroup>
<?php
    }
?>
    </select>
</p>
<ul class="template-parts">
    <li>
        <input type="checkbox" id="chk_ect_load_title" value="1" checked="checked" />
        <label for="chk_ect_load_title">Load the title</label>
    </li>
    <li>
        <input type="checkbox" id="chk_ect_load_content" value="1" checked="checked" />
        <label for="chk_ect_load_content">Load the content</label>
    </li>
    <li>
        <input type="checkbox" id="chk_ect_load_excerpt" value="1" checked="checked" />
        <label for="chk_ect_load_excerpt">Load the excerpt</label>
    </li>
</ul>
<p class="template-load">
    <button id="btn_ect_mtb_load" class="button-secondary">Load Template</button>
</p>
<?php
}
