<?php
/*
Plugin Name: Simple Meta Tags
Description: Allows you to set global meta tags and customize on each individual page/post.
Version: 1.5
Author: Hotscot

Copyright 2011 Hotscot  (email : support@hotscot.net)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/
if (!class_exists("sc_simple_meta_tags")) {
    class sc_simple_meta_tags{

        function __construct() {
            //Admin actions
            add_action('admin_init', array(&$this, 'registerMetaSettings') );
            add_action('admin_menu', array(&$this, 'addAdminPages') );
            add_action('add_meta_boxes', array(&$this, 'addMetaMetaBoxes') );
            add_action("save_post", array(&$this, 'saveMetaData'));

            //Frontend actions
            add_action("wp_head", array(&$this, 'displayOnFrontEnd'));
        }


        /**
         * Run when the plugin is first installed.  It adds options into the wp-options
         */
        function registerMetaSettings(){
            register_setting( 'meta-tag-settings', 'page_meta_title' );
            register_setting( 'meta-tag-settings', 'page_meta_keywords' );
            register_setting( 'meta-tag-settings', 'page_meta_description' );

            register_setting( 'meta-tag-settings', 'post_meta_title' );
            register_setting( 'meta-tag-settings', 'post_meta_keywords' );
            register_setting( 'meta-tag-settings', 'post_meta_description' );

            register_setting( 'meta-tag-settings', 'use_pages_meta_data' );
            register_setting( 'meta-tag-settings', 'use_posts_meta_data' );


            /**
             * The get_option([meta_title,meta_description,meta_keywords]) check is here
             * to allow older versions of the plugin which didn't differentiate between
             * posts and pages to seemlesly upgrade to this version of the plugin
             * without manual intervention.
             *
             * In essence, we:
             * - check if old global value exists
             * - update both page and posts settings to it
             * - set to blank so as not to be used in future
             */
            if(get_option('meta_title') != ''){
                update_option('page_meta_title', get_option('meta_title'));
                update_option('post_meta_title', get_option('meta_title'));
                update_option('meta_title', '');
            }
            if(get_option('meta_description') != ''){
                update_option('page_meta_description', get_option('meta_description'));
                update_option('post_meta_description', get_option('meta_description'));
                update_option('meta_description', '');
            }
            if(get_option('meta_keywords') != ''){
                update_option('page_meta_keywords', get_option('meta_keywords'));
                update_option('post_meta_keywords', get_option('meta_keywords'));
                update_option('meta_keywords', '');
            }
        }


        function addAdminPages(){
            add_options_page('Meta tag defaults', 'Meta Tags', 'manage_options', 'meta_tags', array(&$this, 'renderSettingsPage'));
        }


        function renderSettingsPage(){
            ?>
            <div class="wrap">
                <h1>Simple Meta Tag Options</h1>
                <p>Here you can decide on what sections of the site to use this plugin on, and define the default meta values for those sections.</p>
                <form method="post" action="options.php">
                    <?php settings_fields( 'meta-tag-settings' ); ?>
                    <h2>Pages</h2>
                    <hr>
                    <table class="form-table">


                        <tr valign="top">
                            <th scope="row"><label for="page_meta_title">Title</label></th>
                            <td>
                                <input placeholder="Example Co." size="50" type="text" name="page_meta_title" id="page_meta_title" value="<?php echo get_option('page_meta_title'); ?>" />
                                <p class="description"><strong>Note:</strong> If you leave the title empty it will use the default WordPress Title: <em>blog name | page title</em></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_meta_description">Description</label></th>
                            <td><input placeholder="We make X, Y, and Z" size="50" type="text" name="page_meta_description" id="page_meta_description" value="<?php echo get_option('page_meta_description'); ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="page_meta_keywords">Keywords</label></th>
                            <td><input placeholder="X, Y, Z" size="50" type="text" name="page_meta_keywords" id="page_meta_keywords" value="<?php echo get_option('page_meta_keywords'); ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th><label for="use_pages_meta_data">Use plugin on pages</label></th>
                            <td colspan="2"><label><input type="checkbox" name="use_pages_meta_data" id="use_pages_meta_data" <?php if(get_option("use_pages_meta_data") == "on"){ echo 'checked="checked"'; } ?> /> Tick to use</label></td>
                        </tr>
                    </table>
                    <h2>Posts</h2>
                    <hr>
                    <table class="form-table">

                        <tr valign="top">
                            <th scope="row"><label for="post_meta_title">Title</label></th>
                            <td>
                                <input placeholder="Example Co. Blog" size="50" type="text" name="post_meta_title" id="post_meta_title" value="<?php echo $this->returnFormat(get_option('post_meta_title')); ?>" />
                                <p class="description"><strong>Note:</strong> If you leave the title empty it will use the default WordPress Title: <em>blog name | post title</em></p>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="post_meta_description">Description</label></th>
                            <td><input placeholder="Our blog about X, Y, and Z" size="50" type="text" name="post_meta_description" id="post_meta_description" value="<?php echo $this->returnFormat(get_option('post_meta_description')); ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="post_meta_keywords">Keywords</label></th>
                            <td><input placeholder="Blog, X, Y, Z" size="50" type="text" name="post_meta_keywords" id="post_meta_keywords" value="<?php echo $this->returnFormat(get_option('post_meta_keywords')); ?>" /></td>
                        </tr>

                        <tr valign="top">
                            <th><label for="use_posts_meta_data">Use plugin on posts</label></th>
                            <td colspan="2"><label><input type="checkbox" name="use_posts_meta_data" id="use_posts_meta_data" <?php if(get_option("use_posts_meta_data") == "on"){ echo 'checked="checked"'; } ?> /> Tick to use</label></td>
                        </tr>
                    </table>
                    <hr>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                    </p>
                    <p class="description"><strong>Note to theme developers</strong>&thinsp;&ndash;&thinsp;please remove <code>&lt;title&gt;</code> tag from your template files or there will be duplicate tags.</p>
                </form>
            </div>
            <?php
        }


        function addMetaMetaBoxes() {
            add_meta_box( 'MetaTagsPlugin', 'Simple Meta Tags', array(&$this, 'renderMetaBox'), 'page', 'advanced', 'high' );
            add_meta_box( 'MetaTagsPlugin', 'Simple Meta Tags', array(&$this, 'renderMetaBox'), 'post', 'advanced', 'high' );
        }


        function renderMetaBox(){
            global $post;
            ?>
            <input type="hidden" id="scmesbmt" name="scmesbmt" value="1" />
            <p>
                <label for="scmetatitle">Meta Title</label><br />
                <input placeholder="Example Co." type="text" id="scmetatitle" name="scmetatitle" style="width: 100%" value="<?php echo get_post_meta($post->ID, '_sc_m_title', true); ?>" />
            </p>

            <p>
                <label for="scmetadescription">Meta Description</label><br />
                <input placeholder="We make X, Y, and Z" type="text" id="scmetadescription" name="scmetadescription" style="width: 100%" value="<?php echo get_post_meta($post->ID, '_sc_m_description', true); ?>" />
            </p>

            <p>
                <label for="scmetakeywords">Meta Keywords</label><br />
                <input placeholder="X,Y,Z" type="text" id="scmetakeywords" name="scmetakeywords" style="width: 100%" value="<?php echo get_post_meta($post->ID, '_sc_m_keywords', true); ?>" />
            </p>

            <?php
        }


        function saveMetaData($post_id) {
            //If autosave quit
            if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

            //stop values submitting if this is a "quick edit"
            if(!isset($_POST['scmesbmt'])) return $post_id;

            $scmTitle = isset($_POST['scmetatitle']) ? sanitize_text_field($_POST['scmetatitle']) : '';
            $scmDesc = isset($_POST['scmetadescription']) ? sanitize_text_field($_POST['scmetadescription']) : '';
            $scmKey = isset($_POST['scmetakeywords']) ? sanitize_text_field($_POST['scmetakeywords']) : '';

            update_post_meta($post_id, '_sc_m_title', $scmTitle);
            update_post_meta($post_id, '_sc_m_description', $scmDesc);
            update_post_meta($post_id, '_sc_m_keywords', $scmKey);
        }


        function displayOnFrontEnd(){
            global $post;
            if(!is_404()){
                $isImplemeted = false;
                $meta_title = "";
                $meta_description = "";
                $meta_keywords = "";

                if(is_page() || is_home()){
                    if(get_option('use_pages_meta_data') == 'on'){
                        $isImplemeted = true;
                        $meta_title = (get_post_meta($post->ID, '_sc_m_title', true) != '') ? get_post_meta($post->ID, '_sc_m_title', true) : get_option('page_meta_title');
                        $meta_description = (get_post_meta($post->ID, '_sc_m_description', true) != '') ? get_post_meta($post->ID, '_sc_m_description', true) : get_option('page_meta_description');
                        $meta_keywords = (get_post_meta($post->ID, '_sc_m_keywords', true) != '') ? get_post_meta($post->ID, '_sc_m_keywords', true) : get_option('page_meta_keywords');
                    }
                }

                if(is_single()){
                    if(get_option('use_posts_meta_data') == 'on'){
                        $isImplemeted = true;
                        $meta_title = (get_post_meta($post->ID, '_sc_m_title', true) != '') ? get_post_meta($post->ID, '_sc_m_title', true) : get_option('post_meta_title');
                        $meta_description = (get_post_meta($post->ID, '_sc_m_description', true) != '') ? get_post_meta($post->ID, '_sc_m_description', true) : get_option('post_meta_description');
                        $meta_keywords = (get_post_meta($post->ID, '_sc_m_keywords', true) != '') ? get_post_meta($post->ID, '_sc_m_keywords', true) : get_option('post_meta_keywords');
                    }
                }

                if($isImplemeted){
                    if($meta_title!=''){
                        echo '<title>' . $this->returnFormat($meta_title) . '</title>' . "\n";
                    }else{
                        echo '<title>'. (get_bloginfo('name','display') . wp_title(' | ',false)) .'</title>';
                    }
                    echo '<meta name="description" content="'. $this->returnFormat($meta_description) .'" />' . "\n";
                    echo '<meta name="keywords" content="'. $this->returnFormat($meta_keywords) .'" />' . "\n";
                }else{
                    echo '<title>'. (get_bloginfo('name','display') . wp_title(' | ',false)) .'</title>';
                }
            }
        }


        /* Utility Functions */
        function returnFormat($text){
            return htmlentities(stripslashes($text), ENT_COMPAT, "UTF-8");
        }
    }

    //initialize the class to a variable
    $sc_meta_var = new sc_simple_meta_tags();
}
?>