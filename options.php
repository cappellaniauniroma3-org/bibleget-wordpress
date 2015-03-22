<?php
/** CREATE ADMIN MENU PAGE WITH SETTINGS */
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $options_page_hook;
    private $safe_fonts;
    private $versionsbylang;
    private $versionlangs;
    private $countversionsbylang;
    private $countversionlangs;
    private $biblebookslangs;
    
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
        $this->safe_fonts = array(
    			array("font-family" => "Arial", "fallback" => "Helvetica", "generic-family" => "sans-serif"),
    			array("font-family" => "Arial Black", "fallback" => "Gadget", "generic-family" => "sans-serif"),
    			array("font-family" => "Book Antiqua", "fallback" => "Palatino", "generic-family" => "serif"),
    			array("font-family" => "Courier New", "fallback" => "Courier", "generic-family" => "monospace"),
    			array("font-family" => "Georgia", "generic-family" => "serif"),
    			array("font-family" => "Impact", "fallback" => "Charcoal", "generic-family" => "sans-serif"),
    			array("font-family" => "Lucida Console", "fallback" => "Monaco", "generic-family" => "monospace"),
    			array("font-family" => "Lucida Sans Unicode", "fallback" => "Lucida Grande", "generic-family" => "sans-serif"),
    			array("font-family" => "Palatino Linotype", "fallback" => "Palatino", "generic-family" => "serif"),
    			array("font-family" => "Tahoma", "fallback" => "Geneva", "generic-family" => "sans-serif"),
    			array("font-family" => "Times New Roman", "fallback" => "Times", "generic-family" => "serif"),
    			array("font-family" => "Trebuchet MS", "fallback" => "Helvetica", "generic-family" => "sans-serif"),
    			array("font-family" => "Verdana", "fallback" => "Geneva", "generic-family" => "sans-serif")
    	);
        $this->versionsbylang = array();
        $this->versionlangs = array();
        $this->countversionsbylang = 0;
        $this->countversionlangs = 0;
        $this->biblebookslangs = array();
        
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings" 
        $this->options_page_hook = add_options_page(
            __('BibleGet I/O Settings',"bibleget-io"),	// $page_title
            'BibleGet I/O',								// $menu_title
            'manage_options',							// $capability
            'bibleget-settings-admin',					// $menu_slug (Page ID)
            array( $this, 'create_admin_page' )			// Callback Function
        );
        
        add_action('admin_enqueue_scripts', array( $this, 'admin_print_styles') );
        add_action('admin_enqueue_scripts', array( $this, 'admin_print_scripts') );
        add_action('load-'.$this->options_page_hook, array( $this, 'do_on_my_plugin_settings_save') );
        
        //start populating as soon as possible
        $this->getVersionsByLang();
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        //write_log("creating admin page\n");
        // Set class property
        $this->options = get_option( 'bibleget_settings' );
        if($this->options === false || !isset($this->options['fontstyle_bookchapter']) ){
        	// let's set some default options
        	$this->options = array(
        			'fontstyle_bookchapter' => 'bold',
        			'fontstyle_versenumbers' => 'superscript',
        			'fontstyle_verses' => '',
        			'fontfamily_bibleget' => 'Palatino Linotype',
        			'fontsize_bookchapter' => 14,
        			'fontsize_verses' => 10,
        			'fontsize_versenumbers' => 8,
        			'fontcolor_bookchapter' => '#284f29',
        			'fontcolor_verses' => '#646d73',
        			'fontcolor_versenumbers' => '#c10005',
        			'linespacing_verses' => 150
        	);
        }
        
        $vsnmstyles = array();
        if(isset($this->options['fontstyle_versenumbers'] ) && $this->options['fontstyle_versenumbers']){
        	$vsnmstyles = explode(",",esc_attr( $this->options['fontstyle_versenumbers']));
        }
        
        ?>
        <div id="page-wrap">
            <?php screen_icon(); ?>
            <h2 id="bibleget-h2"><?php _e("BibleGet I/O Settings","bibleget-io") ?></h2>           
            <div id="form-wrapper" class="leftfloat">
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'bibleget_settings_options' );   // $option_group -> match group name in register_setting()
                do_settings_sections( 'bibleget-settings-admin' ); // $page_slug
                submit_button(); 
            ?>
            </form>
            </div>
            <div class="leftfloat">
            	<fieldset id="preview">
            	<legend><?php _e("Preview","bibleget-io") ?></legend>
            		<p class="bibleversion">CEI2008</p>
            		<p class="bookchapter"><span class="biblebook"><?php _e("Genesis","bibleget-io") ?></span> <span class="biblechapter">1</span></p>
            		<p class="verses">
            			<span class="bibleversenumber<?php if(in_array("superscript",$vsnmstyles)){ echo " sup";}else if(in_array("subscript",$vsnmstyle)){ echo " sub";} ?>">1</span><span class="bibleversetext"><?php _e("In the beginning, when God created the heavens and the earth—","bibleget-io") ?></span> <span class="bibleversenumber<?php if(in_array("superscript",$vsnmstyles)){ echo " sup";}else if(in_array("subscript",$vsnmstyle)){ echo " sub";} ?>">2</span><span class="bibleversetext"><?php _e("and the earth was without form or shape, with darkness over the abyss and a mighty wind sweeping over the waters—","bibleget-io") ?></span> <span class="bibleversenumber<?php if(in_array("superscript",$vsnmstyles)){ echo " sup";}else if(in_array("subscript",$vsnmstyle)){ echo " sub";} ?>">3</span><span class="bibleversetext"><?php _e("Then God said: Let there be light, and there was light.","bibleget-io") ?></span>
            		</p>
            	</fieldset>
            </div>
            <div id="page-clear"></div>
        	<div id="bibleget-css-editor">
           		<h3><?php _e("Edit the stylesheet directly","bibleget-io")?></h3>
           		<h4><?php _e("You can edit and save the stylesheet directly, but any changes you make directly to the stylesheet will be overwritten the next time you use the above options form. Also the changes made directly here to the stylesheet will not be reflected in the preview box, they will only be reflected on the pages that contain the shortcode.","bibleget-io") ?></h4>
				<?php $file = plugin_dir_path( __FILE__ ) . 'css/styles.css'; ?>
           		<fieldset id="bibleget-edit-stylesheet">
           		<legend><?php echo $file; ?></legend>
           			<pre><code contenteditable="true" spellcheck="false" id="bibleget-edited-css"><?php 
           				$string = file_get_contents($file);
           				echo $string; 
           			?></code></pre>
           		</fieldset>
           		<button id="bibleget-save-stylesheet-btn" class="button button-primary"><?php _e("SAVE STYLESHEET","bibleget-io") ?></button>
        	</div>
        	<div>
        		<hr>
        		<h3><?php _e("Current BibleGet I/O engine information:","bibleget-io") ?></h3>
        		<ol type="A">
        			<li><?php 
            			if($this->countversionsbylang<1 || $this->countversionlangs<1){
        					echo "Seems like the version info was not yet initialized. Now attempting to initialize...";
							$this->getVersionsByLang();
        				}
        				$b1 = '<b class="bibleget-dynamic-data">';
        				$b2 = '</b>';
        				$string1 = $b1.$this->countversionsbylang.$b2;
        				$string2 = $b1.$this->countversionlangs.$b2;
        				/* translators: please do not change the placeholders %s, they will be substituted dynamically by values in the script. See http://php.net/printf. */
        				printf(__("The BibleGet I/O engine currently supports %s versions of the Bible in %s different languages.","bibleget-io"),$string1,$string2);
        				echo "<br />";
        				_e("Here is the list of currently supported versions, subdivided by language:","bibleget-io");
        				echo "<div class=\"bibleget-dynamic-data-wrapper\"><ol id=\"versionlangs-ol\">";
        				$cc=0;
        				foreach($this->versionlangs as $lang){
        					echo '<li>-'.$lang.'-<ul>';
        					foreach($this->versionsbylang[$lang] as $abbr => $value){
        						echo '<li>'.(++$cc).') '.$abbr.' — '.$value["fullname"].' ('.$value["year"].')</li>';
        					}
        					echo '</ul><div></li>';
        				}
        				echo "</ol>";
        			?></li>
        			<li><?php 
        				$string3 = $b1.count($this->biblebookslangs).$b2;
        				/* translators: please do not change the placeholders %s, they will be substituted dynamically by values in the script. See http://php.net/printf. */
        				printf(__("The BibleGet I/O engine currently recognizes the names of the books of the Bible in %s different languages:","bibleget-io"),$string3); 
        				echo "<br />";
        				echo "<div class=\"bibleget-dynamic-data-wrapper\">".implode(", ",$this->biblebookslangs)."</div>";
        			?></li>
        		</ol>
        		<p><?php _e("This information from the BibleGet server is cached locally to improve performance. If new versions have been added to the BibleGet server or new languages are supported, this information might be outdated. In that case you can click on the button below to renew the information.","bibleget-io"); ?></p>
        		<button id="bibleget-server-data-renew-btn" class="button button-secondary"><?php _e("RENEW INFORMATION FROM BIBLEGET SERVER","bibleget-io") ?></button>
        	</div>
        	<hr>
        	<?php $locale = apply_filters('plugin_locale', get_locale(), 'bibleget-io'); ?>
        	<div id="bibleget-donate"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HDS7XQKGFHJ58"></a><button><img src="<?php echo plugins_url( 'images/btn_donateCC_LG'.($locale ? '-'.$locale : '').'.gif', __FILE__ ); ?>" /></button></a></div>
        </div>
        <?php
    }

    public function get_font_index($fontfamily){
    	foreach($this->safe_fonts as $index => $font){
    		if($font["font-family"] == $fontfamily){ return $index; }
    	}
    	return false;
    }
    
    /**
     * Register and add settings
     */
    public function page_init()
    {        

        register_setting(
            'bibleget_settings_options', // Option group
            'bibleget_settings', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'bibleget_settings_section', // ID
            __('Font & Style Settings',"bibleget-io"), // Title
            array( $this, 'print_section_info' ), // Callback
            'bibleget-settings-admin' // Page
        );  

        add_settings_field(
            'fontfamily_bibleget', // ID
            __('Font Family for Biblical Quotes',"bibleget-io"), // Title 
            array( $this, 'fontfamily_bibleget_callback' ), // Callback
            'bibleget-settings-admin', // Page
            'bibleget_settings_section' // Section           
        );

        add_settings_field(
            'fontsize_bookchapter', 
            __('Font Size for Books and Chapters',"bibleget-io"), 
            array( $this, 'fontsize_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontsize_versenumbers', 
            __('Font Size for Verse Numbers',"bibleget-io"), 
            array( $this, 'fontsize_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontsize_verses', 
            __('Font Size for Text of Verses',"bibleget-io"), 
            array( $this, 'fontsize_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_bookchapter', 
            __('Font Style for Books and Chapters',"bibleget-io"), 
            array( $this, 'fontstyle_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_versenumbers', 
            __('Font Style for Verse Numbers',"bibleget-io"), 
            array( $this, 'fontstyle_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_verses', 
            __('Font Style for Text of Verses',"bibleget-io"), 
            array( $this, 'fontstyle_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_bookchapter', 
            __('Font Color for Book and Chapter',"bibleget-io"), 
            array( $this, 'fontcolor_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_versenumbers', 
            __('Font Color for Verse Numbers',"bibleget-io"), 
            array( $this, 'fontcolor_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_verses', 
            __('Font Color for Text of Verses',"bibleget-io"), 
            array( $this, 'fontcolor_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'linespacing_verses', 
            __('Line-spacing for Verses Paragraphs',"bibleget-io"), 
            array( $this, 'linespacing_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );
        
        add_settings_section(
            'bibleget_settings_section2', // ID
            __('Preferences Settings',"bibleget-io"), // Title
            array( $this, 'print_section_info2' ), // Callback
            'bibleget-settings-admin' // Page
        );
          
        add_settings_field(
            'favorite_version',
            __('Preferred version or versions (when not indicated in shortcode)',"bibleget-io"),
            array( $this, 'favorite_version_callback' ),
            'bibleget-settings-admin',
            'bibleget_settings_section2'
        );

    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {   // use absint for number fields instead of sanitize_text_field
        $new_input = array();
        if( isset( $input['fontfamily_bibleget'] ) )
            $new_input['fontfamily_bibleget'] = sanitize_text_field( $input['fontfamily_bibleget'] );

        if( isset( $input['fontcolor_verses'] ) )
            $new_input['fontcolor_verses'] = sanitize_text_field( $input['fontcolor_verses'] );

        if( isset( $input['fontcolor_bookchapter'] ) )
            $new_input['fontcolor_bookchapter'] = sanitize_text_field( $input['fontcolor_bookchapter'] );

        if( isset( $input['fontcolor_versenumbers'] ) )
            $new_input['fontcolor_versenumbers'] = sanitize_text_field( $input['fontcolor_versenumbers'] );

        if( isset( $input['fontsize_bookchapter'] ) )
            $new_input['fontsize_bookchapter'] = absint( $input['fontsize_bookchapter'] );

        if( isset( $input['fontsize_versenumbers'] ) )
            $new_input['fontsize_versenumbers'] = absint( $input['fontsize_versenumbers'] );

        if( isset( $input['fontstyle_bookchapter'] ) )
            $new_input['fontstyle_bookchapter'] = sanitize_text_field( $input['fontstyle_bookchapter'] );

        if( isset( $input['fontstyle_versenumbers'] ) )
            $new_input['fontstyle_versenumbers'] = sanitize_text_field( $input['fontstyle_versenumbers'] );

        if( isset( $input['fontstyle_verses'] ) )
            $new_input['fontstyle_verses'] = sanitize_text_field( $input['fontstyle_verses'] );

        if( isset( $input['linespacing_verses'] ) )
            $new_input['linespacing_verses'] = absint( $input['linespacing_verses'] );

        if( isset( $input['fontsize_verses'] ) )
            $new_input['fontsize_verses'] = absint( $input['fontsize_verses'] );
        
        if( isset( $input['favorite_version'] ) )
        	$new_input['favorite_version'] = sanitize_text_field($input['favorite_version']);

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print __('Customize the appearance and styling of the Bible Quotations:',"bibleget-io");
    }

    public function print_section_info2()
    {
        print __('Choose your preferences to facilitate the usage of the shortcode:',"bibleget-io");
    }
    
    /** 
     * Get the settings option array and print one of its values
     */
    public function fontfamily_bibleget_callback()
    {    	
    	$fidx = false; 
    	if(isset( $this->options['fontfamily_bibleget'] ) && $this->options['fontfamily_bibleget']) {
    		$ffamily = esc_attr($this->options['fontfamily_bibleget']);
    		$fidx = $this->get_font_index($ffamily);
    	}
    	$style = '';
    	$flbk = '';
    	
    	if($fidx!==false){ 
    		if(isset($this->safe_fonts[$fidx]["fallback"])){
    			$flbk = '&apos;'.$this->safe_fonts[$fidx]["fallback"].'&apos;,';
    		}
    		$style = ' style="font-family:&apos;'.$this->safe_fonts[$fidx]["font-family"].'&apos;,'.$flbk.'&apos;'.$this->safe_fonts[$fidx]["generic-family"].'&apos;;padding:0px;margin:0px;text-align:left;"';
    	}    	 
    	
    	//$style='';
    	echo '<select id="fontfamily_bibleget" name="bibleget_settings[fontfamily_bibleget]"'.$style.'>';
    	foreach($this->safe_fonts as $font){
    		$flbk = '';
    		if(isset($font["fallback"])){
    			$flbk = '&apos;'.$font["fallback"].'&apos;,';
    		}
    		$style = ' style="font-family:&apos;'.$font["font-family"].'&apos;,'.$flbk.'&apos;'.$font["generic-family"].'&apos;;padding:0px;margin:0px;text-align:left;"';
    		$selected = isset( $this->options['fontfamily_bibleget'] ) && esc_attr($this->options['fontfamily_bibleget'])==$font["font-family"] ? " SELECTED" : "";
    		echo '<optgroup'.$style.'><option value="'.$font["font-family"].'" style="padding:0px;margin:0px;text-align:left;"'.$selected.'>'.$font["font-family"].'</option></optgroup>';
    	}
    	echo '</select>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_verses_callback()
    {
    	$sizes = array(4,6,8,9,10,11,12,14,16,18,20,24,28);
    	echo '<select id="fontsize_verses" name="bibleget_settings[fontsize_verses]">';    	
    	foreach($sizes as $size){
    		$selected = '';
    		if(isset( $this->options['fontsize_verses'] ) && $this->options['fontsize_verses']){
    			if($size == esc_attr( $this->options['fontsize_verses'])){
    				$selected = " SELECTED";
    			}
    		}
    		echo "<option value=$size".$selected.">$size</option>";    		
    	}
    	echo '</select>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_bookchapter_callback()
    {
        /*
    	printf(
            '<input type="number" min="4" max="75" id="fontsize_bookchapter" name="bibleget_settings[fontsize_bookchapter]" value="%s" />',
            isset( $this->options['fontsize_bookchapter'] ) && $this->options['fontsize_bookchapter'] ? esc_attr( $this->options['fontsize_bookchapter']) : '12'
        );
        */
    	$sizes = array(4,6,8,9,10,11,12,14,16,18,20,24,28);
    	echo '<select id="fontsize_bookchapter" name="bibleget_settings[fontsize_bookchapter]">';    	
    	foreach($sizes as $size){
    		$selected = '';
    		if(isset( $this->options['fontsize_bookchapter'] ) && $this->options['fontsize_bookchapter']){
    			if($size == esc_attr( $this->options['fontsize_bookchapter'])){
    				$selected = " SELECTED";
    			}
    		}
    		echo "<option value=$size".$selected.">$size</option>";    		
    	}
    	echo '</select>';
    }


    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_versenumbers_callback()
    {
        /*
    	printf(
            '<input type="number" min="4" max="75" id="fontsize_versenumbers" name="bibleget_settings[fontsize_versenumbers]" value="%s" />',
            isset( $this->options['fontsize_versenumbers'] ) && $this->options['fontsize_versenumbers'] ? esc_attr( $this->options['fontsize_versenumbers']) : '7'
        );
        */
    	$sizes = array(4,6,8,9,10,11,12,14,16,18,20,24,28);
    	echo '<select id="fontsize_versenumbers" name="bibleget_settings[fontsize_versenumbers]">';    	
    	foreach($sizes as $size){
    		$selected = '';
    		if(isset( $this->options['fontsize_versenumbers'] ) && $this->options['fontsize_versenumbers']){
    			if($size == esc_attr( $this->options['fontsize_versenumbers'])){
    				$selected = " SELECTED";
    			}
    		}
    		echo "<option value=$size".$selected.">$size</option>";    		
    	}
    	echo '</select>';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontcolor_bookchapter_callback()
    {
        printf(
            '<input type="color" id="fontcolor_bookchapter" name="bibleget_settings[fontcolor_bookchapter]" value="%s" />',
            isset( $this->options['fontcolor_bookchapter'] ) && $this->options['fontcolor_bookchapter'] ? esc_attr( $this->options['fontcolor_bookchapter']) : '#284f29'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontcolor_versenumbers_callback()
    {
        printf(
            '<input type="color" id="fontcolor_versenumbers" name="bibleget_settings[fontcolor_versenumbers]" value="%s" />',
            isset( $this->options['fontcolor_versenumbers'] ) && $this->options['fontcolor_versenumbers'] ? esc_attr( $this->options['fontcolor_versenumbers']) : '#c10005'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontcolor_verses_callback()
    {
        printf(
            '<input type="color" id="fontcolor_verses" name="bibleget_settings[fontcolor_verses]" value="%s" />',
            isset( $this->options['fontcolor_verses'] ) && $this->options['fontcolor_verses'] ? esc_attr( $this->options['fontcolor_verses']) : '#646d73'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontstyle_bookchapter_callback()
    {
        printf(
            '<input type="hidden" id="fontstyle_bookchapter" name="bibleget_settings[fontstyle_bookchapter]" value="%s" />',
            isset( $this->options['fontstyle_bookchapter'] ) && $this->options['fontstyle_bookchapter'] ? esc_attr( $this->options['fontstyle_bookchapter']) : 'bold'
        );
        $styles = array();
        if(isset($this->options['fontstyle_bookchapter'] ) && $this->options['fontstyle_bookchapter']){
        	$styles = explode(",",esc_attr( $this->options['fontstyle_bookchapter']));
        }
        ?>
	    <div id="bookchapter_styles" class="bibleget-buttonset">
	      <input type="checkbox" id="bkchbld" <?php if(in_array("bold",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Bold" style in a toolbar */ 
	      ?>
	      <label for="bkchbld" style="font-weight:bold;"><?php _e("B","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="bkchitlc" <?php if(in_array("italic",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Italic" style in a toolbar */ 
	      ?>
	      <label for="bkchitlc" style="font-weight:bold;font-style:italic;"><?php _e("I","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="bkchundr" <?php if(in_array("underline",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Underline" style in a toolbar */ 
	      ?>
	      <label for="bkchundr" style="text-decoration:underline;"><?php _e("U","bibleget-io") ?></label>

	      <input type="checkbox" id="bkchstrk" <?php if(in_array("strikethrough",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Strikethrough" style in a toolbar */ 
	      ?>
	      <label for="bkchstrk" style="text-decoration:line-through;"><?php _e("S","bibleget-io") ?></label>
	    </div>
        
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontstyle_versenumbers_callback()
    {
        printf(
            '<input type="hidden" id="fontstyle_versenumbers" name="bibleget_settings[fontstyle_versenumbers]" value="%s" />',
            isset( $this->options['fontstyle_versenumbers'] ) && $this->options['fontstyle_versenumbers'] ? esc_attr( $this->options['fontstyle_versenumbers']) : 'superscript'
        );
        $styles = array();
        if(isset($this->options['fontstyle_versenumbers'] ) && $this->options['fontstyle_versenumbers']){
        	$styles = explode(",",esc_attr( $this->options['fontstyle_versenumbers']));
        }
        ?>
	    <div id="versenumber_styles" class="bibleget-buttonset">
	      <input type="checkbox" id="vsnmbld" <?php if(in_array("bold",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Bold" style in a toolbar */ 
	      ?>
	      <label for="vsnmbld" style="font-weight:bold;"><?php _e("B","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="vsnmitlc" <?php if(in_array("italic",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Italic" style in a toolbar */ 
	      ?>
	      <label for="vsnmitlc" style="font-weight:bold;font-style:italic;"><?php _e("I","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="vsnmundr" <?php if(in_array("underline",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Underline" style in a toolbar */ 
	      ?>
	      <label for="vsnmundr" style="text-decoration:underline;"><?php _e("U","bibleget-io") ?></label>

	      <input type="checkbox" id="vsnmstrk" <?php if(in_array("strikethrough",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Strikethrough" style in a toolbar */ 
	      ?>
	      <label for="vsnmstrk" style="text-decoration:line-through;"><?php _e("S","bibleget-io") ?></label>

	      <input type="checkbox" id="vsnmsup" class="supersub" <?php if(in_array("superscript",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: letters that can represent "Superscript" style in a toolbar */ 
	      ?>
	      <label for="vsnmsup"><span style="font-size:0.7em;vertical-align:baseline;position:relative;top:-0.6em;"><?php _e("SUP","bibleget-io") ?></span></label>

	      <input type="checkbox" id="vsnmsub" class="supersub" <?php if(in_array("subscript",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: letters that can represent "Subscript" style in a toolbar */ 
	      ?>
	      <label for="vsnmsub"><span style="font-size:0.7em;vertical-align:baseline;position:relative;bottom:-0.6em;"><?php _e("SUB","bibleget-io") ?></span></label>
	    </div>
        
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontstyle_verses_callback()
    {
        printf(
            '<input type="hidden" id="fontstyle_verses" name="bibleget_settings[fontstyle_verses]" value="%s" />',
            isset( $this->options['fontstyle_verses'] ) && $this->options['fontstyle_verses'] ? esc_attr( $this->options['fontstyle_verses']) : ''
        );
        $styles = array();
        if(isset( $this->options['fontstyle_verses'] ) && $this->options['fontstyle_verses']){
        	$styles = explode(",",esc_attr( $this->options['fontstyle_verses']));
        }
        ?>
	    <div id="versetext_styles" class="bibleget-buttonset">
	      <input type="checkbox" id="vstxbld" <?php if(in_array("bold",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Bold" style in a toolbar */ 
	      ?>
	      <label for="vstxbld" style="font-weight:bold;"><?php _e("B","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="vstxitlc" <?php if(in_array("italic",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Italic" style in a toolbar */ 
	      ?>
	      <label for="vstxitlc" style="font-weight:bold;font-style:italic;"><?php _e("I","bibleget-io") ?></label>
	 
	      <input type="checkbox" id="vstxundr" <?php if(in_array("underline",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Underline" style in a toolbar */ 
	      ?>
	      <label for="vstxundr" style="text-decoration:underline;"><?php _e("U","bibleget-io") ?></label>

	      <input type="checkbox" id="vstxstrk" <?php if(in_array("strikethrough",$styles)){ echo 'checked="checked"'; } ?>>
	      <?php 
	      /* translators: the letter that can represent "Strikethrough" style in a toolbar */ 
	      ?>
	      <label for="vstxstrk" style="text-decoration:line-through;"><?php _e("S","bibleget-io") ?></label>
	    </div>
        
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function linespacing_verses_callback()
    {
        /*
    	printf(
            '<input type="number" min="100" max="200" id="linespacing_verses" name="bibleget_settings[linespacing_verses]" value="%s" />',
            isset( $this->options['linespacing_verses'] ) && $this->options['linespacing_verses'] ? esc_attr( $this->options['linespacing_verses']) : '150'
        );
        */
    	$vals = array(100,150,200);
    	/* translators: "single" refers to line-spacing: "single line-spacing" */
    	$single = __("single","bibleget-io");
    	/* translators: "double" refers to line-spacing: "double line-spacing" */
    	$double = __("double","bibleget-io");   
    	$lbls = array($single,"1½",$double);
    	echo '<select id="linespacing_verses" name="bibleget_settings[linespacing_verses]">';
    	foreach($vals as $idx => $val){
			$selected = '';
			if(isset( $this->options['linespacing_verses'] ) && $this->options['linespacing_verses']){
				if($val == esc_attr( $this->options['linespacing_verses'])){
					$selected = " SELECTED";
				}
			}
			echo '<option value="'.$val.'"'.$selected.'>'.$lbls[$idx].'</option>';
    	}	
    	echo '</select>';
    	
    }
	
    public function getVersionsByLang()
    {
    	global $langcodes;
    	global $worldlanguages;
    	//$locale = substr(get_locale(),0,2);
    	$domain = 'bibleget-io';
    	$locale = substr(apply_filters('plugin_locale', get_locale(), $domain),0,2);
    	//echo "<div style=\"border:3px solid Red;\">locale = $locale</div>";
    	$biblebookslangs = get_option("bibleget_languages");
    	//$biblebookslangs = false;
    	if($biblebookslangs === false || !is_array($biblebookslangs) || count($biblebookslangs) < 1 ){
    		SetOptions();
    		$biblebookslangs = get_option("bibleget_languages");
    	}
    	//echo "<div style=\"border:3px solid Red;\">biblebookslangs = ".print_r($biblebookslangs,true)."</div>";
    	$this->biblebookslangs = array();
    	foreach($biblebookslangs as $key => $lang){
    		if(isset($worldlanguages[$lang][$locale])){
    			$lang = $worldlanguages[$lang][$locale];
    		}
    		array_push($this->biblebookslangs,$lang);
    	}
    	
    	write_log($this->biblebookslangs);
    	 
    	if(extension_loaded('intl') === true){
    		collator_asort(collator_create('root'), $this->biblebookslangs);
    	}else{
    		array_multisort(array_map('Sortify', $this->biblebookslangs), $this->biblebookslangs);
    	}
    	write_log($this->biblebookslangs); 
    	
    	$versions = get_option("bibleget_versions",array()); //theoretically should be an array
    	$versionsbylang = array();
    	$langs = array();
    	if(count($versions)<1){
    		SetOptions(); //global function defined in bibleget-io.php
    		$versions = get_option("bibleget_versions",array());
    	}
    	foreach($versions as $abbr => $versioninfo){
    		$info = explode("|",$versioninfo);
    		$fullname = $info[0];
    		$year = $info[1];
    		$lang = $langcodes[$info[2]]; //this gives the english correspondent of the two letter ISO code
    		if(isset($worldlanguages[$lang][$locale])){
    			$lang = $worldlanguages[$lang][$locale]; //this will translate the English form into the localized form if available
    		}
    		if(isset($versionsbylang[$lang])){
    			if(isset($versionsbylang[$lang][$abbr])){
    				//how can that be?
    			}
    			else{
    				$versionsbylang[$lang][$abbr] = array("fullname"=>$fullname,"year"=>$year);
    			}
    		}
    		else{
    			$versionsbylang[$lang] = array();
    			array_push($langs,$lang);
    			$versionsbylang[$lang][$abbr] = array("fullname"=>$fullname,"year"=>$year);
    		}
    	}
    	$this->versionsbylang = $versionsbylang;
    	 
    	//count total languages and total versions
    	$this->countversionlangs = count($versionsbylang);
    	$counter = 0;
    	foreach($versionsbylang as $lang => $versionbylang){
    		ksort($versionsbylang[$lang]);
    		$counter+=count($versionsbylang[$lang]);
    	}
    	$this->countversionsbylang = $counter;
    	
    	if(extension_loaded('intl') === true){
    		collator_asort(collator_create('root'), $langs);
    	}else{
    		array_multisort(array_map('Sortify', $langs), $langs);
    	}

    	$this->versionlangs = $langs;
    	
    }
    
    public function favorite_version_callback()
    {
		//double check to see if the values have been set
    	if($this->countversionsbylang<1 || $this->countversionslangs<1){
			$this->getVersionsByLang();
		}
    	
		$counter = ($this->countversionsbylang + $this->countversionlangs);
				
		$selected = array();
		if(isset( $this->options['favorite_version'] ) && $this->options['favorite_version']){
			$selected = explode(",",$this->options['favorite_version']);
		}
    	$size = $counter<10 ? $counter : 10;
		echo '<select id="versionselect" size='.$size.' multiple>';
    	
    	$langs = $this->versionlangs;
    	$versionsbylang = $this->versionsbylang;
    	
    	foreach($langs as $lang){
    		echo '<optgroup label="-'.$lang.'-">';
			foreach($versionsbylang[$lang] as $abbr => $value){
				$selectedstr = '';
				if(in_array($abbr,$selected)){ $selectedstr = " SELECTED"; }
				echo '<option value="'.$abbr.'"'.$selectedstr.'>'.$abbr.' — '.$value["fullname"].' ('.$value["year"].')</option>';
    		}
			echo '</optgroup>';
    	}
    	echo '</select>';
    	echo '<input type="hidden" id="favorite_version" name="bibleget_settings[favorite_version]" value="" />';
    }
    
    public function admin_print_styles($hook)
    {
        if($hook == 'settings_page_bibleget-settings-admin'){
    		wp_enqueue_style( 'admin-css', plugins_url('css/admin.css', __FILE__) );
    	}
    }

    public function admin_print_scripts($hook)
    {
        //echo "<div style=\"border:10px ridge Blue;\">$hook</div>";
    	if($hook != 'settings_page_bibleget-settings-admin'){
    		return;
		}
		$handle = 'jquery-ui';
    	$list = 'registered';
    	if (!wp_script_is( $handle, $list )) {
			wp_register_script( $handle, '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js', array('jquery'));
    		wp_enqueue_script( $handle );
    	}

    	wp_enqueue_style('jquery-ui-smoothness', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/sunny/jquery-ui.css', false, null);
    	
    	wp_register_script( 'admin-js', plugins_url('js/admin.js', __FILE__), array($handle) );
    	$thisoptions = get_option( 'bibleget_settings' );
    	$myoptions = array();
    	if($thisoptions){
	    	foreach($thisoptions as $key => $option){
	    		$myoptions[$key] = esc_attr($option);
	    	}
    	}
    	$safefonts = array(
    			array("font-family" => "Arial", "fallback" => "Helvetica", "generic-family" => "sans-serif"),
    			array("font-family" => "Arial Black", "fallback" => "Gadget", "generic-family" => "sans-serif"),
    			array("font-family" => "Book Antiqua", "fallback" => "Palatino", "generic-family" => "serif"),
    			array("font-family" => "Courier New", "fallback" => "Courier", "generic-family" => "monospace"),
    			array("font-family" => "Georgia", "generic-family" => "serif"),
    			array("font-family" => "Impact", "fallback" => "Charcoal", "generic-family" => "sans-serif"),
    			array("font-family" => "Lucida Console", "fallback" => "Monaco", "generic-family" => "monospace"),
    			array("font-family" => "Lucida Sans Unicode", "fallback" => "Lucida Grande", "generic-family" => "sans-serif"),
    			array("font-family" => "Palatino Linotype", "fallback" => "Palatino", "generic-family" => "serif"),
    			array("font-family" => "Tahoma", "fallback" => "Geneva", "generic-family" => "sans-serif"),
    			array("font-family" => "Times New Roman", "fallback" => "Times", "generic-family" => "serif"),
    			array("font-family" => "Trebuchet MS", "fallback" => "Helvetica", "generic-family" => "sans-serif"),
    			array("font-family" => "Verdana", "fallback" => "Geneva", "generic-family" => "sans-serif")
    	);
    	$obj = array("options" => $myoptions,"safe_fonts" => $safefonts,"savecss" => plugins_url( 'savecss.php', __FILE__ ),'ajax_url' => admin_url( 'admin-ajax.php' ),'ajax_nonce' => wp_create_nonce( "bibleget-data" ));
    	wp_localize_script( 'admin-js', 'obj', $obj );
    	wp_enqueue_script( 'admin-js' );
    }
    
    public function do_on_my_plugin_settings_save()
    {
      //print("\n Page with hook ".$this->options_page_hook." was loaded and load hook was called.");
      //exit;
      if(isset($_GET['settings-updated']) && $_GET['settings-updated']){
          //plugin settings have been saved. Here goes your code
          $this->options = get_option( 'bibleget_settings' );
          if($this->options === false || !isset($this->options['fontstyle_bookchapter']) ){
          	// let's set some default options
          	$this->options = array(
          		'fontstyle_bookchapter' => 'bold',
				'fontstyle_versenumbers' => 'superscript',
				'fontstyle_verses' => '',
				'fontfamily_bibleget' => 'Palatino Linotype',
				'fontsize_bookchapter' => 14,
				'fontsize_verses' => 10,
				'fontsize_versenumbers' => 8,
				'fontcolor_bookchapter' => '#284f29',
				'fontcolor_verses' => '#646d73',
				'fontcolor_versenumbers' => '#c10005',
				'linespacing_verses' => 150
          	);
          }
          $bookchapter_bold = false;
          $bookchapter_italic = false;
          $bookchapter_underline = false;
          $bookchapter_strikethrough = false;
          
          if(isset($this->options['fontstyle_bookchapter']) && $this->options['fontstyle_bookchapter'] ){
            $bookchapter_style = explode(",",$this->options['fontstyle_bookchapter']);
            foreach($bookchapter_style as $value){
              if($value === "bold"){
                $bookchapter_bold = true;
              }
              if($value === "italic"){
                $bookchapter_italic = true;
              }
              if($value === "underline"){
                $bookchapter_underline = true;
              }
              if($value === "strikethrough"){
                $bookchapter_strikethrough = true;
              }
            }
          }
          
          $versenumbers_bold = false;
          $versenumbers_italic = false;
          $versenumbers_underline = false;
          $versenumbers_strikethrough = false;
          $versenumbers_superscript = false;
          $versenumbers_subscript = false;
          
          if(isset($this->options['fontstyle_versenumbers']) && $this->options['fontstyle_versenumbers'] ){
            $versenumbers_style = explode(",",$this->options['fontstyle_versenumbers']);
            foreach($versenumbers_style as $value){
              if($value === "bold"){
                $versenumbers_bold = true;
              }
              if($value === "italic"){
                $versenumbers_italic = true;
              }
              if($value === "underline"){
                $versenumbers_underline = true;
              }
              if($value === "strikethrough"){
                $versenumbers_strikethrough = true;
              }
              if($value === "superscript"){
                $versenumbers_superscript = true;
              }
              if($value === "subscript"){
                $versenumbers_subscript = true;
              }
            }
          }
          
          $verses_bold = false;
          $verses_italic = false;
          $verses_underline = false;
          $verses_strikethrough = false;
          
          if(isset($this->options['fontstyle_verses']) && $this->options['fontstyle_verses'] ){
            $verses_style = explode(",",$this->options['fontstyle_verses']);
            foreach($verses_style as $value){
              if($value === "bold"){
                $verses_bold = true;
              }
              if($value === "italic"){
                $verses_italic = true;
              }
              if($value === "underline"){
                $verses_underline = true;
              }
              if($value === "strikethrough"){
                $verses_strikethrough = true;
              }
            }
          }
          $ff = false;
          if(isset($this->options['fontfamily_bibleget']) && $this->options['fontfamily_bibleget']){ $ff = $this->get_font_index($this->options['fontfamily_bibleget']); }
          
          $cssdata = ""
            ."div.results { \n"
            ."  border: 1px solid LightGray; \n"
            ."  border-radius:6px; \n"
            ."  padding: 12px; \n"
            ."  margin:12px auto; \n"
            ."  width: 80%; \n"
            ."  font-family: " .($ff !== false ? "'".$this->safe_fonts[$ff]["font-family"]."',".(isset($this->safe_fonts[$ff]["fallback"]) ? "'".$this->safe_fonts[$ff]["fallback"]."',":"")."'".$this->safe_fonts[$ff]["generic_family"]."'" : "'Palatino Linotype',Palatino,serif"). "; \n"
            ."} \n"
            ."\n"
            ."div.results p.book { \n"
            ."  font-weight: ".($bookchapter_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($bookchapter_italic ? "italic" : "normal")."; \n"
            ."  text-decoration: ".($bookchapter_underline ? "underline" : ($bookchapter_strikethrough ? "line-through" : "none"))."; \n"
            ."  font-size: ".(isset($this->options['fontsize_bookchapter']) && $this->options['fontsize_bookchapter'] ? number_format(($this->options['fontsize_bookchapter'] / 10),1,'.','') : "1.2" )."em; \n"
            ."  color: ".(isset($this->options['fontcolor_bookchapter']) && $this->options['fontcolor_bookchapter'] ? $this->options['fontcolor_bookchapter'] : "DarkRed")."; \n"
            ."  margin: 0px; \n"
            ."} \n"
            ."\n"
            ."div.results p.verses { \n"
            ."  font-weight: ".($verses_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($verses_italic ? "italic" : "normal")."; \n"
            ."  text-decoration: ".($verses_underline ? "underline" : ($verses_strikethrough ? "line-through" : "none"))."; \n"
            ."  line-height: ".(isset($this->options['linespacing_verses']) && $this->options['linespacing_verses'] ? $this->options['linespacing_verses'] : "150" )."%; \n"
            ."  color: ".(isset($this->options['fontcolor_verses']) && $this->options['fontcolor_verses'] ? $this->options['fontcolor_verses'] : "DarkGray")."; \n"
            ."  text-align: justify; \n"
            ."  font-size: ".(isset($this->options['fontsize_verses']) && $this->options['fontsize_verses'] ? number_format(($this->options['fontsize_verses'] / 10),1,'.','') : "1" )."em; \n"
            ."  margin: 0px; \n"
            ."} \n"
            ." \n"
            ."div.results p.verses span.sup { \n"
            ."  font-size: ".(isset($this->options['fontsize_versenumbers']) && $this->options['fontsize_versenumbers'] ? number_format(($this->options['fontsize_versenumbers'] / 10),1,'.','') : "0.8" )."em; \n"
            ."  vertical-align: baseline; \n"
            ."  position: relative; \n"
            ."  font-weight: ".($versenumbers_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($versenumbers_italic ? "italic" : "normal")."; \n"
        	."  text-decoration: ".($versenumbers_underline ? "underline" : ($versenumbers_strikethrough ? "line-through" : "none"))."; \n"
        	."  color: ".(isset($this->options['fontcolor_versenumbers']) && $this->options['fontcolor_versenumbers'] ? $this->options['fontcolor_versenumbers'] : "Red")."; \n"
        	."  ".($versenumbers_superscript ? "top: -0.6em" : ($versenumbers_subscript ? "bottom: -0.6em" : "top: 0em") )."; \n"
            ."} \n"
        	." \n"
        	."/* Senseline. A line that is broken to be reading aloud/public speaking. Poetry is included in this category. */ \n"
			."div.results p.verses span.pof {display: block; text-indent: 0; font-style:italic; margin-top:1em; margin-left:5%; font-family:Serif; } \n"
			."div.results p.verses span.po {display: block; font-style:italic; margin-left:5%; margin-top:-1%; font-family:Serif; } \n"
			."div.results p.verses span.pol {display: block; font-style:italic; margin-left:5%; margin-top:-1%; margin-bottom:1em; font-family:Serif; } \n"
			."div.results p.verses span.pos {display: block; font-style:italic; margin-top:1em; margin-left:5%; font-family:Serif; } \n"
			."div.results p.verses span.poif {display: block; font-style:italic; margin-left:7%; margin-top:1%; font-family:Serif; } \n"
			."div.results p.verses span.poi {display: block; font-style:italic; margin-left:7%; margin-top:-1%; font-family:Serif; } \n"
			."div.results p.verses span.poil {display: block; font-style:italic; margin-left:7%; margin-bottom:1%; font-family:Serif; } \n";
          $cssdata = trim($cssdata);
          $file = plugin_dir_path( __FILE__ ) . 'css/styles.css';
          if(file_exists($file)){
            if(file_put_contents ($file,$cssdata)){
              //print("\n Successfully wrote to file ".$file."! These are the contents we wrote: &lt;&lt;&lt;".$cssdata."&gt;&gt;&gt; \n");
              //print("Value of this->options['fontfamily_bibleget'] is ".$this->options['fontfamily_bibleget']);
            }
            else{
				$notices = get_option('bibleget_error_admin_notices', array());
				$notices[] = __("There was an error saving the settings data. You may have to try again.","bibleget-io");
				update_option('bibleget_error_admin_notices',$notices);
            }
          }
          else{
            $cssfile = fopen($file, "w") or die("Unable to open file!");
            fwrite($cssfile,$cssdata);
            fclose($cssfile);
          }
       }
    }

}