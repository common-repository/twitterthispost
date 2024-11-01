<?php
/*
Plugin Name: TwitterThisPost
Plugin URI: http://www.naden.de/blog/twitter-wordpress
Description: Generates a Twitter this link below you posts! Supports #hashtags, title and url shrinking. It's build above the i2h <a href="http://i2h.de">Shorturl</a> Service <a href="http://i2h.de/api">api</a>.
Version: 0.3
Author: Naden Badalgogtapeh
Author URI: http://www.naden.de/blog
*/

/*
 * v0.3 27.04.2010  minor xhtml fix
 * v0.2 26.06.2009  removed www. from www.i2h.de to be even shorter ;)
 * v0.1 18.07.2008  initial release
 */

class TwitterThisPost {
  /**
   * plugin options array
   */
  var $options;
  /**
   * plugin version
   */
  var $version;
  /**
   * plugin id
   */
  var $id;
  /**
   * plugin name incl. version
   */
  var $name;
  /**
   * plugin url
   */
  var $url;

  function TwitterThisPost() {

    global $table_prefix;

    $this->version    = '0.3';
    $this->id         = 'twitterthispost';
    $this->name       = 'TwitterThisPost Plugin v' . $this->version;
    $this->url        = 'http://www.naden.de/blog/twitter-wordpress';
    
    $locale = get_locale();

	  if( empty( $locale ) ) {
		  $locale = 'en_US';
    }

    load_textdomain( $this->id, sprintf( '%s/%s.mo', dirname( __FILE__ ), $locale ) ); 
    
    $this->loadOptions();

    if( is_admin() ) {
      add_action( 'admin_menu', array( &$this, 'optionMenu' ) ); 
    }
    else {
		  add_action( 'wp_head', array( &$this, 'blogHeader' ) );
    }
  }
  
  function optionMenu() {
    add_options_page( 'TwitterThisPost', 'TwitterThisPost', 8, __FILE__, array( &$this, 'optionMenuPage' ) );
  }
  
