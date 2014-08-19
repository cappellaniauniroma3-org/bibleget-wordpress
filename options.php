<?php
/** CREATE ADMIN MENU PAGE WITH SETTINGS */
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $options_page_hook;
    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings" 
        $this->options_page_hook = add_options_page(
            'BibleGet IO Settings',               // $page_title
            'BibleGet IO Settings',               // $menu_title
            'manage_options',                     // $capability
            'bibleget-settings-admin',            // $menu_slug (Page ID)
            array( $this, 'create_admin_page' )   // Callback Function
        );
        
        add_action('admin_enqueue_scripts', array( $this, 'admin_print_styles') );
        add_action('load-'.$this->options_page_hook, array( $this, 'do_on_my_plugin_settings_save') );

    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'bibleget_settings' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2 id="bibleget-h2">BibleGet IO Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'bibleget_settings_options' );   // $option_group -> match group name in register_setting()
                do_settings_sections( 'bibleget-settings-admin' ); // $page_slug
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
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
            'Font & Style Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'bibleget-settings-admin' // Page
        );  


        add_settings_field(
            'fontfamily_bibleget', // ID
            'Font Family per le Citazioni Bibliche', // Title 
            array( $this, 'fontfamily_bibleget_callback' ), // Callback
            'bibleget-settings-admin', // Page
            'bibleget_settings_section' // Section           
        );

        add_settings_field(
            'fontsize_bookchapter', 
            'Font Size for Books and Chapters', 
            array( $this, 'fontsize_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontsize_versenumbers', 
            'Font Size for Verse Numbers', 
            array( $this, 'fontsize_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontsize_verses', 
            'Font Size for Text of Verses', 
            array( $this, 'fontsize_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_bookchapter', 
            'Font Style for Books and Chapters (can use any combination of the following values, separated by comma: bold,italic)', 
            array( $this, 'fontstyle_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_versenumbers', 
            'Font Style for Verse Numbers (can use any combination of the following values, separated by comma: bold,italic,superscript)', 
            array( $this, 'fontstyle_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontstyle_verses', 
            'Font Style for Verses (can use any combination of the following values, separated by comma: bold,italic)', 
            array( $this, 'fontstyle_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_bookchapter', 
            'Font Color for Book and Chapter', 
            array( $this, 'fontcolor_bookchapter_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_versenumbers', 
            'Font Color for Verse Numbers', 
            array( $this, 'fontcolor_versenumbers_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'fontcolor_verses', 
            'Font Color for Text of Verses', 
            array( $this, 'fontcolor_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
        );

        add_settings_field(
            'linespacing_verses', 
            'Line-spacing for Verses Paragraphs (in percentage, between 100 and 200 percent)', 
            array( $this, 'linespacing_verses_callback' ), 
            'bibleget-settings-admin', 
            'bibleget_settings_section'
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

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Customize the appearance and styling of the Bible Quotations:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontfamily_bibleget_callback()
    {
        printf(
            '<input type="text" id="fontfamily_bibleget" name="bibleget_settings[fontfamily_bibleget]" value="%s" />',
            isset( $this->options['fontfamily_bibleget'] ) && $this->options['fontfamily_bibleget'] ? esc_attr( $this->options['fontfamily_bibleget']) : 'Georgia'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_verses_callback()
    {
        printf(
            '<input type="number" min="4" max="75" id="fontsize_verses" name="bibleget_settings[fontsize_verses]" value="%s" />',
            isset( $this->options['fontsize_verses'] ) && $this->options['fontsize_verses'] ? esc_attr( $this->options['fontsize_verses']) : '10'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_bookchapter_callback()
    {
        printf(
            '<input type="number" min="4" max="75" id="fontsize_bookchapter" name="bibleget_settings[fontsize_bookchapter]" value="%s" />',
            isset( $this->options['fontsize_bookchapter'] ) && $this->options['fontsize_bookchapter'] ? esc_attr( $this->options['fontsize_bookchapter']) : '12'
        );
    }


    /** 
     * Get the settings option array and print one of its values
     */
    public function fontsize_versenumbers_callback()
    {
        printf(
            '<input type="number" min="4" max="75" id="fontsize_versenumbers" name="bibleget_settings[fontsize_versenumbers]" value="%s" />',
            isset( $this->options['fontsize_versenumbers'] ) && $this->options['fontsize_versenumbers'] ? esc_attr( $this->options['fontsize_versenumbers']) : '7'
        );
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
            '<input type="text" id="fontstyle_bookchapter" name="bibleget_settings[fontstyle_bookchapter]" value="%s" />',
            isset( $this->options['fontstyle_bookchapter'] ) && $this->options['fontstyle_bookchapter'] ? esc_attr( $this->options['fontstyle_bookchapter']) : 'bold'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontstyle_versenumbers_callback()
    {
        printf(
            '<input type="text" id="fontstyle_versenumbers" name="bibleget_settings[fontstyle_versenumbers]" value="%s" />',
            isset( $this->options['fontstyle_versenumbers'] ) && $this->options['fontstyle_versenumbers'] ? esc_attr( $this->options['fontstyle_versenumbers']) : 'superscript'
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fontstyle_verses_callback()
    {
        printf(
            '<input type="text" id="fontstyle_verses" name="bibleget_settings[fontstyle_verses]" value="%s" />',
            isset( $this->options['fontstyle_verses'] ) && $this->options['fontstyle_verses'] ? esc_attr( $this->options['fontstyle_verses']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function linespacing_verses_callback()
    {
        printf(
            '<input type="number" min="100" max="200" id="linespacing_verses" name="bibleget_settings[linespacing_verses]" value="%s" />',
            isset( $this->options['linespacing_verses'] ) && $this->options['linespacing_verses'] ? esc_attr( $this->options['linespacing_verses']) : '150'
        );
    }

    public function admin_print_styles()
    {
        wp_enqueue_style( 'admin-css', plugins_url('css/admin.css', __FILE__) );
    }

    public function do_on_my_plugin_settings_save()
    {
      //print("\n Page with hook ".$this->options_page_hook." was loaded and load hook was called.");
      //exit;
      if(isset($_GET['settings-updated']) && $_GET['settings-updated'])
       {
          //plugin settings have been saved. Here goes your code
          $this->options = get_option( 'bibleget_settings' );
          
          $bookchapter_bold = false;
          $bookchapter_italic = false;
          
          if(isset($this->options['fontstyle_bookchapter']) && $this->options['fontstyle_bookchapter'] ){
            $bookchapter_style = explode(",",$this->options['fontstyle_bookchapter']);
            foreach($bookchapter_style as $value){
              if($value == "bold"){
                $bookchapter_bold = true;
              }
              if($value == "italic"){
                $bookchapter_italic = true;
              }
            }
          }
          
          $versenumbers_bold = false;
          $versenumbers_italic = false;
          $versenumbers_superscript = false;
          
          if(isset($this->options['fontstyle_versenumbers']) && $this->options['fontstyle_versenumbers'] ){
            $versenumbers_style = explode(",",$this->options['fontstyle_versenumbers']);
            foreach($versenumbers_style as $value){
              if($value == "bold"){
                $versenumbers_bold = true;
              }
              if($value == "italic"){
                $versenumbers_italic = true;
              }
              if($value == "superscript"){
                $versenumbers_superscript = true;
              }
            }
          }
          
          $verses_bold = false;
          $verses_italic = false;
          
          if(isset($this->options['fontstyle_verses']) && $this->options['fontstyle_verses'] ){
            $verses_style = explode(",",$this->options['fontstyle_verses']);
            foreach($verses_style as $value){
              if($value == "bold"){
                $verses_bold = true;
              }
              if($value == "italic"){
                $verses_italic = true;
              }
            }
          }
          
          $cssdata = ""
            ."div.results { \n"
            ."  border: 1px solid LightGray; \n"
            ."  border-radius:6px; \n"
            ."  padding: 12px; \n"
            ."  margin:12px auto; \n"
            ."  width: 80%; \n"
            ."  font-family: " .(isset($this->options['fontfamily_bibleget']) && $this->options['fontfamily_bibleget'] ? $this->options['fontfamily_bibleget'] : "'Palatino Linotype'"). "; \n"
            ."  font-size: .8em; \n"
            ."} \n"
            ."\n"
            ."div.results p.book { \n"
            ."  font-weight: ".($bookchapter_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($bookchapter_italic ? "italic" : "normal")."; \n"
            ."  font-size: ".(isset($this->options['fontsize_bookchapter']) && $this->options['fontsize_bookchapter'] ? number_format(($this->options['fontsize_bookchapter'] / 10),1,'.','') : "1.2" )."em; \n"
            ."  color: ".(isset($this->options['fontcolor_bookchapter']) && $this->options['fontcolor_bookchapter'] ? $this->options['fontcolor_bookchapter'] : "DarkRed")."; \n"
            ."  margin: 0px;"
            ."} \n"
            ."\n"
            ."div.results p.verses { \n"
            ."  font-weight: ".($verses_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($verses_italic ? "italic" : "normal")."; \n"
            ."  line-height: ".(isset($this->options['linespacing_verses']) && $this->options['linespacing_verses'] ? $this->options['linespacing_verses'] : "150" )."%; \n"
            ."  color: ".(isset($this->options['fontcolor_verses']) && $this->options['fontcolor_verses'] ? $this->options['fontcolor_verses'] : "DarkGray")."; \n"
            ."  text-align: justify; \n"
            ."  font-size: ".(isset($this->options['fontsize_verses']) && $this->options['fontsize_verses'] ? number_format(($this->options['fontsize_verses'] / 10),1,'.','') : "1" )."em; \n"
            ."  margin: 0px;"
            ."} \n"
            ." \n"
            ."div.results p.verses span.sup,span.sub { \n"
            ."  font-size: ".(isset($this->options['fontsize_versenumbers']) && $this->options['fontsize_versenumbers'] ? number_format(($this->options['fontsize_versenumbers'] / 10),1,'.','') : ".7" )."em; \n"
            ."  vertical-align: baseline; \n"
            ."  position: relative; \n"
            ."} \n"
            ." \n"
            ."div.results p.verses span.sup { \n"
            ."  font-weight: ".($versenumbers_bold ? "bold" : "normal")."; \n"
            ."  font-style: ".($versenumbers_italic ? "italic" : "normal")."; \n"
            ."  color: ".(isset($this->options['fontcolor_versenumbers']) && $this->options['fontcolor_versenumbers'] ? $this->options['fontcolor_versenumbers'] : "Red")."; \n"
            ."  top: ".($versenumbers_superscript ? "-0.6" : "0")."em; \n"
            ."} \n"
            ."div.results p.verses span.sub { \n"
            ."  font-size: 70%; \n"
            ."  color: Red; \n"
            ."  bottom: -0.6em; \n"
            ."  display: none; \n"
            ."}";
          $file = plugin_dir_path( __FILE__ ) . 'css/styles.css';
          if(file_exists($file)){
            //print("\n File Exists! Now writing to file ".$file."\n");
            if(file_put_contents ($file,$cssdata)){
              //print("\n Successfully wrote to file ".$file."! These are the contents we wrote: &lt;&lt;&lt;".$cssdata."&gt;&gt;&gt; \n");
              //print("Value of this->options['fontfamily_bibleget'] is ".$this->options['fontfamily_bibleget']);
            }
            else{
              //print("\n Error writing to file ".$file."... \n");
            }
          }
          else{
            //print("\n Could not access file ".$file."... \n");
          }
       }
    }

}

if( is_admin() ){
    $my_settings_page = new MySettingsPage();
}
