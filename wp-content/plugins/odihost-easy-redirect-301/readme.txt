=== Easy 301 Redirects ===
Contributors: odihost
Tags: 301, redirect, easy 301 redirect
Requires at least: 3.0
Tested up to: 4.6
Stable tag: 1
Version: 1.32
License: GPLv2 or later
Donate link: http://odihost.com/contact-us/
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easy 301 Redirects provides an easy method of redirecting requests to another page on your site or elsewhere on the web.

== Description ==

Easy 301 Redirects provides an easy method of redirecting requests to another page on your site or elsewhere on the web. This is useful when you are migrating sites and can not preserve the url and want to redirect it safely to another page. With 301 redirect, it will pass your backlink score to the new page which is good for SEO. You can add the destination url when you edit/add a page/post and then through Settings > Easy 301 Redirects Creator you will get a list of 301 redirect setting which you can save in Settings > Easy 301 Redirects

== Installation ==

1. Upload Easy 301 Redirects to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add redirects on the Settings > Easy 301 Redirects.

== Frequently Asked Questions ==

What should I enter in the setting page?

Use this format [old url,new url]. For example: 

/blog,http://www.google.com, will redirect yourdomain.com/blog to http://www.google.com. Please don't use bracket in your setting.

You can also use wildcard using *

/blog*,http://www.google.com, will redirect yourdomain.com/blogabc to http://www.google.com. 

/blog/*,http://www.google.com, will redirect yourdomain.com/blog/abc to http://www.google.com.

/document/*/doc,yourdomain.com/#1#/new-doc, will redirect yourdomain.com/document/123/doc to yourdomain.com/123/new-doc

/document/*/doc/*,yourdomain.com/#1#/new-doc/#2#, will redirect yourdomain.com/document/123/doc/456 to yourdomain.com/123/new-doc/456


== Screenshots ==

Screenshot of the setting can be seen here http://odihost.com/easy-redirect.png

== Upgrade Notice ==

No upgrade is needed for now

== Changelog ==

= 1.0 =
* Initial Release

= 1.2 =
* Adjust FAQ and added wildcard function

= 1.3 =
* Added wildcard function for redirect destination 