  function optionMenuPage() {
?>
<div class="wrap">
<h2>TwitterThisPost</h2>
<div align="center"><p><?=$this->name?> <a href="<?php print( $this->url ); ?>" target="_blank">Plugin Homepage</a></p></div> 
<?php
  if( isset( $_POST[ $this->id ] ) ) {
    /**
     * nasty checkbox handling
     */
    foreach( array('inject', 'nofollow', 'target_blank', 'help') as $field ) {
      if( !isset( $_POST[ $this->id ][ $field ] ) ) {
        $_POST[ $this->id ][ $field ] = '0';
      }
    }
    
    $this->updateOptions( $_POST[ $this->id ] );
    
    echo '<div id="message" class="updated fade"><p><strong>' . __( 'Settings saved!', $this->id ) . '</strong></p></div>'; 
  }
?>      
<form method="post" action="options-general.php?page=twitterthispost/twitterthispost.php">

<table class="form-table">

<tr valign="top">
  <th scope="row"><?php _e('Link title', $this->id); ?></th>
  <td><input name="twitterthispost[title]" type="text" id="" class="code" value="<?=$this->options['title']?>" /><br /><?php _e('Title of link to display below posts', $this->id); ?>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Post title', $this->id); ?></th>
  <td><input name="twitterthispost[post_title]" type="text" id="" class="code" value="<?=$this->options['post_title']?>" />
  <br /><?php _e('Max length of post title. <strong><em>"0"</em></strong> for no post title!', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Tags', $this->id); ?></th>
  <td><input name="twitterthispost[hashtags]" type="text" id="" class="code" value="<?=$this->options['hashtags']?>" />
  <br /><?php _e('Max number of #hashtags to include. <strong><em>"0"</em></strong> for no #hashtags!', $this->id); ?></td>
</tr>

<tr valign="top">
  <th scope="row"><?php _e('Additional target text', $this->id); ?></th>
  <td><input name="twitterthispost[target]" type="text" id="" class="code" value="<?=$this->options['target']?>" />
  <br /><?php _e('possible placeholders: [LINK]', $this->id); ?></td>
</tr>

<tr>
<th scope="row" colspan="2" class="th-full">
<label for="">
<input name="twitterthispost[inject]" type="checkbox" id="" value="1" <?php echo $this->options['inject']=='1'?'checked="checked"':''; ?> />
<?php _e('show link on detail page only', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="2" class="th-full">
<label for="">
<input name="twitterthispost[target_blank]" type="checkbox" id="" value="1" <?php echo $this->options['target_blank']=='1'?'checked="checked"':''; ?> />
<?php _e('open link in new window?', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="2" class="th-full">
<label for="">
<input name="twitterthispost[nofollow]" type="checkbox" id="" value="1" <?php echo $this->options['nofollow']=='1'?'checked="checked"':''; ?> />
<?php _e('set link to rel nofollow?', $this->id); ?></label>
</th>
</tr>

<tr>
<th scope="row" colspan="2" class="th-full">
<label for="">
<input name="twitterthispost[help]" type="checkbox" id="" value="1" <?php echo $this->options['help']=='1'?'checked="checked"':''; ?> />
<?php _e('display about twitter icon?', $this->id); ?></label>
</th>
</tr>

</table>

<p class="submit">
<input type="submit" name="Submit" value="<?php _e('save', $this->id); ?>" class="button" />
</p>
</form>

</div>
<?php
  }

  function display() {
  
    if( $this->options[ 'inject' ] == '1' && !is_single() ) {
      return;
    }

    $target = $this->options[ 'target' ];
    
    /**
     * include post title?
     */
    $post_title = intval($this->options[ 'post_title' ]);
    if( $post_title != 0 ) {
      $tmp = get_the_title();

      /**
       * cut down post title if required
       */
      if( strlen( $tmp ) > $post_title ) {
        $tmp = substr( $tmp, 0, $post_title );
      }
      
      $target .= ' ' . $tmp;
  
    }

    /**
     * include post tags
     */
    if( $this->options[ 'hashtags' ] != '0' && function_exists( 'get_the_tags' ) ) {
      $tags = get_the_tags();
      /**
       * reduced to max wanted tags if required
       */
      if( $tags !== false && count( $tags ) > 0 ) {
        $hashtags = intval($this->options[ 'hashtags' ]);
        if( count( $tags ) > $hashtags ) {
          $tags = array_slice( $tags, 0, $hashtags );
        }
  
        foreach( $tags as $tag ) {
          $target .= ' #' . $tag->slug;
        }
      }
    }

    printf( '<a href="#" onclick="location.href=\'http://i2h.de/?url=%s&appid=%s_%s&redirect=http://twitter.com/home/?status=%s\';return false;"%s%s>%s</a>%s',
      urlencode(get_permalink()),
      $this->id,
      $this->version, 
      urlencode( $target ), 
      $this->options['target_blank'] == '1' ? ' target="_blank"' : '',
      $this->options['nofollow'] == '1' ? ' rel="nofollow"' : '',
      $this->options['title'],
      $this->options['help'] == '1' ? sprintf( ' <a href="http://www.naden.de/twitter-wordpress" target="_blank" title="%s"><img src="%s/wp-content/plugins/twitterthispost/img/help.gif" style="vertical-align:bottom;" border="0" /></a>', __( 'what\'s this?' ), get_bloginfo( 'wpurl' ) ) : ''
    );
  }

  function updateOptions( $options ) {
    foreach( $this->options as $k => $v ) {
      if( array_key_exists( $k, $options ) ) {
        $this->options[ $k ] = $options[ $k ];
      }
    }

		update_option( $this->id, $this->options );
	}
  
  function blogHeader() {
    printf( '<meta name="%s" content="%s/%s" />' . "\n", $this->id, $this->id, $this->version );
  }

  function loadOptions() {
    $this->options = get_option( $this->id );

    if( !$this->options ) {
      $this->options = array(
        'installed' => time(),
        'target_blank' => 1,
        'nofollow' => 1,
        'title' => __( 'Post this to Twitter', $this->id ),
        'target' => __( 'Reading: [LINK]', $this->id ),
        'post_title' => 80,
        'hashtags' => 3,
        'help' => 1,
        'inject' => 1
			);
      
#      $this->updateOptions( $this->options );
    
      add_option( $this->id, $this->options, $this->name, 'yes' );
      
      if( is_admin() ) {
        add_filter( 'admin_footer', array( &$this, 'addAdminFooter' ) );
      }
    }
    
    // update
    if(!array_key_exists('help',$this->options)) {
      $this->options['help'] = 1;
      $this->updateOptions( $this->options );
    }
  }
 
  function addAdminFooter() {
    printf( '<img src="http://www.naden.de/gateway/?q=%s" width="1" height="1" />', urlencode( sprintf( 'action=install&plugin=%s&version=%s&platform=%s&url=%s', $this->id, $this->version, 'wordpress', get_bloginfo( 'wpurl' ) ) ) );
  } 
}

add_action( 'plugins_loaded', create_function( '$TwitterThisPost_dkdk3kk23e0', 'global $TwitterThisPost; $TwitterThisPost = new TwitterThisPost();' ) );

function twitter_this_post() {
  
  global $TwitterThisPost;
  
  if( is_callable( array( &$TwitterThisPost, 'display' ) ) ) {
    $TwitterThisPost->display();
  }
  
}

?>