<?php

/*
Plugin Name: SEO Smart Links Professional
Version: 1.8.3
Plugin URI: http://www.prelovac.com/products/seo-smart-links
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
Description: SEO Smart Links Professional provides automatic SEO benefits for your site in addition to custom keyword lists, nofollow and much more.
*/

if (!class_exists('SEOLinksPRO')):
    class SEOLinksPRO
    {
        // Name for our options in the DB
        var $version = '1.8.3';
        var $name = 'SEO Smart Links Pro';
        var $SEOLinks_DB_option = 'SEOLinks';
        var $SEOLinks_options;
        var $key = "SEO Smart Links";
        
        
        var $meta_boxes = array("custom_process" => array("name" => "box_process", "type" => "checkbox", "title" => "Do not process this post", "description" => "Check to instruct SEO Smart Links not to process this post"), "custom_nolink" => array("name" => "box_nolink", "type" => "checkbox", "title" => "Do not link to this post", "description" => "Check to instruct SEO Smart Links not to link to this post"), "post_keywords" => array("name" => "box_post_keywords", "type" => "input", "title" => "Post Keywords", "description" => "Define additional keywords this post will be recognized as and possibly automatically linked to from other posts. Separate keywords with commma or leave empty if you do not want to use this."), "custom_keywords" => array("name" => "box_custom_keywords", "type" => "textarea", "title" => "Custom Keywords", "description" => "Override the global custom keywords settings for this post. Leave empty to enforce global custom keyword rules."), "custom_add" => array("name" => "add_instead_override", "type" => "checkbox", "title" => "Add the keywords to the global list instead of override", "description" => "Entered keywords should be added to the global custom keywords list"));
        //"custom_nofollow" => array("name" => "box_nofollow", "type" => "checkbox", "title" => "Do not add nofollow to external links", "description" => "Check to instruct SEO Smart Links not to add nofollow to external links in this post"), "custom_blank" => array("name" => "box_blank", "type" => "checkbox", "title" => "Do not open external links in new window", "description" => "Check to instruct SEO Smart Links to not open external links in new window for this post"),
        var $cap = 'manage_options';
        
        //var $cap = 'edit_posts'; // for test version
        
        // Initialize WordPress hooks
        function SEOLinksPRO()
        {
            $options = $this->get_options();
            if ($options) {
                if ($options['post'] || $options['page'])
                    add_filter('the_content', array(
                        &$this,
                        'SEOLinks_the_content_filter'
                    ), 200);
                if ($options['comment'])
                    add_filter('comment_text', array(
                        &$this,
                        'SEOLinks_the_content_filter'
                    ), 200);
            }
            
            
            // Add Options Page
            add_action('admin_menu', array(
                &$this,
                'SEOLinks_admin_menu'
            ));
            
            add_action('init', array(
                &$this,
                'redirect_link'
            ));
            
            if ($options['disable_texturize']) {
                remove_filter('the_content', 'wptexturize');
                remove_filter('the_excerpt', 'wptexturize');
                remove_filter('comment_text', 'wptexturize');
                remove_filter('the_title', 'wptexturize');
            }
            if ($options['box_custom']) {
                add_action('admin_menu', array(
                    &$this,
                    'create_meta_box'
                ));
                
                add_action('save_post', array(
                    &$this,
                    'save_meta_box'
                ));
            }
        }
        
        
        function microtime_float()
        {
            list($usec, $sec) = explode(" ", microtime());
            return ((float) $usec + (float) $sec);
        }
        
        function redirect_link()
        {
            $options = $this->get_options();
            
            $reqURL  = $_SERVER['REQUEST_URI'];
            $fullURL = 'http://' . $_SERVER['HTTP_HOST'] . $reqURL;
            
            $hopURL = '/' . $options['base_url'] . '/';
            
            if ($options['base_url'] != '')
                if (stristr($fullURL, $hopURL) !== false) {
                    $reqArr = explode('/', $reqURL);
                    foreach ($reqArr as $key => $token) {
                        if ($token == '') {
                            unset($reqArr[$key]);
                        }
                    }
                    $tag = array_pop($reqArr);
                    $tag = strtok($tag, '?');
                    
                    $reg = '/(https?\:\/\/[^\n]*)\|\s*' . $tag . '/imsuU';
                    //echo $reg; print_r($options['customkey']);
                    preg_match($reg, $options['customkey'], $matches);
                    
                    $urls = explode('|', $matches[1]);
                    $url  = trim($urls[rand(0, count($urls) - 1)]);
                    
                    
                    if ($url) {
                        $redir = trim($url);
                    } else {
                        $redir = get_bloginfo('home');
                    }
                    header("HTTP/1.1 301 Moved Permanently");
                    header('Location: ' . $redir);
                    die();
                }
        }
        
        function sidebar_news()
        {
            return '     
        		<div style="float: right; width:270px; height:800px; margin-left: 10px;" id="sideblock"> 
					<iframe width=270 height=800 frameborder="0" src="http://www.prelovac.com/plugin/news.php?id=101&utm_source=plugin&utm_medium=plugin&utm_campaign=SEO%2BSmart%2BLinks%2BProfessional"></iframe>
					</div>';
        }
        
        function pluralize($word)
        {
            static $rules = array(1 => array('o' => 'oes', 'y' => 'ies', 'x' => 'xes', 'f' => 'ves', 's' => 'ses', 'z' => 'zzes'), 2 => array('sh' => 'shes', 'ch' => 'ches'));
            
            foreach ($rules as $len => $rule) {
                $key = substr($word, -$len);
                if (isset($rule[$key])) {
                    return substr($word, 0, -$len) . $rule[$key];
                }
            }
            
            return $word . 's';
            
        }
        
        function getexcerpt($text, $length = 25)
        {
            $text  = strip_tags($text);
            $words = explode(' ', $text, $length + 1);
            if (count($words) > $length) {
                array_pop($words);
                array_push($words, '[...]');
                $text = implode(' ', $words);
            }
            return $text;
        }
        
        function trim_quote($s)
        {
            return preg_quote(trim($s), '/');
            
        }
        
        function SEOLinks_process_text($text, $recreate_cache = false)
        {
            global $wpdb, $post, $wp_version, $id;
            
            $options    = $this->get_options();
            $min_length = $options['min_length'];
            
            $kw_array = array();
            
            $links         = 0;
            $data          = 0;
            $num_links     = 0;
            $sml_meta_data = '';
            $self_id       = -1;
            
            
            $thisurl = '';
            
            
            if ($options['box_custom']) {
                $sml_meta_data = get_post_meta($post->ID, $this->key, true);
                if ($sml_meta_data['box_process'] == 'on')
                    return $text;
            }
            
            if (is_feed() && !$options['allowfeed'])
                return $text;
            else if (!$recreate_cache && ($options['onlysingle'] && (!is_singular())))
                return $text;
            
            $arrignorepost = $this->explode_trim(",", ($options['ignorepost']));
            
            if (is_page($arrignorepost) || is_single($arrignorepost)) {
                return $text;
            }
            
            if ($post->post_type == 'post' && !$options['post'])
                return $text;
            else if ($post->post_type == 'page' && !$options['page'])
                return $text;
            
            if ($options['skipdays'] > 0) {
                $expire = time() - $options['skipdays'] * 24 * 60 * 60;
                
                if (mysql2date("U", $post->post_date) > $expire)
                    return $text;
            }
            
            if ($options['maxtotallinks'] > 0) {
                $regexp    = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
                $num_links = preg_match_all("/$regexp/siU", $text, $mat_links);
                if ($num_links >= $options['maxtotallinks'])
                    return $text;
                else
                    $options['maxlinks'] = min($options['maxlinks'], max(0, $options['maxtotallinks'] - $num_links));
            }
            
            $maxlinks     = ($options['maxlinks'] > 0) ? $options['maxlinks'] : 0;
            $maxsingle    = ($options['maxsingle'] > 0) ? $options['maxsingle'] : 1;
            $maxsingleurl = ($options['maxsingleurl'] > 0) ? $options['maxsingleurl'] : 0;
            $minusage     = ($options['minusage'] > 0) ? $options['minusage'] : 0;
            
            if (($post->post_type == 'page' && !$options['pageself']) || ($post->post_type == 'post' && !$options['postself'])) {
                $thisurl = trailingslashit(get_permalink($post->ID));
                $self_id = $post->ID;
            }
            
            
            
            $limit = $options['limit_posts'] > 0 ? $options['limit_posts'] : 500;
            
            $urls          = array();
            $keyword_count = array();
            
            $arrignore = $this->explode_trim(",", ($options['ignore']));
            
            if ($options['samecat']) {
                $cats = wp_get_post_categories($post->ID);
                $cats = implode(',', $cats);
            }
            
            $urltemplate = html_entity_decode($options['urltemplate']);
            
            
            
            if ($options['utfsupport'])
                $reg = $options['casesens'] ? '/(?!\pL)($name)(?!\pL)(?!(?:(?!<\/?[ha].*?>).)*<\/[ha].*?>)(?![^<>]*>)/mUu' : '/(?<!\pL)($name)(?!\pL)(?!(?:(?!<\/?[ha].*?>).)*<\/[ha].*?>)(?![^<>]*>)/iumU';
            else
                $reg = $options['casesens'] ? '/\b($name)\b(?!(?:(?!<\/?[ha].*?>).)*<\/[ha].*?>)(?![^<>]*>)/mU' : '/\b($name)\b(?!(?:(?!<\/?[ha].*?>).)*<\/[ha].*?>)(?![^<>]*>)/imU';
            
            
            
            $strpos_fnc = $options['casesens'] ? 'strpos' : 'stripos';
            
            $text = " $text ";
            
            if ($options['box_custom']) {
                if ($sml_meta_data['box_custom_keywords']) {
                    if ($sml_meta_data['add_instead_override'] == 'on')
                        $options['customkey'] = $sml_meta_data['box_custom_keywords'] . "\n" . $options['customkey'];
                    else
                        $options['customkey'] = $sml_meta_data['box_custom_keywords'];
                }
            }
            
            
            
            
            
            if (!empty($options['customkey'])) {
                $line_array = explode("\n", stripslashes($options['customkey']));
                
                foreach ($line_array as $line) {
                    $parts = explode("|", $line);
                    
                    //$keywords = trim($parts[0]);
                    
                    $keywords = ($parts[0]); // no trimming since 1.6.2
                    $uris     = trim($parts[1]);
                    $word     = trim($parts[2]);
                    
                    
                    if ($options['trimspaces'])
                        $chunks = array_map('trim', explode($options['custom_separator'] != '' ? $options['custom_separator'] : ',', $keywords));
                    
                    else
                        $chunks = explode($options['custom_separator'] != '' ? $options['custom_separator'] : ',', $keywords);
                    
                    $url = '';
                    
                    if ($word)
                        $url = get_bloginfo('home') . '/' . $options['base_url'] . '/' . $word;
                    else if (!empty($uris)) {
                        $temp = explode($options['custom_separator'] != '' ? $options['custom_separator'] : ',', $uris);
                        $url  = trim($temp[rand(0, count($temp) - 1)]);
                    }
                    
                    if ($options['check_plural']) {
                        for ($i = 0; $i < count($chunks); $i++)
                            $chunks[$i] = $chunks[$i] . '| ' . $this->pluralize($chunks[$i]);
                    }
                    
                    $total_chunks = count($chunks);
                    $i            = 0;
                    if ($url) {
                        if ($options['customkey_preventduplicatelink'] == TRUE)
                            $kw_array[implode('| ', $chunks)] = $url;
                        else
                            while ($i < $total_chunks) {
                                if (!empty($chunks[$i]))
                                    $kw_array[$chunks[$i]] = $url;
                                $i++;
                            }
                    }
                }
            }
            
            
            $name_plural = '';
            
            foreach ($kw_array as $name => $url) {
                if ($url && (!$maxlinks || ($links < $maxlinks)) && (!$maxsingleurl || $urls[$url] < $maxsingleurl) && (trailingslashit($url) != $thisurl) && !in_array($options['casesens'] ? $name : strtolower($name), $arrignore)) {
                    $chunks_match = explode('| ', $name);
                    
                    $cnt = count($chunks_match);
                    for ($i = 0; $i < $cnt; $i++) {
                        $name = $chunks_match[$i];
                        if ((!$maxsingle || $keyword_count[$name] < $maxsingle) && $strpos_fnc($text, $name) !== false) {
                            $replace = str_replace(array(
                                '{keyword}',
                                '{url}',
                                '{description}'
                            ), array(
                                '$1',
                                $url,
                                '$1'
                            ), $urltemplate);
                            
                            $regexp = str_replace('$name', preg_quote($name, '/'), $reg);
                            
                            $newtext = preg_replace($regexp, $replace, $text, $maxsingle);
                            
                            if ($newtext != $text) {
                                $links++;
                                $text = $newtext;
                                if (!isset($urls[$url]))
                                    $urls[$url] = 1;
                                else
                                    $urls[$url]++;
                                
                                if (!isset($keyword_count[$name]))
                                    $keyword_count[$name] = 1;
                                else
                                    $keyword_count[$name]++;
                                
                                
                                
                                break;
                            }
                        }
                    }
                }
            }
            
            
            
            $urltemplate = str_replace('{url}', '$$$url$$$', $urltemplate);
            
            $porderby = 'ORDER BY ' . ($options['limit_post_order'] == 'date' ? 'post_date ' : 'LENGTH(post_title) ') . ($options['limit_post_sort'] == 'asc' ? 'ASC' : 'DESC');
            
            if ($options['lposts'] || $options['lpages']) {
                if ($options['samecat'] && $cats != '')
                    $query = "
			SELECT DISTINCT post_title, ID, post_type, post_name
			FROM $wpdb->posts wposts
			LEFT JOIN $wpdb->postmeta wpostmeta ON wposts.ID = wpostmeta.post_id 
			LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id)
			LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
			WHERE (post_type='page' 
			OR ($wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id IN($cats)))
			AND post_status = 'publish' AND LENGTH(post_title)>=$min_length $porderby LIMIT $limit";
                else
                    $query = "SELECT post_title, ID, post_type, post_name FROM $wpdb->posts WHERE post_status = 'publish' AND LENGTH(post_title)>=$min_length $porderby LIMIT $limit";
                
                $posts = $wpdb->get_results($query);
                
                
                // $starttime = $this->microtime_float(); 
                
                
                foreach ($posts as $postitem) {
                    if ((($options['lposts'] && $postitem->post_type == 'post') || ($options['lpages'] && $postitem->post_type == 'page')) && (!$maxlinks || ($links < $maxlinks)) && ($postitem->ID != $self_id) && (!in_array(($options['casesens'] ? ($postitem->post_title) : strtolower(($postitem->post_title))), $arrignore))) {
                        $sml_target_data = '';
                        
                        if ($options['box_custom']) {
                            $sml_target_data = get_post_meta($postitem->ID, $this->key, true);
                            
                            if ($sml_target_data['box_nolink'] == 'on') {
                                continue;
                            }
                        }
                        $name = trim($postitem->post_title);
                        
                        
                        $checking  = true;
                        $checkedkw = array();
                        
                        while ($checking) {
                            $found = false;
                            
                            if ((!$maxsingle || $keyword_count[$name] < $maxsingle) && !$checkedkw[$name] && $strpos_fnc($text, $name) !== false) {
                                $found = true;
                            } else if ($options['match_slug']) {
                                $name = str_replace('-', ' ', $postitem->post_name);
                                if (!in_array($name, $arrignore) && (!$maxsingle || $keyword_count[$name] < $maxsingle) && !$checkedkw[$name] && $strpos_fnc($text, $name) !== false)
                                    $found = true;
                                
                            }
                            
                            
                            if (!$found && $sml_target_data['box_post_keywords']) {
                                $chunks_match = array_map('trim', explode(",", $sml_target_data['box_post_keywords']));
                                
                                $cnt = count($chunks_match);
                                for ($i = 0; $i < $cnt; $i++) {
                                    $name = $chunks_match[$i];
                                    if ($name && (!$maxsingle || $keyword_count[$name] < $maxsingle) && !$checkedkw[$name] && $strpos_fnc($text, $name) !== false) {
                                        $found = true;
                                        break;
                                    }
                                }
                            }
                            
                            
                            
                            if ($found) {
                                $regexp = str_replace('$name', preg_quote($name, '/'), $reg);
                                
                                $replace = str_replace('{keyword}', '$1', $urltemplate);
                                
                                if (strpos($urltemplate, '{description}') !== false) {
                                    $query   = "SELECT post_excerpt, post_content FROM $wpdb->posts WHERE post_status = 'publish' AND ID=$postitem->ID LIMIT 1";
                                    $postex  = $wpdb->get_row($query);
                                    $desc    = ($postex->post_excerpt != '') ? $postex->post_excerpt : $this->getexcerpt($postex->post_content, 25);
                                    $replace = str_replace('{description}', $desc, $replace);
                                }
                                
                                $newtext = preg_replace($regexp, $replace, $text, $maxsingle);
                                
                                $checkedkw[$name] = 1;
                                
                                if ($newtext != $text) {
                                    $url = get_permalink($postitem->ID);
                                    if (!$maxsingleurl || $urls[$url] < $maxsingleurl) {
                                        $links++;
                                        
                                        $text = str_replace('$$$url$$$', $url, $newtext);
                                        
                                        if (!isset($urls[$url]))
                                            $urls[$url] = 1;
                                        else
                                            $urls[$url]++;
                                        
                                        if (!isset($keyword_count[$name]))
                                            $keyword_count[$name] = 1;
                                        else
                                            $keyword_count[$name]++;
                                        
                                        if ($urls[$url] >= $maxsingleurl)
                                            $checking = false;
                                    }
                                }
                                
                            } else
                                $checking = false;
                            
                        }
                    }
                }
            }
            /* $endtime = $this->microtime_float(); 
            $totaltime =($endtime - $starttime)*1000;
            
            echo 'Post: '.$totaltime.'<br>';     */
            
            
            $taxs['category'] = $options['lcats'];
            $taxs['post_tag'] = $options['ltags'];
            
            if (version_compare($wp_version, '2.7.9', '>')) {
                $args       = array(
                    'public' => true,
                    '_builtin' => false
                    
                );
                $taxonomies = get_taxonomies($args, 'names');
                $checktax   = $options['tax'];
                
                if ($taxonomies) {
                    foreach ($taxonomies as $taxonomy) {
                        $taxs[$taxonomy] = $options['tax']['smltax_' . $taxonomy];
                        
                    }
                }
                
            }
            
            foreach ($taxs as $tax_slug => $tax_option)
                if ($tax_option) {
                    $query = "SELECT $wpdb->terms.name, $wpdb->terms.term_id FROM $wpdb->terms LEFT JOIN $wpdb->term_taxonomy ON $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id WHERE $wpdb->term_taxonomy.taxonomy = '$tax_slug'  AND LENGTH($wpdb->terms.name)>=$min_length AND $wpdb->term_taxonomy.count >= $minusage ORDER BY LENGTH($wpdb->terms.name) ASC LIMIT $limit";
                    $tags  = $wpdb->get_results($query);
                    
                    
                    foreach ($tags as $tag) {
                        if ((!$maxlinks || ($links < $maxlinks)) && !in_array($options['casesens'] ? $tag->name : strtolower($tag->name), $arrignore)) {
                            if ((!$maxsingle || $keyword_count[$tag->name] < $maxsingle) && $strpos_fnc($text, $tag->name) !== false) {
                                $regexp = str_replace('$name', preg_quote($tag->name, '/'), $reg);
                                
                                $replace = str_replace('{keyword}', '$1', $urltemplate);
                                if (strpos($urltemplate, '{description}') !== false) {
                                    $desc = strip_tags(term_description($tag->term_id, $tax_slug));
                                    if ($desc == '')
                                        $desc = '$1';
                                    
                                    $replace = str_replace('{description}', $desc, $replace);
                                }
                                
                                $newtext = preg_replace($regexp, $replace, $text, $maxsingle);
                                if ($newtext != $text) {
                                    $term = get_term($tag->term_id, $tax_slug);
                                    $url  = get_term_link($term, $tax_slug);
                                    
                                    if (!$maxsingleurl || $urls[$url] < $maxsingleurl) {
                                        $links++;
                                        $text = str_replace('$$$url$$$', $url, $newtext);
                                        
                                        if (!isset($urls[$url]))
                                            $urls[$url] = 1;
                                        else
                                            $urls[$url]++;
                                        
                                        if (!isset($keyword_count[$name]))
                                            $keyword_count[$name] = 1;
                                        else
                                            $keyword_count[$name]++;
                                        
                                    }
                                }
                            }
                        }
                    }
                }
            
            
            
            
            if (($options['blanko'] || $options['nofolo']) && !$recreate_cache) {
                $link = parse_url(get_bloginfo('wpurl'));
                $host = $link['host'];
                
                if ($options['blanko'] && $sml_meta_data['box_blank'] != 'on')
                    $text = preg_replace('%(<a[^>]+)(href="https?://)((?:(?!(' . $host . '))[^"])+)"%i', '$1$2$3" target="_blank" ', $text);
                
                if ($options['nofolo'] && $sml_meta_data['box_nofollow'] != 'on') {
                    $follow_list = '';
                    if ($options['nofollow_whitelist']) {
                        $follow_array   = explode("\n", $options['nofollow_whitelist']);
                        $empty_elements = array_keys($follow_array, "");
                        foreach ($empty_elements as $e)
                            unset($follow_array[$e]);
                        $follow_list = '|(?:www\.)?' . implode('|(?:www\.)?', $follow_array);
                    }
                    
                    $text = preg_replace('%(<a[^>]+)(href="https?://)((?:(?!(' . $host . $follow_list . '))[^"])+)"%i', '$1$2$3" rel="nofollow" ', $text);
                }
                
            }
            return trim($text);
            
        }
        
        function SEOLinks_the_content_filter($text)
        {
            $result = $this->SEOLinks_process_text($text, false);
            
            return $result;
        }
        
        
        function explode_trim($separator, $text)
        {
            $arr = explode($separator, $text);
            
            $ret = array();
            foreach ($arr as $e) {
                $ret[] = trim($e);
            }
            return $ret;
        }
        
        // Handle our options
        function get_options()
        {
            $options = array(
                'post' => 'on',
                'postself' => '',
                'page' => 'on',
                'pageself' => '',
                'comment' => '',
                'lposts' => 'on',
                'lpages' => '',
                'lcats' => '',
                'ltags' => '',
                'ignore' => 'about,contact',
                'ignorepost' => 'contact,',
                'maxlinks' => 3,
                'maxsingle' => 1,
                'minusage' => 1,
                'customkey' => '',
                'customkey_preventduplicatelink' => FALSE,
                'nofoln' => '',
                'nofolo' => '',
                'blankn' => '',
                'blanko' => '',
                'onlysingle' => 'on',
                'casesens' => '',
                'allowfeed' => '',
                'maxsingleurl' => '1',
                'samecat' => 'on',
                'urltemplate' => '<a href="{url}">{keyword}</a>',
                'utfsupport' => '',
                'disable_texturize' => '',
                'match_slug' => '',
                'limit_posts' => 500,
                'customkey_form_file' => '',
                'append_or_replace' => '',
                'box_custom' => '',
                'base_url' => 'go',
                'time' => 0,
                'visual_kw_edit' => '',
                'custom_separator' => ',',
                'min_length' => 5,
                'check_plural' => '',
                'add_instead_override' => '',
                'nofollow_whitelist' => '',
                'maxtotallinks' => 0,
                'limit_post_order' => 'title',
                'limit_post_sort' => 'asc',
                'skipdays' => 0,
                'trimspaces' => 'on'
            );
            
            $saved = get_option($this->SEOLinks_DB_option);
            
            
            if (!empty($saved)) {
                foreach ($saved as $key => $option)
                    $options[$key] = $option;
            }
            
            if ($saved != $options)
                update_option($this->SEOLinks_DB_option, $options);
            
            return $options;
            
        }
        
        
        
        // Set up everything
        function install()
        {
            global $wp_version;
            
            $exit_msg = 'SEO Smart Links requires WordPress 3.0 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
            
            if (version_compare($wp_version, "3.0", "<")) {
                exit($exit_msg);
            }
            
            $SEOLinks_options = $this->get_options();
            
            
        }
        
        
        
        
        
        function getCustomKeywords()
        {
            $options = $this->get_options();
            
            return $options['customkey'];
        }
        
        
        
        function handle_import()
        {
            $options = $this->get_options();
            
            
            if (isset($_POST['submitted'])) {
                check_admin_referer('seo-smart-links');
                
                if ($_POST['Submit'] == 'Import from CSV') {
                    if ($_FILES["csv_file"]["tmp_name"] && ($file_open = file($_FILES["csv_file"]["tmp_name"])) != FALSE) {
                        $values = "\n";
                        $cnt    = 0;
                        foreach ($file_open as $line_num => $line) {
                            $cnt++;
                            $values .= htmlspecialchars($line);
                        }
                        $options['customkey'] = $values;
                        echo '<div class="updated fade"><p>Keyword import successful. (' . $cnt . ' keywords total)</p></div>';
                    } else
                        echo '<div class="updated fade"><p>No file specified, please try again.</p></div>';
                } else if ($_POST['Submit'] == 'Upload') {
                    if ($_FILES["config_file"]["tmp_name"] && ($file_open = file_get_contents($_FILES["config_file"]["tmp_name"])) != FALSE) {
                        $decoded = base64_decode($file_open);
                        
                        $unserialized = unserialize($decoded);
                        
                        if ($unserialized == FALSE)
                            echo '<div class="updated fade"><p>Configuration file corrupted, import aborted.</p></div>';
                        else {
                            $options = $unserialized;
                            echo '<div class="updated fade"><p>Configuration imported.</p></div>';
                        }
                    } else
                        echo '<div class="updated fade"><p>No file specified, please try again.</p></div>';
                }
                $options['time'] = time();
                update_option($this->SEOLinks_DB_option, $options);
            }
            
            $action_url = $_SERVER['REQUEST_URI'];
            $imgpath    = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i';
            $nonce      = wp_create_nonce('seo-smart-links');
            
            $sidebar_news = $this->sidebar_news();
            echo <<<END

	<div class="wrap" >
	<img class="logoimg" src="$imgpath/logo.png" ><h2>$this->name</h2><h3>Import/Export</h3>
	
	<a href="admin.php?page=sml_options">Options</a> &nbsp;&nbsp; <a href="admin.php?page=sml_custom_keywords">Custom Keywords</a> &nbsp;&nbsp; <a href="admin.php?page=sml_import">Import/Export</a>   &nbsp;&nbsp; <a href="admin.php?page=sml_about">About</a>
			
	<div id="poststuff" style="margin-top:10px;">

	
	$sidebar_news
	 <div id="mainblock" >
	 
		<div class="dbx-content">
				
					     <h2>Import/Export Keywords</h2>
					     <p>You can export keywords in .csv file readable in most spreadsheets. Alternatively you can import your saved keywords from a .csv file.</p>
						 	<form name="SEOLinks_import" action="$action_url" method="post" enctype="multipart/form-data">
		 		  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 
					<span style="">
<p>Select a CSV file to import:</p>
					<input type="file" name="csv_file" /><input type="submit" name="Submit" value="Import from CSV" class="button-primary" /> <br><br>
 
<p>Click to export keywords to a CSV file <a class="button-primary" href="$action_url&amp;sml_csv=true" id="save_file_dialog" >Export to CSV</a></p>

					</span>	
</form>

<h2>Import/Export Configuration</h2>
<p>You can import/export your entire configuration.</p>
	<form name="SEOLinks_import_options" action="$action_url" method="post" enctype="multipart/form-data">
		 		  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 
					<span style="">
<p>Select a configuration file to import:</p>
					<input type="file" name="config_file" /><input type="submit" name="Submit" value="Upload" class="button-primary" /> <br><br>
 
<p>Export configuration: <a class="button-primary" href="$action_url&amp;sml_options=true" id="save_file_dialog" >Download</a></p>

					</span>	
</form>


		</div>
		
		<div>
		
		
		</div>
		<br/><br/><h3>&nbsp;</h3>	
	 </div>

	</div>
	
<h5>Another fine WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
</div>
END;
            
            
        }
        
        
        
        function handle_about()
        {
            global $wp_version;
            $options = $this->get_options();
            
            
            
            $action_url = $_SERVER['REQUEST_URI'];
            $nonce      = wp_create_nonce('seo-smart-links');
            
            $imgpath = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i';
            
            
            $lic_msg      = '<p>Welcome to ' . $this->name . '.</p>';
            $sidebar_news = $this->sidebar_news();
            
            echo <<<END

	<div class="wrap" >
	
	<img class="logoimg" src="$imgpath/logo.png" ><h2>$this->name</h2><h3>About</h3>
	
	<a href="admin.php?page=sml_options">Options</a> &nbsp;&nbsp; <a href="admin.php?page=sml_custom_keywords">Custom Keywords</a> &nbsp;&nbsp;  <a href="admin.php?page=sml_import">Import/Export</a> &nbsp;&nbsp; <a href="admin.php?page=sml_about">About</a>
			
	<div id="poststuff" style="margin-top:10px;">

	$sidebar_news

	 <div id="mainblock" >
	 
		<div class="dbx-content">
	 	<form name="SEOLinks_about" action="$action_url" method="post">
		 		  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 			
	$lic_msg				     

	Version: $this->version $upd_msg
</form>	
		</div>
		
		<div>
		
		
		</div>
		<br/><br/><h3>&nbsp;</h3>	
	 </div>

	</div>
	
<h5>Another fine WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
</div>
END;
            
            
        }
        
        
        
        
        function handle_custom_keywords()
        {
            $options = $this->get_options();
            
            
            if (isset($_POST['submitted'])) {
                check_admin_referer('seo-smart-links');
                
                
                
                if ($_POST['Submit'] == 'Import from CSV') {
                    if ($_FILES["csv_file"]["tmp_name"] && ($file_open = file($_FILES["csv_file"]["tmp_name"])) != FALSE) {
                        $values = "\n";
                        $cnt    = 0;
                        foreach ($file_open as $line_num => $line) {
                            $cnt++;
                            $values .= htmlspecialchars($line);
                        }
                        $options['customkey'] = $values;
                        echo '<div class="updated fade"><p>Keyword import successful. (' . $cnt . ' keywords total)</p></div>';
                    } else
                        echo '<div class="updated fade"><p>No file specified, please try again.</p></div>';
                } else if ($_POST['Submit'] == 'Upload') {
                    if ($_FILES["config_file"]["tmp_name"] && ($file_open = file_get_contents($_FILES["config_file"]["tmp_name"])) != FALSE) {
                        //echo($file_open); 
                        $decoded      = base64_decode($file_open);
                        //echo($decoded);
                        $unserialized = unserialize($decoded);
                        
                        if ($unserialized == FALSE)
                            echo '<div class="updated fade"><p>Configuration file corrupted, import aborted.</p></div>';
                        else {
                            $options = $unserialized;
                            echo '<div class="updated fade"><p>Configuration imported.</p></div>';
                        }
                    } else
                        echo '<div class="updated fade"><p>No file specified, please try again.</p></div>';
                } else {
                    $options['customkey']                      = preg_replace('/\r\n|\r/', "\n", $_POST['customkey']);
                    $options['customkey_preventduplicatelink'] = $_POST['customkey_preventduplicatelink'];
                    $options['append_or_replace']              = $_POST['append_or_replace'];
                    $options['visual_kw_edit']                 = $_POST['visual_kw_edit'];
                    $options['custom_separator']               = $_POST['custom_separator'] != '' ? $_POST['custom_separator'] : ',';
                    $options['check_plural']                   = $_POST['check_plural'];
                    $options['trimspaces']                     = $_POST['trimspaces'];
                    
                    
                    echo '<div class="updated fade"><p>Custom keywords options saved.</p></div>';
                    
                }
                $options['time'] = time();
                update_option($this->SEOLinks_DB_option, $options);
                
                
            }
            
            
            
            
            $action_url = $_SERVER['REQUEST_URI'];
            $imgpath    = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i';
            
            
            $customkey                      = htmlspecialchars(stripslashes($options['customkey']));
            $customkey_preventduplicatelink = $options['customkey_preventduplicatelink'] == 'on' ? 'checked' : '';
            $append_or_replace              = $options['append_or_replace'] == 'on' ? 'checked' : '';
            $visual_kw_edit                 = $options['visual_kw_edit'] == 'on' ? 'checked' : '';
            $custom_separator               = stripslashes($options['custom_separator']);
            $check_plural                   = $options['check_plural'] == 'on' ? 'checked' : '';
            $trimspaces                     = $options['trimspaces'] == 'on' ? 'checked' : '';
            
            
            
            
            $lines = explode("\n", $customkey);
            $data  = array();
            $old   = 0;
            foreach ($lines as $k => $v) {
                $parts = explode('|', $v);
                
                if (empty($parts[1])) { // import old settings
                    $old  = 1;
                    $temp = explode($options['custom_separator'], $v);
                    $u    = trim($temp[count($temp) - 1]);
                    unset($temp[count($temp) - 1]);
                    $w = trim(implode($options['custom_separator'], $temp));
                    if ($w && $u)
                        $lines[$k] = $w . '|' . $u;
                } else
                    break;
                
            }
            if ($old)
                $customkey = implode("\n", $lines);
            
            
            if ($options['visual_kw_edit']) {
                //prepare table data
                $customkey = preg_replace('/\r\n|\r/', "\n", $customkey);
                $lines     = explode("\n", $customkey);
                
                $data = array();
                foreach ($lines as $k => $v) {
                    $parts = explode('|', $v);
                    
                    if ($parts[0] && $parts[1]) {
                        $data[$k]['words']    = $parts[0];
                        $data[$k]['link']     = $parts[1];
                        $data[$k]['redirect'] = $parts[2];
                    }
                    
                }
                
                
                $table = '
Search: <input type="text" id="filter">
					<input type="button" style="float:right" id="addrowbutton" name="addrow" value="Add Keyword" class="button-primary"/><br /> 
	<table id="mySortable" class="tablesorter"> 
						<thead> 
					<tr> 
    		<th>Keywords</th> 
				<th>URL</th> 
				<th>Redirect</th> 
				<th>Edit</th> 
				<th>Delete</th> 
	   </tr>
	   <tr id="addrow" style="display:none;">	
				<form> 
				<td><input type="text" name="addword" value="" /></td> 
				<td><input type="text" name="addurl" value="http://" /></td> 
				<td><input type="text" name="addredir" value="" /></td>  
				
				<td> 
					<a href="javascript:void(0);" id="saveadd">Save</a></td>  <td> <a href="javascript:void(0);" id="canceladd">Cancel</a> 
				</td> 
				</form>				
			</tr> 
				</thead> 
	<tbody>
	<tr class="dataline" style="display: none"> 
				<td class="word"></td> 
				<td class="url"></td> 
				<td class="redir"></td> 
				
				<td><a href="javascript:void(0);" class="editlink"><img alt="Edit" style="border: 0px none; margin-left: 5px;" src="' . $imgpath . '/page_edit.gif"/></a><a style="display: none;" href="javascript:void(0);" class="savelink">Save</a></td> 
				<td><a href="javascript:void(0);" class="removelink"><img alt="Remove" style="border: 0px none; margin-left: 5px;" src="' . $imgpath . '/bin.gif"/></a><a style="display: none;" href="javascript:void(0);" class="cancellink">Cancel</a></td> 
			</tr> 
	';
                foreach ($data as $k => $v) {
                    if ($v["words"] && $v["link"])
                        $table .= '<tr class="dataline"> 
				<td class="word">' . $v["words"] . '</td> 
				<td class="url">' . $v["link"] . '</td> 
				<td class="redir">' . $v["redirect"] . '</td> 
				
				<td><a href="javascript:void(0);" class="editlink"><img alt="Edit" style="border: 0px none; margin-left: 5px;" src="' . $imgpath . '/page_edit.gif"/></a><a style="display: none;" href="javascript:void(0);" class="savelink">Save</a></td> 
				<td><a href="javascript:void(0);" class="removelink"><img alt="Remove" style="border: 0px none; margin-left: 5px;" src="' . $imgpath . '/bin.gif"/></a><a style="display: none;" href="javascript:void(0);" class="cancellink">Cancel</a></td> 
			</tr> 
			';
                    
                }
                
                $table .= '</tbody></table>';
            }
            //end prepare table data
            
            $nonce = wp_create_nonce('seo-smart-links');
            
            $dispcustom  = $visual_kw_edit ? 'display: none' : '';
            $dispcustom2 = $visual_kw_edit ? '<br/><br/><a href="#" id="edit_all" />Edit custom keywords >></a>' : '';
            
            echo <<<END

	<div class="wrap" >
		<img class="logoimg" src="$imgpath/logo.png" ><h2>$this->name</h2><h3>Custom Keywords</h3>
	
	<a href="admin.php?page=sml_options">Options</a> &nbsp;&nbsp; <a href="admin.php?page=sml_custom_keywords">Custom Keywords</a> &nbsp;&nbsp;  <a href="admin.php?page=sml_import">Import/Export</a>   &nbsp;&nbsp; <a href="admin.php?page=sml_about">About</a>
			
	<div id="poststuff" style="margin-top:10px;">

	

	 <div id="mainblock" >
	 
		<div class="dbx-content">
	
					<p>Here you can manually extra keywords that you want to automaticaly link. Use comma (or separator you specify) to separate keywords in a group. Then seperate the target URL with vertical bar ('|' character). Enter new set of comma seperated keywords and URL in the new line. You can have these keywords link to any url, not only on your site. If you enter several URLs seperated with comma, plugin will randomly select one of them to link to. If you want to use redirection, add a redirection phrase after the URL, seperated by another vertical bar | character.</p>
					<p>Examples:<br />					
					car, auto, automobile|http://mycarblog.com/ (links any of the keywords to mycarblog.com)<br />	
					lincoln coin, lincoln piece| http://yourcoinsite1.com, http://yourcoinsite2.com, http://yourcoinsite3.com (links any of the keywords randomly to one of the urls specified)<br />          
          lincoln coin, lincoln piece| http://mycoinsite.com| lincoln (links any of the keywords to mycoinsite.com using redirection via word lincoln)<br />							          
					</p>
<h2>Custom keywords settings</h2>
<form name="SEOLinks" action="$action_url" method="post" enctype="multipart/form-data">
  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 
					<input type="checkbox" name="visual_kw_edit" $visual_kw_edit /><label for="visual_kw_edit"> Use visual keyword editor (uses Javascript interface, recommended for small keyword sets only)</label>  <br>
					
					
					
					<input type="checkbox" name="customkey_preventduplicatelink" $customkey_preventduplicatelink /><label for="customkey_preventduplicatelink"> Prevent duplicate links for grouped keywords (will link only first of the keywords found in text)</label>  <br>
					<input type="checkbox" name="check_plural" $check_plural /><label for="check_plural"> Automatically check plural form of the keyword</label>  <br>
					<input type="checkbox" name="trimspaces" $trimspaces /><label for="trimspaces"> Trim spaces from custom keywords</label>  <br>
					Separator for custom keyword list: <input type="text" name="custom_separator" size="1" value="$custom_separator"/>  Use this option if default separator (comma) is used in your keywords or links<br>
					<p>
					<input type="submit" name="Submit" value="Save Options" class="button-primary" />
					$dispcustom2
					
					
								
					</p>


					<textarea name="customkey" id="customkey" rows="25" style="width:100%;$dispcustom">$customkey</textarea>
								
					
					<p id="updatemessage"></p> 
					
					$table
					
					<div class="submit">
	 
					<input type="submit" name="Submit" value="Save Options" class="button-primary" />
					</div>
					</form>


		</div>
		
		<div>
		
		
		</div>
		<br/><br/><h3>&nbsp;</h3>	
	 </div>

	</div>
	
<h5>Another fine WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
</div>
END;
            
        }
        
        
        
        
        
        function handle_options()
        {
            global $wp_version;
            $options = $this->get_options();
            
            $tax    = array();
            $taxout = '';
            
            if (isset($_POST['submitted'])) {
                check_admin_referer('seo-smart-links');
                
                
                
                $options['post']          = $_POST['post'];
                $options['postself']      = $_POST['postself'];
                $options['page']          = $_POST['page'];
                $options['pageself']      = $_POST['pageself'];
                $options['comment']       = $_POST['comment'];
                $options['lposts']        = $_POST['lposts'];
                $options['lpages']        = $_POST['lpages'];
                $options['lcats']         = $_POST['lcats'];
                $options['ltags']         = $_POST['ltags'];
                $options['ignore']        = $_POST['ignore'];
                $options['ignorepost']    = $_POST['ignorepost'];
                $options['maxlinks']      = (int) $_POST['maxlinks'];
                $options['maxtotallinks'] = (int) $_POST['maxtotallinks'];
                $options['maxsingle']     = (int) $_POST['maxsingle'];
                if ($options['maxsingle'] < 1)
                    $options['maxsingle'] = 1;
                $options['maxsingleurl'] = (int) $_POST['maxsingleurl'];
                $options['minusage']     = (int) $_POST['minusage'];
                
                $options['nofoln']            = $_POST['nofoln'];
                $options['nofolo']            = $_POST['nofolo'];
                $options['blankn']            = $_POST['blankn'];
                $options['blanko']            = $_POST['blanko'];
                $options['onlysingle']        = $_POST['onlysingle'];
                $options['casesens']          = $_POST['casesens'];
                $options['allowfeed']         = $_POST['allowfeed'];
                $options['samecat']           = $_POST['samecat'];
                $options['urltemplate']       = stripslashes(htmlspecialchars($_POST['urltemplate']));
                $options['utfsupport']        = $_POST['utfsupport'];
                $options['disable_texturize'] = $_POST['disable_texturize'];
                $options['match_slug']        = $_POST['match_slug'];
                $options['limit_posts']       = (int) $_POST['limit_posts'];
                $options['box_custom']        = $_POST['box_custom'];
                
                $options['base_url']             = $_POST['base_url'];
                $options['min_length']           = (int) $_POST['min_length'];
                $options['add_instead_override'] = $_POST['add_instead_override'];
                $options['nofollow_whitelist']   = preg_replace('/\r\n|\r/', "\n", $_POST['nofollow_whitelist']);
                
                $options['limit_post_order'] = $_POST['limit_posts_order'];
                $options['limit_post_sort']  = $_POST['limit_posts_sort'];
                
                $options['skipdays'] = (int) $_POST['skipdays'];
                
                
                
                
                foreach ($_POST as $key => $value) {
                    if (strpos($key, 'smltax_') !== FALSE)
                        $tax[$key] = 'checked';
                }
                $options['tax']  = $tax;
                $options['time'] = time();
                
                update_option($this->SEOLinks_DB_option, $options);
                
                echo '<div class="updated fade"><p>Options saved.</p></div>';
                
                
            }
            
            
            
            
            $action_url = $_SERVER['REQUEST_URI'];
            
            $post          = $options['post'] == 'on' ? 'checked' : '';
            $postself      = $options['postself'] == 'on' ? 'checked' : '';
            $page          = $options['page'] == 'on' ? 'checked' : '';
            $pageself      = $options['pageself'] == 'on' ? 'checked' : '';
            $comment       = $options['comment'] == 'on' ? 'checked' : '';
            $lposts        = $options['lposts'] == 'on' ? 'checked' : '';
            $lpages        = $options['lpages'] == 'on' ? 'checked' : '';
            $lcats         = $options['lcats'] == 'on' ? 'checked' : '';
            $ltags         = $options['ltags'] == 'on' ? 'checked' : '';
            $ignore        = $options['ignore'];
            $ignorepost    = $options['ignorepost'];
            $maxlinks      = $options['maxlinks'];
            $maxtotallinks = $options['maxtotallinks'];
            $maxsingle     = $options['maxsingle'];
            $maxsingleurl  = $options['maxsingleurl'];
            $minusage      = $options['minusage'];
            
            $nofoln            = $options['nofoln'] == 'on' ? 'checked' : '';
            $nofolo            = $options['nofolo'] == 'on' ? 'checked' : '';
            $blankn            = $options['blankn'] == 'on' ? 'checked' : '';
            $blanko            = $options['blanko'] == 'on' ? 'checked' : '';
            $onlysingle        = $options['onlysingle'] == 'on' ? 'checked' : '';
            $casesens          = $options['casesens'] == 'on' ? 'checked' : '';
            $allowfeed         = $options['allowfeed'] == 'on' ? 'checked' : '';
            $samecat           = $options['samecat'] == 'on' ? 'checked' : '';
            $utfsupport        = $options['utfsupport'] == 'on' ? 'checked' : '';
            $disable_texturize = $options['disable_texturize'] == 'on' ? 'checked' : '';
            $match_slug        = $options['match_slug'] == 'on' ? 'checked' : '';
            $limit_posts       = $options['limit_posts'];
            $box_custom        = $options['box_custom'] == 'on' ? 'checked' : '';
            
            $base_url           = $options['base_url'];
            $min_length         = $options['min_length'];
            $nofollow_whitelist = stripslashes($options['nofollow_whitelist']);
            
            $add_instead_override = $options['add_instead_override'] == 'on' ? 'checked' : '';
            
            $tax = $options['tax'];
            
            $urltemplate = wp_specialchars($options['urltemplate']);
            
            $skipdays = $options['skipdays'];
            
            $limit_post_order_1 = $options['limit_post_order'] == 'title' ? 'selected="selected"' : '';
            $limit_post_order_2 = $options['limit_post_order'] == 'date' ? 'selected="selected"' : '';
            $limit_post_sort_1  = $options['limit_post_sort'] == 'asc' ? 'selected="selected"' : '';
            $limit_post_sort_2  = $options['limit_post_sort'] == 'desc' ? 'selected="selected"' : '';
            
            if (!is_numeric($minusage))
                $minusage = 1;
            
            
            if (version_compare($wp_version, '2.7.9', '>')) {
                $args       = array(
                    'public' => true,
                    '_builtin' => false
                    
                );
                $taxonomies = get_taxonomies($args, 'names');
                
                if ($taxonomies) {
                    foreach ($taxonomies as $taxonomy) {
                        $taxout .= '<input type="checkbox" name="smltax_' . $taxonomy . '" ' . $tax['smltax_' . $taxonomy] . '/><label for="smltax_' . $taxonomy . '"> ' . ucfirst($taxonomy) . '</label>  <br>';
                        
                    }
                }
                
            }
            
            
            $nonce = wp_create_nonce('seo-smart-links');
            
            $imgpath = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i';
            
            
            $sidebar_news = $this->sidebar_news();
            
            
            echo <<<END

<div class="wrap">
	<img class="logoimg" src="$imgpath/logo.png" ><h2>$this->name</h2><h3>Options</h3>
<a href="admin.php?page=sml_options">Options</a> &nbsp;&nbsp; <a href="admin.php?page=sml_custom_keywords">Custom Keywords</a> &nbsp;&nbsp;  <a href="admin.php?page=sml_import">Import/Export</a>   &nbsp;&nbsp; <a href="admin.php?page=sml_about">About</a>
	<div id="poststuff" style="margin-top:10px;">

	$sidebar_news
	 <div id="mainblock" >
	 
		<div class="dbx-content">
		 	<form name="SEOLinks" action="$action_url" method="post">
		 		  <input type="hidden" id="_wpnonce" name="_wpnonce" value="$nonce" />
					<input type="hidden" name="submitted" value="1" /> 
					
					
										
					
					<h2>Processing options</h2>
					<p>SEO Smart Links can process your posts, pages and comments in search for suitable keywords for creating links. Use the checkboxes below to select what should be processed.</p>
					<input type="checkbox" name="post"  $post/><label for="post"> Posts</label>
					<ul>&nbsp;<input type="checkbox" name="postself"  $postself/><label for="postself"> Allow post autolinking to itself</label></ul>
					<br />
					<input type="checkbox" name="page"  $page/><label for="page"> Pages</label>
					<ul>&nbsp;<input type="checkbox" name="pageself"  $pageself/><label for="pageself"> Allow page autolinking to itself</label></ul>
					<br />
				
					Process only posts older than: <input type="text" name="skipdays" size="3" value="$skipdays"/> days  
						<br />
					<br />
					<input type="checkbox" name="comment"  $comment /><label for="comment"> Comments (WARNING: comment links are never cached, be cautious about turning this on for big websites)</label> <br> 
					
					
					<h2>Automatic interlinking</h2>

					<p>SEO Smart Links can check every word in your article against your existing post/page titles or category/tag name in search for a match. If a match is made the word will be linked to the given target. Select what targets should SEO Smart Links consider for automatic link creation.</p>
					<input type="checkbox" name="lposts" $lposts /><label for="lposts"> Posts</label>  <br>
                                        <ul>&nbsp;<input type="checkbox" name="samecat"  $samecat/><label for="samecat"> Link only to posts within the same category</label></ul>
					<ul>&nbsp;<input type="checkbox" name="match_slug"  $match_slug/><label for="match_slug"> Check post slug match (ie. phrase 'Getting to Mars' would link to post with the slug 'getting-to-mars') </label></ul><br>
					<input type="checkbox" name="lpages" $lpages /><label for="lpages"> Pages</label>  <br><br>
					<input type="checkbox" name="lcats" $lcats /><label for="lcats"> Categories</label>  <br>
					<input type="checkbox" name="ltags" $ltags /><label for="ltags"> Tags</label>  <br>
					$taxout
					<br>Link only if taxonomy has been used minimum <input type="text" name="minusage" size="2" value="$minusage"/> times (at least one post is using it).
					<br>Minimum length in characters for term to be considered for autolinking <input type="text" name="min_length" size="2" value="$min_length"/> (does not relate to custom keywords).
					<br><br>
					
					<h2>Misc. Options</h3>

					<p>You can choose to have SEO Smart Links work only on single posts and pages (for example not on main page or archives).</p>
					<input type="checkbox" name="onlysingle" $onlysingle /><label for="onlysingle"> Process only single posts and pages</label>  <br>
					<br>
					<p>You can specify maximum number of posts to be checked as a possible automatic interlinking target. Also you can select criteria for post filtering. Keeping check limit low helps performance. Default is 500, Post title length, Ascending (meaning all of posts on the blog will be sorted from those with smallest title to largest, and first 500 will be checked as possible link targets).</p>
					Check limit: <input type="text" name="limit_posts" size="4" value="$limit_posts"/>  
					order by <select name="limit_posts_order"> 
									<option $limit_post_order_1 value="title">Post title length</option> 									
									<option $limit_post_order_2  value="date">Post date</option> 									
									</select> 
									
									
					<select name="limit_posts_sort"> 
									<option $limit_post_sort_1 value="asc">Ascending</option> 									
									<option $limit_post_sort_2 value="desc">Descending</option> 									
									</select> 
					<br>
					<br>
					<p>Allow processing of RSS feeds. SEO Smart Links will embed links in all posts in your RSS feed (according to other options)</p>
					<input type="checkbox" name="allowfeed" $allowfeed /><label for="allowfeed"> Process RSS feeds</label>  <br>
					<br>					
					<p>Set whether matching should be case sensitive.</p>
					<input type="checkbox" name="casesens" $casesens /><label for="casesens"> Case sensitive matching</label> <br> 
					<br>	
			
					<p>Use this option to disable wptexturize filter which can in some cases interfere with matching algorithm.</p>
					<input type="checkbox" name="disable_texturize" $disable_texturize /><label for="disable_texturize"> Disable Wordpress Texturization</label>  <br>	
					<br>	
					<p>Check to enable UTF-8 support if your site uses non-english characters. Also helps with matching non-letter characters.</p>
					<input type="checkbox" name="utfsupport" $utfsupport /><label for="utfsupprt"> Enable UTF-8 support</label>  <br>	
					<br>
					
					<h2>Override options per post</h2>
					<p>If the following option is checked, you will have an extra box in the write post screen for overriding default rules. If unchecked, only the global rules will apply..</p>
					<input type="checkbox" name="box_custom" $box_custom /><label for="box_custom"> Enable options in post screen</label>  <br>
					&nbsp;&nbsp;&nbsp;
					
					

					<h2>Link Template</h2>
          <p>Allows you to custimize the link HTML template.</p><p>It recognizes special replacements: <em>{keyword}</em> (replaced by the matched keyword), <em>{url}</em> (replaced with the target URL) and <em>{description}</em> (replaced by post excerpt, or taxonomy description).</p>
					<input type="text" name="urltemplate" size="90" value="$urltemplate"/> 
					<br>
					
					<h2>Ignore Posts and Pages</h2>				
					<p>You may wish to prevent SEO Smart Links from generating any links on certain posts or pages. Enter them here either by id, slug or title and separate by comma.</p>	
					<input type="text" name="ignorepost" size="90" value="$ignorepost"/> 
					<br>
                    
					<h2>Ignore keywords</h2>				
					<p>You may wish to prevent certain words or phrases from becoming links. Separate them by comma. Does not exclude custom keywords.</p>
					<input type="text" name="ignore" size="90" value="$ignore"/> 
					<br><br>                 
					 					 
					
				
					<h2>Limits</h2>				
					<p>You can limit the maximum number of different links SEO Smart Links will generate per post. Set to 0 for no limit. </p>
					Maximum Created Links: <input type="text" name="maxlinks" size="2" value="$maxlinks"/>  
					
					<p>You can set the maximum number of links the page is allowed to have (original + SEO Smart Links). If the page has 4 links already and you set this option to 5, SEO Smart Links will generate only up to one link. Set to 0 for no limit. </p>
					Maximum Total Links on page: <input type="text" name="maxtotallinks" size="2" value="$maxtotallinks"/>  
					
					<p>You can limit the maximum number of links created with the same keyword. </p>
					Maximum Same Keyword: <input type="text" name="maxsingle" size="2" value="$maxsingle"/> 
					
					<p>You can limit number of same URLs the plugin will link to. Set to 0 for no limit. Will work only if Max. Same Keyword option is set to 1.</p>
					Maximum Same Target: <input type="text" name="maxsingleurl" size="2" value="$maxsingleurl"/>    
					<br><br>
					 
					<h2>External Links</h2>			
					<p>SEO Smart Links can open links to external sites in new window and add nofollow attribute to them.</p>
					
					<input type="checkbox" name="blanko" $blanko /><label for="blanko"> Open in new window</label>  <br>
				
					<input type="checkbox" name="nofolo" $nofolo /><label for="nofolo"> Add nofollow attribute</label>  <br>
					
					<p>Whitelisted domains for nofollow exclusion (enter one per line, without http://)<p>
					<textarea name="nofollow_whitelist" id="nofollow_whitelist" rows="5" style="width:500px">$nofollow_whitelist</textarea>

					<br>	
					<h2>Redirection support</h2>			
					<p>SEO Smart Links provides easy to use redirection support for custom keywords. Custom keyword links can be redirected through the base path entered below.</p>
				        <p>Default is 'go' (redirection link would be formed as http://yoursite.com/go/word).</p>					
					Redirection base path : <input type="text" name="base_url" size="10" value="$base_url"/> 				    
					

					<br>						

					<div class="submit"><input type="submit" name="Submit" value="Update options" class="button-primary" /></div>
			</form>
		</div>
	
		<br/><br/><h3>&nbsp;</h3>	
	 </div>

	</div>
	
<h5>Another fine WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
</div>
END;
            
            
        }
        
        function SEOLinks_admin_menu()
        {
            $imgpath = trailingslashit(get_option('siteurl')) . 'wp-content/plugins/' . dirname(plugin_basename(__FILE__)) . '/i';
            
            add_menu_page($this->name, $this->name, $this->cap, 'sml_options', array(
                &$this,
                'handle_options'
            ), $imgpath . '/icon.png');
            $page_options = add_submenu_page('sml_options', $this->name . ' Options', 'Options', $this->cap, 'sml_options', array(
                &$this,
                'handle_options'
            ));
            $page_ck      = add_submenu_page('sml_options', $this->name . ' Custom Keywords', 'Custom Keywords', $this->cap, 'sml_custom_keywords', array(
                &$this,
                'handle_custom_keywords'
            ));
            
            
            $page_import = add_submenu_page('sml_options', $this->name . ' Import/Export', 'Import/Export', $this->cap, 'sml_import', array(
                &$this,
                'handle_import'
            ));
            
            $page_about = add_submenu_page('sml_options', $this->name . ' About', 'About', $this->cap, 'sml_about', array(
                &$this,
                'handle_about'
            ));
            
            
            add_action('admin_print_styles-' . $page_options, array(
                &$this,
                'load_script'
            ));
            add_action('admin_print_styles-' . $page_ck, array(
                &$this,
                'load_script'
            ));
            add_action('admin_print_styles-' . $page_import, array(
                &$this,
                'load_script'
            ));
            add_action('admin_print_styles-' . $page_about, array(
                &$this,
                'load_script'
            ));
            
        }
        
        function load_script()
        {
            $options    = $this->get_options();
            $plugin_url = trailingslashit(get_bloginfo('wpurl')) . PLUGINDIR . '/' . dirname(plugin_basename(__FILE__));
            
            wp_enqueue_script('ssl_files_script1', $plugin_url . '/js/tablesorter.js', array(
                'jquery',
                'jquery-form'
            ));
            
            wp_enqueue_script('ssl_files_script2', $plugin_url . '/js/pager.js', array(
                'jquery',
                'jquery-form'
            ));
            //wp_enqueue_script('ssl_files_script3', $plugin_url.'/js/quicksearch.js', array('jquery', 'jquery-form'));
            wp_enqueue_script('ssl_files_script4', $plugin_url . '/js/filter.js', array(
                'jquery',
                'jquery-form'
            ));
            wp_enqueue_script('ssl_files_script5', $plugin_url . '/js/seo-links.js', array(
                'jquery',
                'jquery-form'
            ));
            wp_localize_script('ssl_files_script5', 'SEOSmartOptions', array(
                'separator' => $options['custom_separator']
            ));
            
            wp_enqueue_script('ssl_files_script6', $plugin_url . '/js/jquery.tablednd_0_5.js', array(
                'jquery',
                'jquery-form'
            ));
            
            
            wp_register_style('sml_style', $plugin_url . '/sml.css');
            wp_enqueue_style('sml_style');
        }
        
        
        
        
        
        function create_meta_box()
        {
            if (function_exists('add_meta_box')) {
                add_meta_box('sml_box', 'SEO Smart Links', array(
                    &$this,
                    'display_meta_box'
                ), 'post', 'normal', 'high');
                add_meta_box('sml_box', 'SEO Smart Links', array(
                    &$this,
                    'display_meta_box'
                ), 'page', 'normal', 'high');
            }
        }
        
        function display_meta_box()
        {
            global $post, $options;
            $options = $this->get_options();
?>

	    <div class="form-wrap">

	    <?php
            
            echo '<input type="hidden" name="sml_nonce" id="sml_nonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';
            
            
            foreach ($this->meta_boxes as $meta_box) {
                $data = get_post_meta($post->ID, $this->key, true);
                //if ($options[$meta_box['name']]) {
                    {
?>
	    
	    
	    <?php
                    if ($meta_box['type'] == 'input'):
?>
	    <div class="form-field form-required">
	    <label for="<?php
                        echo $meta_box['name'];
?>"><?php
                        echo $meta_box['title'];
?></label>
	      <input title="<?php
                        echo $meta_box['description'];
?>" type="text" name="<?php
                        echo $meta_box['name'];
?>" value="<?php
                        echo htmlspecialchars($data[$meta_box['name']]);
?>" />
 </div>
	    <?php
                    elseif ($meta_box['type'] == 'textarea'):
?>
<div class="form-field form-required">
<label for="<?php
                        echo $meta_box['name'];
?>"><?php
                        echo $meta_box['title'];
?></label>
	    
	      <textarea title="<?php
                        echo $meta_box['description'];
?>" name="<?php
                        echo $meta_box['name'];
?>" id="<?php
                        echo $meta_box['name'];
?>"  rows=4 style="width:100%;"><?php
                        echo htmlspecialchars($data[$meta_box['name']]);
?></textarea>
 </div>
 <?php
                    elseif ($meta_box['type'] == 'checkbox'):
?>				
	      <input style="margin-left:10px;" title="<?php
                        echo $meta_box['description'];
?>" type="checkbox" name="<?php
                        echo $meta_box['name'];
?>" id="<?php
                        echo $meta_box['name'];
?>"  <?php
                        if ($data[$meta_box['name']] == 'on')
                            echo 'checked';
?> >
	   <?php
                        echo $meta_box['title'];
?>
	    <?php
                    endif;
?>
	   

	    <?php
                }
            }
?>

	    </div>
	    <?php
        }
        
        function save_meta_box($post_id)
        {
            global $post;
            
            
            foreach ($this->meta_boxes as $meta_box) {
                $data[$meta_box['name']] = $_POST[$meta_box['name']];
            }
            
            
            if (!wp_verify_nonce($_POST['sml_nonce'], plugin_basename(__FILE__)))
                return $post_id;
            
            if (!current_user_can('edit_post', $post_id))
                return $post_id;
            
            update_post_meta($post_id, $this->key, $data);
        }
        
        
        
    }
endif;

if (is_admin()) {
    if (isset($_GET['sml_csv']) && ($_GET['sml_csv'] == true)) {
        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        
        header('Content-Type: ' . _get_mime_type());
        header('Expires: ' . $now);
        
        header('Content-Disposition: attachment; filename="seosmartlinks_keywords_' . gmdate('d_m_y') . '.csv"');
        header('Pragma: no-cache');
        $SEOLinks1 = new SEOLinksPRO();
        $options   = $SEOLinks1->getCustomKeywords();
        echo html_entity_decode($options);
        exit;
        
    }
    if (isset($_GET['sml_options']) && ($_GET['sml_options'] == true)) {
        $now = gmdate('D, d M Y H:i:s') . ' GMT';
        
        header('Content-Type: ' . _get_mime_type());
        header('Expires: ' . $now);
        
        header('Content-Disposition: attachment; filename="seosmartlinks_config_' . gmdate('d_m_y') . '.cfg"');
        header('Pragma: no-cache');
        $SEOLinks1 = new SEOLinksPRO();
        $options   = $SEOLinks1->get_options();
        
        $serialized = serialize($options);
        $encoded    = base64_encode($serialized);
        
        echo ($encoded);
        exit;
        
    }
}
function _get_browser_type()
{
    $USER_BROWSER_AGENT = "";
    
    if (ereg('OPERA(/| )([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
        $USER_BROWSER_AGENT = 'OPERA';
    } else if (ereg('MSIE ([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
        $USER_BROWSER_AGENT = 'IE';
    } else if (ereg('OMNIWEB/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
        $USER_BROWSER_AGENT = 'OMNIWEB';
    } else if (ereg('MOZILLA/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
        $USER_BROWSER_AGENT = 'MOZILLA';
    } else if (ereg('KONQUEROR/([0-9].[0-9]{1,2})', strtoupper($_SERVER["HTTP_USER_AGENT"]), $log_version)) {
        $USER_BROWSER_AGENT = 'KONQUEROR';
    } else {
        $USER_BROWSER_AGENT = 'OTHER';
    }
    
    return $USER_BROWSER_AGENT;
}

function _get_mime_type()
{
    $USER_BROWSER_AGENT = _get_browser_type();
    
    $mime_type = ($USER_BROWSER_AGENT == 'IE' || $USER_BROWSER_AGENT == 'OPERA') ? 'application/octetstream' : 'application/octet-stream';
    return $mime_type;
}


if (class_exists('SEOLinksPRO')):
    $SEOLinksPRO = new SEOLinksPRO();
    if (isset($SEOLinksPRO)) {
        register_activation_hook(__FILE__, array(
            &$SEOLinksPRO,
            'install'
        ));
    }
endif;



?>