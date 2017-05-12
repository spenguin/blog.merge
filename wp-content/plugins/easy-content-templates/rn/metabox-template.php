<h4>Sharing</h4>
<p>Would you like to share this template with other users?</p>
<p>
    <input type="checkbox" id="chk_ect_share" name="ect_share" value="1"<?php echo ect_template::is_shared($post->ID) ? " checked=\"checked\"" : ""; ?> />
    <label for="chk_ect_make_public">Yes please!</label>
</p>
<hr />
<h4>Default Loading</h4>
<p>Choose which parts of this template should be loaded by default</p>
<ul class="template-parts">
    <li>
        <input type="checkbox" id="chk_ect_load_title" name="ect_load_title" value="1"<?php echo ect_template::load_title($post->ID) ? " checked=\"checked\"" : ""; ?> />
        <label for="chk_ect_load_title">Title</label>
    </li>
    <li>
        <input type="checkbox" id="chk_ect_load_content" name="ect_load_content" value="1"<?php echo ect_template::load_content($post->ID) ? " checked=\"checked\"" : ""; ?> />
        <label for="chk_ect_load_content">Content</label>
    </li>
    <li>
        <input type="checkbox" id="chk_ect_load_excerpt" name="ect_load_excerpt" value="1"<?php echo ect_template::load_excerpt($post->ID) ? " checked=\"checked\"" : ""; ?> />
        <label for="chk_ect_load_excerpt">Excerpt</label>
    </li>
</ul>
<hr />
<h4>Support Development</h4>
<p>If you like this functionality or find it useful, <a href="http://wpsolutions.llemos.com/easy-content-templates-wordpress-plugin#donate">please consider a donation</a>.</p>