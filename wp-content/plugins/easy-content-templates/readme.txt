=== Easy Content Templates ===
Contributors: japaalekhinllemos
Donate link: http://wpsolutions.llemos.com/easy-content-templates-wordpress-plugin#donate
Tags: content templates, writing, posts, templates
Requires at least: 4.0
Tested up to: 4.1
Stable tag: 1.4.3

This plugin lets you define content templates to quickly and easily create new posts or pages.

== Description ==

Easy Content Templates is a plugin for WordPress that lets you define content templates to quickly apply to new or existings posts and pages. By default, templates are private to the author/creator only. Templates can be shared to other users by checking the "Make this Template available for others" checkbox. Other users can't edit your template, they can only use it.

[Visit plugin site &raquo;](http://wpsolutions.llemos.com/easy-content-templates-wordpress-plugin "Easy Content Templates WordPress Plugin by WP Solutions")
[Support development &raquo;](http://wpsolutions.llemos.com/easy-content-templates-wordpress-plugin#donate "Support the development of Easy Content Templates")

== Installation ==

Just install like you normally do with a plugin.

1. Upload `easy-content-templates` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done

== Frequently Asked Questions ==

= How do I create templates? =

In your WordPress admin, usually under the *Comments* section, there will be a *Templates* section (much like *Posts* and *Pages*). Hover over it and click *Add New*.

= How do I insert them into my posts or pages? =

In your write screen look to the upper right and there's a box there. Select a template from the drop-down and click "Load Template".

= Nothing shows up in the drop-down list, what do I do? =

First you need to define your templates, go to the *Templates* section of your WordPress admin and create your templates there.

= Can I define multiple templates? =

Sure you can! ;) Templates are actually Custom Post Types so you can make a lot of templates.

= What does the "Share this Template with others." check box do? =

If you define a template and check that box, it means that you are allowing users to use your template. That is, they will be able to create posts or pages using that template. This does not mean that they will see your template in the administration area or they will be able to edit it.

== Screenshots ==

1. Step 1. Template Definition - You need to add templates first before you can use them.
2. Step 2. Select a Template - When creating (or editing) a post or page, there's an "Easy Content Template" box to the upper right. Click the drop-down box to select the Template you want to load from the list. Click *Load Template*.
2. Step 3. Template Usage - After the Template finishes loading, you can now build upon the template by editing the post or page and filling in values.

== Changelog ==

= 1.4.3.1 =
* FIXED: debugging info error printed when saving posts

= 1.4.3 =
* FIXED: undefined when loading content in HTML/Text view

= 1.4.2 =
* FIXED: undefined error in line 234

= 1.4.1 =
* UPDATED: descriptions, versions, and links
* UPDATED: screenshots
* FIXED: default loading options, now defaults to checked as it should be

= 1.4.0 =
* REWRITE: the plugin was rewritten from the ground up to accommodate additional features
* REMOVED: shortcode processing on title
* REMOVED: the date shortcode functionality (to be replaced by a better mechanism)
* FIXED: the excerpt part is now working
* CHANGED: templates are no longer shared to others by default (check it manually if you want to share)
* ADDED: default loading options for parts of templates


= 1.3.1 =
* remove the limit on admins when viewing templates - they can now see all templates created by other authors

= 1.3 =
* added the long awaited timeout fix
* added a disable while loading feature for the load template button
* added the [postdate] shortcode (with formatting options) feature (also applies to titles)
* cleaned up the code a little bit (you won't see this as it's under the hood)

= 1.2.2 =
* added a fix to the bug in 1.2.1, if you upgraded to 1.2.1 you SHOULD get this

= 1.2.1 =
* sorted the templates in the Templates admin section
* added a visual editor detection feature to disable switching back to visual view if the user was originally in html view

= 1.2 =
* fixed a sorting problem in the drop-down box
* added a message if you click Load Template and no Template is selected
* updated the icon for Templates post type
* updated the F.A.Q. section
* updated screenshots so it shows the updated plugin
* updated text from "make this template public" to "share this template"

= 1.1 =
* changed the way the plugin fetches the ajax url to fix some issues
* removed filters when loading a template to disable processing shortcodes (and other codes)
* made the dropdown span the whole width of the container box so it looks better
* added check boxes below the dropdown for selective loading (title, content, excerpt)
* added a `-- select template --` item in the dropdown as first item
* made the templates private (to the author/creator) by default, with an option to make it public
* updated the readme file so tells you to upload `easy-content-templates` instead of `plugin-name.php`
* removed the limit of templates loaded in the dropdown

= 1.0 =
* added wp_enqueue_script('jquery') to init action to make sure jquery is loaded
* fixed readme so it provides more instructions and some screenshots

= 0.9 =
* creation

== Upgrade Notice ==

= 1.3.1 =
Very minor upgrade, you can ignore unless your administrators need to see all templates

= 1.3 =
New features, a bug fix and donation button.

= 1.2.2 =
CRITICAL BUG FIXED! If you upgraded to 1.2.1, you SHOULD get this!

= 1.2.1 =
Some bug fixed, please refer to the change log.

= 1.2 =
Minor bug fixed, icons updated and fully tested.

= 1.1 =
New features, safe update!

= 1.0 =
Upgrade as you like. Not much change here.

= 0.9 =
Don't upgrade! This is the first version. hehe