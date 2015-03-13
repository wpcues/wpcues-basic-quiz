<?php
/*
Plugin Name: WpCues Basic Quiz
Description: This is a plugin to generate Quizzes having multimedia and math in questions and answers both.
Text Domain: wpcues-basic-quiz
Domain Path: /languages/
Author: wpcues
Version: 1.0
*/
if(!class_exists('wpcues_basic_quiz')){
class wpcues_basic_quiz{
		/**
         * Construct the plugin object
         */
		private $wpprocuesetting; 
		private static $_wpcuesbasicquiz;
		public function __construct()
		{		
			self::$_wpcuesbasicquiz = $this;
			$this->wpprocuesetting=get_option('wpcuequiz_setting');
			add_action( 'admin_init',array(&$this,'settings_register' ));
			add_action('init',array(&$this,'wpcuequiz_rewrite_rules'));
			//admin-menu pages
			add_action('admin_menu', array(&$this,'wpcue_proquiz_add_page'));
			add_action('wp_kses_allowed_html',array(&$this,'wpcue_allowed_html'),10,1);
			load_plugin_textdomain('wpcues-basic-quiz', false, basename( dirname( __FILE__ ) ) . '/languages/' );
			//Register and initialize custom post types
			require_once(sprintf("%s/post-types/wpcue_quiz_action.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_quiz.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_question.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_section.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_gradegroup.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_leaderboard.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_chart.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_certificate.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_badge.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_level.php", dirname(__FILE__)));
			require_once(sprintf("%s/post-types/wpcue_basic_product.php", dirname(__FILE__)));
			$WpCueBasicQuiz=new WpCueBasicQuiz();
			$WpCueBasicQuestion = new WpCueBasicQuestion();
			$WpCueQuizAction=new WpCueQuizAction();
			$WpCueBasicCertificate = new WpCueBasicCertificate();
			$WpCueBasicLeaderboard= new WpCueBasicLeaderboard();
			$WpCueBasicChart= new WpCueBasicChart();
			$WpCueBasicSection=new WpCueBasicSection();
			$WpCueBasicGradeGroup = new WpCueBasicGradeGroup();
			$WpCueBasicBadge= new WpCueBasicBadge();
			$WpCueBasicLevel= new WpCueBasicLevel();
			$WpCueBasicProduct= new WpCueBasicProduct();
			//Show author specific posts and comments
			add_filter('pre_get_posts', array(&$this, 'filter_postauthor'));
			add_filter('query_vars', array(&$this,'wpcuequiz_plugin_query_vars'));
			add_action('template_redirect', array(&$this,'wpcue_templateRedirect'));
			add_action('wp_ajax_dynamic_css',array(&$this,'dynaminc_css'));
			add_action('admin_init',array(&$this,'wpcue_versioncheck'));
        } // END public function __construct
		public static function this() {
			return self::$_wpcuesbasicquiz;
		}


        /**
         * Activate the plugin
         */
        public static function activate($network_wide)
		{	
			self::check_wpversion();
			global $wpdb;
			 if ( is_multisite() && $network_wide ) {
				// store the current blog id
			$current_blog = $wpdb->blogid;

			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::wpcue_versioncheck();
					self::wpcuebasicquiz_quizstatdb();
					self::add_default_settings();
					self::wpcuequiz_rewrite_rules();
					flush_rewrite_rules();
					restore_current_blog();
					}
			}else {
					self::wpcue_versioncheck();
					self::wpcuebasicquiz_quizstatdb();
					self::add_default_settings();
					self::wpcuequiz_rewrite_rules();
					flush_rewrite_rules();
			}
        } // END public static function activate

        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            flush_rewrite_rules();
        } // END public static function deactivate
		/**
		* Remove options and tables when uninstalled
		*/
		public static function uninstall_wpprocue($network_wide){
			if ( ! current_user_can( 'activate_plugins' ) )
				return;
			check_admin_referer( 'bulk-plugins' );
			global $wpdb;
			if ( is_multisite() && $network_wide ) {
				// store the current blog id
			$current_blog = $wpdb->blogid;

			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					$table_names = array($wpdb->prefix.'wpcuequiz_quizstat',$wpdb->prefix.'wpcuequiz_quizstatinfo');	
					$table_name=implode(",",$table_names);
					$wpdb->query( "DROP TABLE IF EXISTS  $table_name");
					flush_rewrite_rules();
					restore_current_blog();
					
				}
			} else {
					$table_names = array($wpdb->prefix.'wpcuequiz_quizstat',$wpdb->prefix.'wpcuequiz_quizstatinfo');	
					$table_name=implode(",",$table_names);
					$wpdb->query( "DROP TABLE IF EXISTS  $table_name");
					flush_rewrite_rules();
			}
		
		}
		public static function check_wpversion(){
			global $wp_version;
			if(version_compare( $wp_version,'3.3', '<' ) ){
				self::deactivate();
				wp_die('<p>The <strong>WpCue Basic Quiz</strong> plugin requires wordpress  version 5.3 or greater.</p>','Plugin Activation Error',  array( 'response'=>200, 'back_link'=>TRUE ) );
			}
		}
		/**
		* Add mathslate plugin to tinymce editors
		*/
		public function  wpcue_custom_plugins($plugins_array){
			$plugins = array('mathslate'); //Add any more plugins you want to load here
			//Build the response - the key is the plugin name, value is the URL to the plugin JS
			foreach ($plugins as $plugin ) {
				$plugins_array[ $plugin ] = plugins_url('tinymce/', __FILE__) . $plugin . '/plugin.js';
			}
			return $plugins_array;
		}
		public function wpcue_register_mathslate_button($buttons){
			array_push($buttons, "mathslate");
			return $buttons;
		}
		/**
		* Create Menu page
		*/
		public function wpcue_proquiz_add_page() {
			// Main Menu Page
			global $wp_version;
			add_menu_page( 'Quiz', 'Quiz', 'edit_posts','edit.php?post_type=wpcuebasicquiz','','dashicons-admin-page','5.9025');
			//Create Submenu
			add_submenu_page('edit.php?post_type=wpcuebasicquiz','All Quizzes', 'All Quizzes', 'edit_posts','edit.php?post_type=wpcuebasicquiz');
			$createquiz_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Add New quiz', 'Add New quiz', 'edit_posts','wpcuequizaddnew',array(&$this,'wpcue_proquiz_createquiz_page'));
			remove_submenu_page('edit.php','edit-tags.php?taxonomy=wpcuebasicquizcat&post_type=wpcuebasicquiz');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Quiz Categories', 'Quiz Category', 'manage_categories','edit-tags.php?taxonomy=wpcuebasicquizcat&post_type=wpcuebasicquiz');
			$questcat_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Question Categories', 'Question Category', 'manage_categories','edit-tags.php?taxonomy=wpcuebasicquestcat&post_type=wpcuebasicquestion');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Certificates', 'Certificates', 'edit_posts','edit.php?post_type=wpcuecertificate');
			$createlevel_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Levels', 'Levels', 'edit_posts','edit.php?post_type=wpcuebasiclevel');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Badges', 'Badges', 'edit_posts','edit.php?post_type=wpcuebasicbadge');
			add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Monetization', 'Products', 'edit_posts','edit.php?post_type=wpcuebasicproduct');
			$report_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Report Generation', 'Report Generation', 'edit_posts','wpcuequizreport',array(&$this,'wpcue_proquiz_report_page'));
			$quizstat_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Statistics', 'Statistics', 'edit_posts','wpcuequizstatistics',array(&$this,'wpcue_proquiz_quizstat_page'));
			$quizsetting_hook_suffix=add_submenu_page('edit.php?post_type=wpcuebasicquiz', 'Settings', 'Settings','edit_posts','wpcuequizsetting',array(&$this,'wpcue_proquiz_setting_page'));
			add_submenu_page(null, 'Add New Certificate', 'Add New Certificate', 'edit_posts','wpcuequizcertificate',array(&$this,'wpcue_proquiz_createcertificate_page'));
			add_submenu_page(null, 'Add New Badge', 'Add New Badge', 'edit_posts','wpcuequizbadge',array(&$this,'wpcue_proquiz_createbadge_page'));
			add_submenu_page(null, 'Add New Level', 'Add New Level', 'edit_posts','wpcuequizlevel',array(&$this,'wpcue_proquiz_createlevel_page'));
			$quizmonetize_hook_suffix=add_submenu_page(null, 'Add New Product', 'Add New Product', 'edit_posts','wpcuequizproduct',array(&$this,'wpcue_proquiz_createproduct_page'));
			//add admin scripts to menu and submenu pages
			add_action('load-' . $createquiz_hook_suffix, array(&$this,'wpcue_createquizpage_add'));
			add_action('load-'.$quizstat_hook_suffix,array(&$this,'load_quizstat_script'));
			add_action('load-edit-tags.php',array(&$this,'load_questcat_script'));
			add_action('load-'.$report_hook_suffix,array(&$this,'load_report_script'));
			add_action('load-'.$quizsetting_hook_suffix,array(&$this,'load_quizsetting_script'));
			add_action('load-'.$quizmonetize_hook_suffix,array(&$this,'load_monetize_script'));
		}
		public function load_questcat_script(){
			$screen = get_current_screen();
			if (!isset($screen->taxonomy)){return;}
			$taxonomy=$screen->taxonomy;
			switch($taxonomy){
				case 'wpcuebasicquestcat':
					add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_questcat_scripts'));
					break;
				case 'wpcuebasicquizcat':
					add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_quizcat_scripts'));
					break;
			}
		}
		public function load_report_script(){
			add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_report_scripts'));
		}
		public function wpcue_proquiz_questcat_scripts(){
			wp_register_script( 'wpcuebasicquiz-questcat', plugins_url( '/js/wpcuebasicquiz-questcat.js', __FILE__ ),array('jquery') );
			wp_enqueue_script('wpcuebasicquiz-questcat');
		}
		public function wpcue_proquiz_quizcat_scripts(){
			wp_register_script( 'wpcuebasicquiz-quizcat', plugins_url( '/js/wpcuebasicquiz-quizcat.js', __FILE__ ),array('jquery') );
			wp_enqueue_script('wpcuebasicquiz-quizcat');
		}
		public function load_quizsetting_script(){
			wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('css/wpcuebasicquiz-createquiz.css',__FILE__));
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-tabs');
		}
		public function load_monetize_script(){
			wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('css/wpcuebasicquiz-createquiz.css',__FILE__));
			wp_register_script('wpcuebasicquiz-product',plugins_url('js/wpcuebasicquiz-product.js',__FILE__),array('jquery-ui-datepicker','jquery','jquery-ui-core'));
			wp_enqueue_script('wpcuebasicquiz-product');
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_enqueue_script('jquery-ui-tabs');
		}
		public function dynaminc_css(){
			require(sprintf("%s/css/trial.php",realpath(dirname(__FILE__))));
			exit;
		}
		/**
		* Enqueue scripts for report page
		*/
		public function wpcue_proquiz_report_scripts(){
			wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('css/wpcuebasicquiz-createquiz.css',__FILE__));
			wp_enqueue_style('wpcuebasicquiz-createquiz');
			wp_register_script('wpcuebasicquiz-report', plugins_url('/js/wpcuebasicquiz-report.js', __FILE__ ),array('jquery-ui-dialog','jquery-form','jquery','jquery-ui-tabs'));
			wp_enqueue_script('wpcuebasicquiz-report');
		}
		
		
		/**
		* Create submenu Pages
		*/
		public function wpcue_proquiz_createquiz_page(){require_once(sprintf("%s/templates/createquiz.php", dirname(__FILE__)));}
		public function wpcue_proquiz_quizstat_page(){require_once(sprintf("%s/templates/quizstat.php", realpath(dirname(__FILE__))));}
		public function wpcue_proquiz_report_page(){require_once(sprintf("%s/templates/report.php", realpath(dirname(__FILE__))));}
		public function wpcue_proquiz_setting_page(){require_once(sprintf("%s/templates/wpprocue_setting.php", dirname(__FILE__)));}
		public function wpcue_proquiz_createbadge_page(){require_once(sprintf("%s/templates/edit-badge-form.php", realpath(dirname(__FILE__))));}
		public function wpcue_proquiz_createlevel_page(){require_once(sprintf("%s/templates/edit-level-form.php", realpath(dirname(__FILE__))));}
		public function wpcue_proquiz_createcertificate_page(){require_once(sprintf("%s/templates/edit-certificate-form.php", realpath(dirname(__FILE__))));}
		public function wpcue_proquiz_createproduct_page(){require_once(sprintf("%s/templates/edit-product-form.php", realpath(dirname(__FILE__))));}
		/**
		* Add action to enqueue scripts and style
		*/
		public function wpcue_createquizpage_add($pagehook){
			$screen=get_current_screen();
			$screen->show_screen_options();
			add_filter('tiny_mce_before_init', array(&$this,'wpcue_change_mce_options'));
			add_filter('mce_external_plugins', array(&$this,'wpcue_custom_plugins'));
			add_filter('mce_buttons', array(&$this,'wpcue_register_mathslate_button'));
			add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_admin_scripts'));
		}
		/**
		* Enqueue scripts for admin submenu pages
		*/
		public function wpcue_proquiz_admin_scripts(){
			global $wp_version;
			wp_register_script( 'wpcuebasicquiz-upload', plugins_url( '/js/wpcuebasicquiz-main.js', __FILE__ ),array('jquery','jquery-ui-dialog','jquery-ui-tabs','postbox') );
			wp_register_script('wpcuebasicquiz-questioneditor',plugins_url('/js/wpcuebasicquiz-questioneditor.js',__FILE__),array('jquery','jquery-ui-core','jquery-ui-tabs'));
			wp_register_script('wpcuebasicquiz-quizeditor',plugins_url('/js/wpcuebasicquiz-quizeditor.js',__FILE__),array('jquery','jquery-ui-core','jquery-ui-tabs'));
			if( version_compare($wp_version, '3.5', '<')){
				wp_register_style( 'wpcuebasicquiz-createquizold', plugins_url('css/wpcuebasicquiz-createquiz-old.css',__FILE__));
				wp_enqueue_style('wpcuebasicquiz-createquizold');
				wp_register_style('jquery-smooth-old',plugins_url('css/jquery-ui-smooth-old.css',__FILE__));
				wp_enqueue_style('jquery-smooth-old');
			}else{
				wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('css/wpcuebasicquiz-createquiz.css',__FILE__));
				wp_enqueue_style('wpcuebasicquiz-createquiz');
			}
			wp_enqueue_script('wpcuebasicquiz-upload');
			wp_enqueue_script('wpcuebasicquiz-quizeditor');
			wp_enqueue_script('wpcuebasicquiz-questioneditor');
			wp_register_script('mathjax','//cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML');
			wp_enqueue_script('mathjax');
		}
		public function wpcue_loginform(){
			$content='<a href="'.wp_registration_url().'">Register</a> | ';
			$content.='<a href="'.wp_lostpassword_url().'">Lost Password?</a>';
			return $content;
		}
		public function load_quizstat_script(){
			add_action('admin_enqueue_scripts',array(&$this,'wpcue_proquiz_quizstat_scripts'));
		}
		public function wpcue_proquiz_quizstat_scripts(){
			wp_register_style( 'wpcuebasicquiz-createquiz', plugins_url('css/wpcuebasicquiz-createquiz.css',__FILE__));
			wp_enqueue_script('jquery-ui');
			wp_enqueue_script('jquery-ui-tabs');
			wp_enqueue_style('wpcuebasicquiz-createquiz');
		
		}
	
		public function filter_postauthor($query) {
			global $wp_version;
			if (isset($_GET['post_type']) && post_type_exists($_GET['post_type']) && in_array(strtolower($_GET['post_type']), array('wpcuebasicquiz'))) {
				if ( is_admin() && version_compare($wp_version, '3.1', '>') && is_post_type_archive( array('wpcuebasicquiz') ) ) {
					$current_user = wp_get_current_user();
					if(!current_user_can('edit_others_posts')){
					$query->set( 'author', $current_user->ID );}
				}
			}
            return $query;
		}  
		public function wpcue_basicquiz_remwpautop($content){
			in_array(get_post_type(),array('wpcuebasicquiz','wpcuebasicquestion'))  && remove_filter( 'the_content', 'wpautop' );
			return $content;
		}
		/**
		* Generic Function to get summary
		*/
		public static function summary($str, $limit=100, $strip = false) {
				$str = ($strip == true)?strip_tags($str):$str;
				if (strlen ($str) > $limit) {
					$str = substr ($str, 0, $limit - 3);
					return (substr ($str, 0, strrpos ($str, ' ')).'...');
			}
			return trim($str);
		}
		public static function wpcuebasicquiz_quizstatdb() {
			global $wpdb;
			$table_name1 = $wpdb->prefix.'wpcuequiz_quizstat';	
			$charset_collate = '';
			if ( ! empty( $wpdb->charset ) ) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}
			if ( ! empty( $wpdb->collate ) ) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}
			$sql1= "CREATE TABLE $table_name1 (
					instanceid bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					starttime datetime DEFAULT '0000-00-00 00:00:00' NULL,
					endtime datetime,
					quizid bigint(20) unsigned NOT NULL,
					userid bigint(20) unsigned NOT NULL,
					grade varchar(20),
					certificate bigint(20) unsigned,
					mode tinyint(2) unsigned,
					status tinyint(1) unsigned,	
					timeremaining int(10) unsigned,
					processed tinyint(1) DEFAULT 0 NOT NULL,
					UNIQUE KEY id (instanceid)
				) $charset_collate;";
			$table_name2=$wpdb->prefix.'wpcuequiz_quizinfo';
			$sql2="CREATE TABLE $table_name2 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					quizid bigint(20) unsigned NOT NULL,
					entityid bigint(20) unsigned NOT NULL,
					parentid bigint(20),
					entityorder DECIMAL(15,8) unsigned,
					category int(10),
					point int(10),
					questionchange tinyint(1) DEFAULT 0 NOT NULL,
					questionchangedate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name3=$wpdb->prefix.'wpcuequiz_quizstatinfo';
			$sql3="CREATE TABLE $table_name3 (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				instanceid bigint(20) unsigned NOT NULL,
				entityid bigint(20) unsigned NOT NULL,
				answer text(20),
				reply text(20),
				point int(4),
				status tinyint(3) unsigned,
				disabled tinyint(1) DEFAULT 0 NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name4=$wpdb->prefix.'wpcuequiz_quizerrorinfo';
			$sql4="CREATE TABLE $table_name4 (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				instanceid bigint(20) unsigned NOT NULL,
				quizid bigint(20) unsigned NOT NULL,
				entityid bigint(20) unsigned NOT NULL,
				errorid bigint(20) unsigned NOT NULL,
				status tinyint(2) unsigned NOT NULL,
				UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name5=$wpdb->prefix.'wpcuequiz_productinfo';
			$sql5="CREATE TABLE $table_name5 (
					productid bigint(20) unsigned NOT NULL,
					itemid bigint(2) unsigned NOT NULL,
					itemtype tinyint(2) unsigned NOT NULL
				) $charset_collate;";
			$table_name6=$wpdb->prefix.'wpcuequiz_productsale';
			$sql6="CREATE TABLE $table_name6 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					productid bigint(20) unsigned NOT NULL,
					userid bigint(2) unsigned NOT NULL,
					purchasedate datetime,
					UNIQUE KEY id (id)
				) $charset_collate;";
			$table_name7=$wpdb->prefix.'wpcuequiz_badgestat';
			$sql7="CREATE TABLE $table_name7 (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					userid bigint(20) unsigned NOT NULL,
					badgeid bigint(2) unsigned NOT NULL,
					issueddate  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					status tinyint(1) DEFAULT 0 NOT NULL,
					UNIQUE KEY id (id)
				) $charset_collate;";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql1 );dbDelta( $sql2 );dbDelta( $sql3 );
			dbDelta($sql4);dbDelta($sql5);dbDelta($sql6);dbDelta($sql7);
		}
		public static function add_default_settings(){
			$settings=array();
			$settings['basic']['login']=1;
			$settings['basic']['adminemail']='';
			$settings['activetab']=1;
			$settings['basic']['social']=1;
			$settings['text']['logintext']='You need to be registered and logged in to take this quiz.';
			$settings['text']['login']='login';
			$settings['text']['submit']='Submit';
			$settings['text']['start']='Start';
			$settings['text']['continue']='Continue';
			$settings['text']['next']='Next';
			$settings['text']['prev']='Previous';
			$settings['text']['quizduration']='Duration : ';
			$settings['text']['timeleft']='Time Left : ';
			$settings['text']['processingquiz']='Processing Quiz.Wait for the result... ';
			$settings['basic']['badgelevelcron']=1;
			$settings['level']['adminemailsubj']='User reached new level';
			$settings['level']['adminemailbody']='%%USERNAME%% earned %%NEWLEVEL%% level.';
			$settings['level']['useremailsubj']='Congrats! You reached new level';
			$settings['level']['useremailbody']='Dear %%USERNAME%%,Congratulations with your new level: %%NEWLEVEL%% .Greetings,Admin Team';
			$settings['badge']['adminemailsubj']='User earned new badge';
			$settings['badge']['adminemailbody']='%%USERNAME%% earned new badge %%BADGENAME%% ';
			$settings['badge']['useremailsubj']='Congrats! You earned new badge';
			$settings['badge']['useremailbody']='Dear %%USERNAME%%,Congratulations with your new Badge: %%BADGEIMAGE%% .';
			$settings['badge']['mozurltext']='Click here to claim your badge.';
			update_option('wpcuequiz_setting',$settings);
		}
		public function settings_register(){
			$origoption=(array)$this->wpprocuesetting;
			add_settings_section( 'wpcuebasicquiz_basic_setting', null,null,'wpcuebasicquiz_basic_settings' );
			add_settings_field( 'Login', 'Login', array(&$this,'wpcuebasicquizsetting_login'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>__('Show login form in dialog box when login button is clicked. (for quizzes requiring login)','wpcues-basic-quiz')));	
			add_settings_field( 'adminemail', 'Aministrator Email', array(&$this,'wpcuebasicquizsetting_adminemail'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>'Please Enter the administrator emailid required to receive various emails'));	
			add_settings_field( 'schedule_badgelevel_cron', 'Schedule Badge/Level Cron', array(&$this,'wpcuebasicquizsetting_badgelevelcron'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',
				array('origoption'=>$origoption,'label'=>''));	
			add_settings_section('wpcuebasicquiz_email_option',null,null,'wpcuebasicquiz_email_options');
			add_settings_field( 'emailleveladmin', 'Email', array(&$this,'wpcuebasicquizsetting_leveladminemail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>'Notify admin when user attains new level'));	
			add_settings_field( 'emailleveluser',null, array(&$this,'wpcuebasicquizsetting_leveluseremail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>'Notify user when user attains new level'));
			add_settings_field( 'emailbadgeadmin',null, array(&$this,'wpcuebasicquizsetting_badgeadminemail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>'Notify admin when new Badge issued to user'));
			add_settings_field( 'emailbadgeuser',null, array(&$this,'wpcuebasicquizsetting_badgeuseremail'), 'wpcuebasicquiz_email_options', 'wpcuebasicquiz_email_option',
				array('origoption'=>$origoption,'label'=>'Notify user when new Badge issued to him'));	
			add_settings_field('wpprocueactivetab',null, array(&$this,'wpcuebasicquizsetting_activetab'), 'wpcuebasicquiz_basic_settings', 'wpcuebasicquiz_basic_setting',array('origoption'=>$origoption));
			add_settings_section( 'wpcuebasicquiz_recaptcha_setting', null,null,'wpcuebasicquiz_recaptcha_settings' );
			add_settings_field('wpuebasicquiz_recpacha_private_key','Site key',array(&$this,'wpuebasicquiz_recpacha_private_key'),'wpcuebasicquiz_recaptcha_settings','wpcuebasicquiz_recaptcha_setting',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_recpacha_public_key','Secret key',array(&$this,'wpcuebasicquiz_recapcha_publickey'),'wpcuebasicquiz_recaptcha_settings','wpcuebasicquiz_recaptcha_setting',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_text_setting', null,null,'wpcuebasicquiz_text_settings' );
			add_settings_field('wpcuebasicquiz_login_text','Login Text',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'logintext','label'=>'You need to be registered and logged in to take this quiz.'));
			add_settings_field('wpcuebasicquiz_login_button','Login Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'login','label'=>'login'));
			add_settings_field('wpcuebasicquiz_submit_button','Submit Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'submit','label'=>'Submit'));
			add_settings_field('wpcuebasicquiz_start_button','Start Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'start','label'=>'Start'));
			add_settings_field('wpcuebasicquiz_continue_button','Continue Quiz Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'continue','label'=>'Continue'));
			add_settings_field('wpcuebasicquiz_next_button','Next Question Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'next','label'=>'Next'));
			add_settings_field('wpcuebasicquiz_prev_button','Previous Question Button',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'prev','label'=>'Previous'));
			add_settings_field('wpcuebasicquiz_duration','Quiz Duration',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'quizduration','label'=>'Duration : '));
			add_settings_field('wpcuebasicquiz_timeleft','Time Left (timer text)',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'timeleft','label'=>'Time Left : '));
			add_settings_field('wpcuebasicquiz_processquiz','Processing Quiz Message',array(&$this,'wpcuebasicquiz_textsetting'),'wpcuebasicquiz_text_settings','wpcuebasicquiz_text_setting',
				array('origoption'=>$origoption,'settingvariable'=>'processingquiz','label'=>'Processing Quiz.Wait for the result... '));
			add_settings_section( 'wpcuebasicquiz_level_adminemail', 'Admin',null,'wpcuebasicquiz_level_adminemails' );
			add_settings_field('level_adminemailsubj','Subject',array(&$this,'wpuebasicquiz_level_adminemailsubj'),'wpcuebasicquiz_level_adminemails','wpcuebasicquiz_level_adminemail',
				array('origoption'=>$origoption,'label'=>'User reached new level'));
			add_settings_field('level_adminemailbody','Body',array(&$this,'wpuebasicquiz_level_adminemailbody'),'wpcuebasicquiz_level_adminemails','wpcuebasicquiz_level_adminemail',
				array('origoption'=>$origoption,'label'=>'%%USERNAME%% earned %%NEWLEVEL%% level.'));
			add_settings_section( 'wpcuebasicquiz_level_useremail', 'User',null,'wpcuebasicquiz_level_useremails' );
			add_settings_field('level_useremailsubj','Subject',array(&$this,'wpuebasicquiz_level_useremailsubj'),'wpcuebasicquiz_level_useremails','wpcuebasicquiz_level_useremail',
				array('origoption'=>$origoption,'label'=>'Congrats! You reached new level'));
			add_settings_field('level_useremailbody','Body',array(&$this,'wpuebasicquiz_level_useremailbody'),'wpcuebasicquiz_level_useremails','wpcuebasicquiz_level_useremail',
				array('origoption'=>$origoption,'label'=>'Dear %%USERNAME%%,Congratulations with your new level: %%NEWLEVEL%% .Greetings,Admin Team'));
			add_settings_section( 'wpcuebasicquiz_badge_adminemail', 'Admin',null,'wpcuebasicquiz_badge_adminemails' );
			add_settings_field('badge_adminemailsubj','Subject',array(&$this,'wpuebasicquiz_badge_adminemailsubj'),'wpcuebasicquiz_badge_adminemails','wpcuebasicquiz_badge_adminemail',
				array('origoption'=>$origoption,'label'=>'User earned new badge'));
			add_settings_field('badge_adminemailbody','Body',array(&$this,'wpuebasicquiz_badge_adminemailbody'),'wpcuebasicquiz_badge_adminemails','wpcuebasicquiz_badge_adminemail',
				array('origoption'=>$origoption,'label'=>'%%USERNAME%% earned new badge %%BADGENAME%% '));
			add_settings_section( 'wpcuebasicquiz_badge_useremail', 'User',null,'wpcuebasicquiz_badge_useremails' );
			add_settings_field('badge_useremailsubj','Subject',array(&$this,'wpuebasicquiz_badge_useremailsubj'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>'Congrats! You earned new badge'));
			add_settings_field('badge_useremailbody','Body',array(&$this,'wpuebasicquiz_badge_useremailbody'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>'Dear %%USERNAME%%,Congratulations with your new Badge: %%BADGEIMAGE%% .'));
			add_settings_field('badge_mozurltext','%%BADGEOPENMOZURL%% text',array(&$this,'wpcuebasicquiz_badge_mozurltext'),'wpcuebasicquiz_badge_useremails','wpcuebasicquiz_badge_useremail',
				array('origoption'=>$origoption,'label'=>'Click here to claim your badge.'));
			add_settings_section( 'wpcuebasicquiz_payment_method',null,null,'wpcuebasicquiz_payment_methods' );
			add_settings_field('wpcuebasicquiz_payment_option','Payment Method',array(&$this,'wpcuebasicquiz_paymentoptions'),'wpcuebasicquiz_payment_methods','wpcuebasicquiz_payment_method',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_stripe_detail','Stripe',null,'wpcuebasicquiz_stripe_details' );
			add_settings_field('wpcuebasicquiz_stripe_apiprivate','Private Key',array(&$this,'wpcuebasicquiz_stripeapiprivate'),'wpcuebasicquiz_stripe_details','wpcuebasicquiz_stripe_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_stripe_apipublic','Public Key',array(&$this,'wpcuebasicquiz_stripeapipublic'),'wpcuebasicquiz_stripe_details','wpcuebasicquiz_stripe_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_paypal_detail','Paypal',null,'wpcuebasicquiz_paypal_details' );
			add_settings_field('wpcuebasicquiz_paypal_apiusername','Api User name',array(&$this,'wpcuebasicquiz_paypalapiuser'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_paypal_apipassword','Api User name',array(&$this,'wpcuebasicquiz_paypalapipassword'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_paypal_apisignature','Api User name',array(&$this,'wpcuebasicquiz_paypalapisignature'),'wpcuebasicquiz_paypal_details','wpcuebasicquiz_paypal_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuebasicquiz_issuer_detail','Issuer Details',null,'wpcuebasicquiz_issuer_details' );
			add_settings_field('wpcuebasicquiz_issuername','Issuer name',array(&$this,'wpcuebasicquiz_issuername'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issueremail','Issuer Email',array(&$this,'wpcuebasicquiz_issueremail'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerurl','Issuer Url',array(&$this,'wpcuebasicquiz_issuerurl'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerdescription','Issuer Description',array(&$this,'wpcuebasicquiz_issuerdescription'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_issuerlogo','Issuer Logo',array(&$this,'wpcuebasicquiz_issuerlogo'),'wpcuebasicquiz_issuer_details','wpcuebasicquiz_issuer_detail',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_section( 'wpcuwbasicquiz_submit_dialog','Submit Dialog',null,'wpcuwbasicquiz_submit_dialogs' );
			add_settings_field('wpcuebasicquiz_submitdialogstat','Staus',array(&$this,'wpcuebasicquiz_submitdialogstat'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>''));
			add_settings_field('wpcuebasicquiz_submitdialog','Dialog',array(&$this,'wpcuebasicquiz_submitdialog'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>'Thanks for taking this quiz.'));
			add_settings_field('wpcuebasicquiz_submitdialheight','Height(px)',array(&$this,'wpcuebasicquiz_submitdialheight'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_field('wpcuebasicquiz_submitdialwidth','Width(px)',array(&$this,'wpcuebasicquiz_submitdialwidth'),'wpcuwbasicquiz_submit_dialogs','wpcuwbasicquiz_submit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_section( 'wpcuwbasicquiz_autosubmit_dialog','Autosubmit Dialog',null,'wpcuwbasicquiz_autosubmit_dialogs' );
			add_settings_field('wpcuebasicquiz_autosubmitdialogstat','Status',array(&$this,'wpcuebasicquiz_autosubmitdialogstat'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>""));
			add_settings_field('wpcuebasicquiz_autosubmitdialog','Dialog',array(&$this,'wpcuebasicquiz_autosubmitdialog'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>"Time's Up !"));
			add_settings_field('wpcuebasicquiz_autodialheight','Height(px)',array(&$this,'wpcuebasicquiz_autodialheight'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			add_settings_field('wpcuebasicquiz_autodialwidth','Width(px)',array(&$this,'wpcuebasicquiz_autodialwidth'),'wpcuwbasicquiz_autosubmit_dialogs','wpcuwbasicquiz_autosubmit_dialog',
				array('origoption'=>$origoption,'label'=>'400'));
			register_setting('wpcuebaiscquiz_basic_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_social_share_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_recaptcha_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_text_settings','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_level_adminemails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_level_useremails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_badge_adminemails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_badge_useremails','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_payment_methods','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_stripe_details','wpcuequiz_setting');
			register_setting('wpcuebasicquiz_issuer_details','wpcuequiz_setting');
			register_setting('wpcuwbasicquiz_submit_dialogs','wpcuequiz_setting');
			register_setting('wpcuwbasicquiz_autosubmit_dialogs','wpcuequiz_setting');
		}
		public function wpcuebasicquizsetting_login($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['login'])){$wpprocuelogin=$origoption['basic']['login'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][login]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_adminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['adminemail'])){
				$adminemail=$origoption['basic']['adminemail'];
			}else{
				 $adminemail=get_option('admin_email');
			}
			echo '<input type="text" name="wpcuequiz_setting[basic][adminemail]" value="'.$adminemail.'">';
			$label = esc_attr( $args['label'] );
		}
		public function wpcuebasicquizsetting_activetab($args){
			$origoption=$args['origoption'];
			if(empty($origoption['activetab'])){$activetab=1;}else{$activetab=$origoption['activetab'];}
			echo '<input type="hidden" name="wpcuequiz_setting[activetab]" value="'.$activetab.'">';
		}
		public function wpcuebasicquizsetting_social($args){
			$origoption=$args['origoption'];
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][social]' value='1' ";
			if(!(empty($origoption['basic']['social']))){echo 'checked';}
			echo " />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
			
		}
		public function wpuebasicquiz_recpacha_private_key($args){
			$origoption=$args['origoption'];
			if(empty($origoption['recaptcha']['privatekey'])){$value=$args['label'];}else{$value=$origoption['recaptcha']['privatekey'];}
			echo '<input type="text" name="wpcuequiz_setting[recaptcha][privatekey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_recapcha_publickey($args){
			$origoption=$args['origoption'];
			if(empty($origoption['recaptcha']['publickey'])){$value=$args['label'];}else{$value=$origoption['recaptcha']['privatekey'];}
			echo '<input type="text" name="wpcuequiz_setting[recaptcha][publickey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_textsetting($args){
			$origoption=$args['origoption'];
			$settingvariable=$args['settingvariable'];
			if(isset($origoption['text'][$settingvariable])){$value=$origoption['text'][$settingvariable];}else{$value=esc_attr($args['label']);}
			if($settingvariable=='logintext'){
				echo '<textarea name="wpcuequiz_setting[text]['.$settingvariable.']" style="width:50%;">'.$value.'</textarea>';
			}else{
				echo "<input type='text' name='wpcuequiz_setting[text][".$settingvariable."]' value='".$value."'>";
			}
			
		}
		
		public function wpcuebasicquizsetting_badgelevelcron($args){
			$origoption=$args['origoption'];
			if(!(empty($origoption['basic']['badgelevelcron']))){$value=$origoption['basic']['badgelevelcron'];}else{$value=1;}
			echo "<select name='wpcuequiz_setting[basic][badgelevelcron]'>";
			echo '<option value="1"';
			if($value==1){echo 'selected';}
			echo '>Hourly</option>';
			echo '<option value="2"';
			if($value==2){echo 'selected';}
			echo '>Daily</option>';
			echo '<option value="3"';
			if($value==3){echo 'selected';}
			echo '>twicedaily</option>';
			echo '</select>';
			
		}
		public function wpcuebasicquizsetting_leveladminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['leveladmin'])){$wpprocuelogin=$origoption['basic']['leveladmin'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][leveladmin]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_leveluseremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['leveluser'])){$wpprocuelogin=$origoption['basic']['leveluser'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][leveluser]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_badgeadminemail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['badgeadmin'])){$wpprocuelogin=$origoption['basic']['badgeadmin'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][badgeadmin]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
		}
		public function wpcuebasicquizsetting_badgeuseremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['basic']['badgeuser'])){$wpprocuelogin=$origoption['basic']['badgeuser'];}else{$wpprocuelogin=0;}
			$label = esc_attr( $args['label'] );
			echo "<input type='checkbox' name='wpcuequiz_setting[basic][badgeuser]' value='1' ".checked(1,$wpprocuelogin, false) ." />";
			 echo  '<label for="show_header"> '  . $label . '</label>';
			 
		}
		public function wpuebasicquiz_level_adminemailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['adminemailsubj'])){$value=$origoption['level']['adminemailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[level][adminemailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_level_adminemailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['adminemailbody'])){$value=$origoption['level']['adminemailbody'];}else{$value=esc_attr($args['label']);}
			echo wp_editor($value,'wpcuebasicquiz_level_adminemailbody',array('textarea_name'=>"wpcuequiz_setting[level][adminemailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">You can use the following variables: %%USERNAME%% , %%EMAIL%% , %%NEWLEVEL%% , %%OLDLEVEL%%.</div>';
		}
		public function wpuebasicquiz_level_useremailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['useremailsubj'])){$value=$origoption['level']['useremailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[level][useremailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_level_useremailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['level']['useremailbody'])){$value=$origoption['level']['useremailbody'];}else{$value=esc_attr($args['label']);}
			wp_editor($value,'wpcuebasicquiz_level_useremail',array('textarea_name'=>"wpcuequiz_setting[level][useremailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">You can use the following variables: %%USERNAME%% , %%EMAIL%% , %%NEWLEVEL%% , %%OLDLEVEL%%.</div>';
		}
		public function wpuebasicquiz_badge_adminemailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['adminemailsubj'])){$value=$origoption['badge']['adminemailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][adminemailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_badge_adminemailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['adminemailbody'])){$value=$origoption['badge']['adminemailbody'];}else{$value=esc_attr($args['label']);}
			echo wp_editor($value,'wpcuebasicquiz_badge_adminemailbody',array('textarea_name'=>"wpcuequiz_setting[badge][adminemailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">You can use the following variables: %%USERNAME%% , %%EMAIL%% , %%BADGENAME%% , %%BADGEIMAGE%%.</div>';
		}
		public function wpuebasicquiz_badge_useremailsubj($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['useremailsubj'])){$value=$origoption['badge']['useremailsubj'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][useremailsubj]" value="'.$value.'">';
		}
		public function wpuebasicquiz_badge_useremailbody($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['useremailbody'])){$value=$origoption['badge']['useremailbody'];}else{$value=esc_attr($args['label']);}
			wp_editor($value,'wpcuebasicquiz_badge_useremail',array('textarea_name'=>"wpcuequiz_setting[badge][useremailbody]",'wpautop'=>false,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'quicktags'=>true,'dfw'=>true,'editor_height'=>100));
			echo '<div class="entitymsg settingmsg">You can use the following variables: %%USERNAME%% , %%EMAIL%% , %%BADGENAME%% , %%BADGEIMAGE%% , %%BADGEOPENMOZURL%%.</div>';
		}
		public function wpcuebasicquiz_badge_mozurltext($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badge']['mozurltext'])){$value=$origoption['badge']['mozurltext'];}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[badge][mozurltext]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_paymentoptions($args){
			$origoption=$args['origoption'];
			if(isset($origoption['payment']['method'])){$value=$origoption['payment']['method'];}else{$value=1;}
			echo '<select name="wpcuequiz_setting[payment][method]">';
			echo '<option value="1"';
			if($value == 1){echo ' selected';}
			echo '>Stripe</option>';
			echo '</select>';
		}
		public function wpcuebasicquiz_stripeapiprivate($args){
			$origoption=$args['origoption'];
			if(isset($origoption['stripe']['privatekey'])){$value=$origoption['stripe']['privatekey'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[stripe][privatekey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_stripeapipublic($args){
			$origoption=$args['origoption'];
			if(isset($origoption['stripe']['publickey'])){$value=$origoption['stripe']['publickey'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[stripe][publickey]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_paypalapisignature($args){
			$origoption=$args['origoption'];
			if(isset($origoption['paypal']['signature'])){$value=$origoption['paypal']['signature'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[paypal][signature]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuername($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['name'])){$value=$origoption['badgeissuer']['name'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][name]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerurl($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['url'])){$value=$origoption['badgeissuer']['url'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][url]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerdescription($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['description'])){$value=$origoption['badgeissuer']['description'];}else{$value='';}
			wp_editor( $value,'wpcuebasicquiz-issuerdesc',array('textarea_name'=>'wpcuequiz_setting[badgeissuer][description]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>400));
		}
		public function wpcuebasicquiz_issueremail($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['email'])){$value=$origoption['badgeissuer']['email'];}else{$value='';}
			echo '<input name="wpcuequiz_setting[badgeissuer][email]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_issuerlogo($args){
			$origoption=$args['origoption'];
			if(isset($origoption['badgeissuer']['logo'])){$value=$origoption['badgeissuer']['logo'];}else{$value='';}
			?>
			<div class='badgeimage'>
				<div id="addedimage" <?php if(empty($value)){echo 'class="hiddendiv"';}?> >
					<div id="imagecontainer">
						<img src="<?php echo $value; ?>" id='badgeimage'>
					</div>
					<div id="imageremovetool"></div>
				</div>
				<div id="badgeimagebutton">
					<input id="upload_image_button" type="button" value="Upload Image" />
					<input type="hidden" name="wpcuequiz_setting[badgeissuer][logo]" id="wpcuebasicquiz-setting-issuerlogo"value="<?php echo $value; ?>">
				</div>
			</div>
			<?php
		}
		public function wpcuebasicquiz_submitdialogstat($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['status'])){
				$value=$origoption['submitdial']['status'];
			}else{$value=1;}
			echo '<div class="switch demo3"><input type="checkbox" name="wpcuequiz_setting[submitdial][status]" value="1"';
			if(!empty($value)){echo ' checked';}
			echo '><label><i></i></label></div>';
		}
		public function wpcuebasicquiz_submitdialog($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['dialog'])){
				$value=$origoption['submitdial']['dialog'];
			}else{$value=esc_attr($args['label']);}
			wp_editor( $value,'wpcuebasicquiz-submitdial',array('textarea_name'=>'wpcuequiz_setting[submitdial][dialog]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>100));
		}
		public function wpcuebasicquiz_submitdialheight($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['height'])){
				$value=$origoption['submitdial']['height'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[submitdial][height]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_submitdialwidth($args){
			$origoption=$args['origoption'];
			if(isset($origoption['submitdial']['width'])){
				$value=$origoption['submitdial']['width'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[submitdial][width]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_autosubmitdialogstat($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['status'])){
				$value=$origoption['autosubdial']['status'];
			}else{$value=1;}
			echo '<div class="switch demo3"><input type="checkbox" name="wpcuequiz_setting[autosubdial][status]" value="1"';
			if(!empty($value)){echo ' checked';}
			echo '><label><i></i></label></div>';
		}
		public function wpcuebasicquiz_autosubmitdialog($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['dialog'])){
				$value=$origoption['autosubdial']['dialog'];
			}else{$value=esc_attr($args['label']);}
			wp_editor( $value,'wpcuebasicquiz-autosubdial',array('textarea_name'=>'wpcuequiz_setting[autosubdial][dialog]','wpautop'=>true,'default_editor'=>'tinymce','drag_drop_upload'=>true,'textarea_rows'=>40,'editor_class'=> 'requiredvar','quicktags'=>true,'dfw'=>true,'editor_height'=>100));
		}
		public function wpcuebasicquiz_autodialheight($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['height'])){
				$value=$origoption['autosubdial']['height'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[autosubdial][height]" value="'.$value.'">';
		}
		public function wpcuebasicquiz_autodialwidth($args){
			$origoption=$args['origoption'];
			if(isset($origoption['autosubdial']['width'])){
				$value=$origoption['autosubdial']['width'];
			}else{$value=esc_attr($args['label']);}
			echo '<input type="text" name="wpcuequiz_setting[autosubdial][width]" value="'.$value.'">';
		}
		
		public function wpcue_change_mce_options($initArray) {
				$initArray['verify_html'] = false;
				$initArray['remove_redundant_brs'] = false;
				$initArray['remove_linebreaks'] = false;
				$initArray['force_p_newlines'] = false;
				$initArray['force_br_newlines'] = false;
			return $initArray;
		}
		public static function wpcue_versioncheck(){
			$wpcuebasicquiz_version=get_option('wpcuebasicquiz_version');
			if(empty($wpcuebasicquiz_version)){update_option('wpcuebasicquiz_version',1);}
		}
		public static function wpcuequiz_rewrite_rules(){
			add_rewrite_rule('wpcuecertificate/?([^/]*)', 'index.php?pagename=wpcuecertificate&wpcuecertificateid=$matches[1]', 'top');
			add_rewrite_rule('wpcuenewbadge/?([^/]*)','index.php?pagename=wpcuenewbadge&wpcuebadgeuid=$matches[1]', 'top');
			add_rewrite_rule('wpcuebadgejson/?([^/]*)','index.php?pagename=wpcuebadgejson&wpcuebadgeuid=$matches[1]', 'top');
			add_rewrite_rule('wpcuebadgeclassjson/?([^/]*)','index.php?pagename=wpcuebadgeclassjson&wpcuebadgeid=$matches[1]', 'top');
			add_rewrite_rule('wpcueissuerjson/?([^/]*)','index.php?pagename=wpcueissuerjson', 'top');
			add_rewrite_rule('wpcuedynamiccss/?([^/]*)','index.php?pagename=wpcuedynamiccss&wpcuequizid=$matches[1]', 'top');
		}
		public  function wpcuequiz_plugin_query_vars($vars) {
			$vars[] = 'wpcuecertificateid';
			$vars[]='wpcuebadgeuid';
			$vars[]='wpcuebadgeid';
			$vars[]='wpcuequizid';
			return $vars;
		}
		public function wpcue_templateRedirect(){
			$page = get_query_var('pagename');
			switch($page){
				case 'wpcuecertificate':
				$certificateid = get_query_var('wpcuecertificateid');
				if('' != $certificateid){
					global $wpdb;
					$table_name=$wpdb->prefix.'wpcuequiz_quizstat';
					$result=$wpdb->get_row($wpdb->prepare("select quizid,grade,userid,endtime from $table_name where instanceid=%d",$certificateid),ARRAY_A);
					if(empty($result)){
						header("HTTP/1.0 404 Not Found");
					}else{
						header("HTTP/1.1 200 OK");
						if(!(empty($result['grade']))){
							$gradegroupid=get_post_meta($result['quizid'],'quizgrade',true);
							$gradegroup=get_post($gradegroupid);$grademeta=unserialize($gradegroup->post_content);
							$certi=(int)$grademeta[$result['grade']]['certi'];
							$certificate=get_post($certi);
							$certificatemet =get_post_meta($certi,'wpcuecertificate_det');$certificatemeta=maybe_unserialize($certificatemet);
							$certificatemetavalues=$certificatemeta[0]; 
							if(empty($certificatemetavalues['approval'])){
								$certificatecontent=$certificate->post_content;
								if($certificatemetavalues['certype']==1){
									include(sprintf("%s/lib/mpdf/mpdf.php", dirname(__FILE__)));
									$mpdf=new mPDF('utf-8',array(100,50));
									$mpdf->WriteHTML($certificatecontent);
									$mpdf->Output();
									exit;
								}else{
									echo '<!DOCTYPE html><html><head></head><body>'.$certificatecontent.'</body></html>';
								}
							}else{
							
								_e('This certificate need admin approval to be issued. You will be notified when approved.','wpcues-quiz-pro');
							}
						}else{
							$post=get_post($result['quizid']);
							$current_user=wp_get_current_user();
							if (is_user_logged_in())  {
								echo $current_user->ID;
								_e('Please add suitable Grade group to your quiz first','wpcues-quiz-pro');
							}
						}
					}
					exit;
				}else{
					header("HTTP/1.0 404 Not Found");
					exit;
				}
				break;
				case 'wpcuenewbadge':
					$badgeguid=get_query_var('wpcuebadgeuid');
					if(!(empty($badgeguid))){
						global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
						$processed=$wpdb->get_var($wpdb->prepare("SELECT status from $table_name where id=%d",$badgeguid));
						if(!empty($processed)){
						header("HTTP/1.1 200 OK");
						$badgeurl=get_site_url().'/wpcuebadgejson/'.$badgeguid.'/';
						?>
						<!DOCTYPE html><html><head><script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js" ></script><script src="https://backpack.openbadges.org/issuer.js"></script><script type="text/javascript">var ajaxurl="<?php echo admin_url('admin-ajax.php');?>";</script></head><body>Click here
						<a href="javascript:OpenBadges.issue(['<?php echo urlencode($badgeurl);?>'],function(errors, successes){if(successes != ''){$.ajax({
							type: 'POST',
							dataType:'html',
							url:ajaxurl,
							data: {'action':'wpcuequizbadgesuccess_action','badgeguid':<?php echo $bageguid;?>
							},success: function(response){}});}
						});">Mozilla Badge Backpack</a></body></html>
						<?php	exit;
						}else{
							header("HTTP/1.0 404 Not Found");
							exit;
						}
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					break;
				case 'wpcuebadgejson':
					$badgeguid=get_query_var('wpcuebadgeuid');
					global $wp;
					$verifyurl=home_url(add_query_arg(array(),$wp->request)).'/';
					if(!empty($badgeguid)){
						global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_badgestat';
						$badgedet=$wpdb->get_row($wpdb->prepare("SELECT id,badgeid,userid,UNIX_TIMESTAMP(issueddate) as issueddate from $table_name where id=%d",$badgeguid),ARRAY_A);
						if(!empty($badgedet)){
							header('Content-Type: application/json');
							header("HTTP/1.1 200 OK");
							$badge=get_site_url().'/wpcuebadgeclassjson/'.$badgedet['badgeid'].'/';
							$userid=$badgedet['userid'];$user=get_user_by('id',$userid);$hashedemail=$user->user_email;
							$string='{"uid":"'.$badgeguid.'","badge":"'.$badge.'","verify":{"type":"hosted","url":"'.$verifyurl.'"},"recipient":{"type":"email","hashed":false,"identity":"'.$hashedemail.'"},"issuedOn":'.$badgedet['issueddate'].'}';
							echo $string;
							exit;
						}else{
							header("HTTP/1.0 404 Not Found");
							exit;
						}
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					break;
				case 'wpcuebadgeclassjson':
					$badgeid=get_query_var('wpcuebadgeid');
					$badge=get_post($badgeid);
					if($badge){
						header('Content-Type: application/json');
						header("HTTP/1.1 200 OK");
						$criteriaurl=get_permalink($badgeid);
						$baseurl=get_site_url();
						$issuerurl=$baseurl.'/wpcueissuerjson/';
						$imageurl=get_post_meta($badgeid,'wpcuebadgeimage',true);
						$string='{"name":"'.$badge->post_title.'","description":"'.$badge->post_content.'","image":"'.$imageurl.'","criteria":"'.$criteriaurl.'","issuer":"'.$issuerurl.'"}';
						echo $string;
						exit;
					}else{
						header("HTTP/1.0 404 Not Found");
						exit;
					}
					
					break;
				case 'wpcueissuerjson':
					$wpprocuesetting=get_option('wpcuequiz_setting');
					if(!empty($wpprocuesetting['badgeissuer']['name']) && !empty($wpprocuesetting['badgeissuer']['url'])){
						header('Content-Type: application/json');
						header("HTTP/1.1 200 OK");
						$string='{"name":"'.$wpprocuesetting['badgeissuer']['name'].'","url":"'.$wpprocuesetting['badgeissuer']['url'].'"';
						if(!empty($wpprocuesetting['badgeissuer']['description'])){$string.=',"description":"'.$wpprocuesetting['badgeissuer']['description'].'"';}
						if(!empty($wpprocuesetting['badgeissuer']['email'])){$string.=',"email":"'.$wpprocuesetting['badgeissuer']['email'].'"';}
						if(!empty($wpprocuesetting['badgeissuer']['logo'])){$string.=',"image":"'.$wpprocuesetting['badgeissuer']['logo'].'"';}
						$string.='}';
						echo $string;
					}else{
						header("HTTP/1.0 404 Not Found");
					}
					exit;
					break;
				case 'wpcuedynamiccss':
					$quizid=get_query_var('wpcuequizid');
					$customcss=get_post_meta($quizid,'customcss',true);
					header('Content-Type: text/css');
					header("HTTP/1.1 200 OK");
					echo $customcss;
					exit;
					break;
			}	
		}
		public function wpcue_allowed_html($tags){
			$tags['iframe']=array('src'=>1);
			//array_push($tags['div'],'data-send'=>1);
			$tags['div']['data-send']=1;$tags['div']['data-width']=1;$tags['div']['data-show-faces']=1;
			$tags['div']['data-href']=1;$tags['div']['data-action']=1;$tags['div']['data-layout']=1;$tags['div']['data-share']=1;
			//$tags['div']=array('data-send'=>1,'data-width'=>1,'data-show-faces'=>1);
			$tags['math']=array('display'=>1,'class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'display'=>1,'mode'=>1,'overflow'=>1);
			$tags['mi']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['mo']=array('accent'=>1,'class'=>1,'id'=>1,'style'=>1,'dir'=>1,'fence'=>1,'form'=>1,'href'=>1,'largeop'=>1,'lspace'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1,'maxsize'=>1,'minsize'=>1,'movablelimits'=>1,'rspace'=>1,'separator'=>1,'stretchy'=>1,'symmetric'=>1);
			$tags['mn']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['mtext']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1);
			$tags['ms']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'mathsize'=>1,'mathvariant'=>1,'lquote'=>1,'rquote'=>1);
			$tags['msub']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1);
			$tags['msup']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'superscriptshift'=>1);
			$tags['msubsup']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1,'superscriptshift'=>1);
			$tags['munder']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accentunder'=>1,'align'=>1);
			$tags['mover']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accent'=>1,'align'=>1);
			$tags['munderover']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'accent'=>1,'accentunder'=>1,'align'=>1);
			$tags['mmultiscripts']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'subscriptshift'=>1,'superscriptshift'=>1);
			$tags['mrow']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mfrac']=array('class'=>1,'id'=>1,'style'=>1,'dir'=>1,'href'=>1,'linethickness'=>1,'mathbackground'=>1,'mathcolor'=>1,'numalign'=>1);
			$tags['msqrt']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mroot']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1);
			$tags['mpadded']=array('class'=>1,'id'=>1,'style'=>1,'depth'=>1,'height'=>1,'href'=>1,'lspace'=>1,'mathbackground'=>1,'mathcolor'=>1,'voffset'=>1,'width'=>1);
			$tags['mphatnom']=array('class'=>1,'id'=>1,'style'=>1,'mathbackground'=>1);
			$tags['mfenced']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'close'=>1,'open'=>1,'separators'=>1);
			$tags['menclose']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'notation'=>1);
			$tags['mtable']=array('align'=>1,'alignmentscope'=>1,'class'=>1,'id'=>1,'style'=>1,'columnalign'=>1,'columnlines'=>1,'columnspacing'=>1,'columnwidth'=>1,'displaystyle'=>1,'equalcolumns'=>1,'equalrows'=>1,'frame'=>1,'framespacing'=>1,'groupalign'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'minlabelspacing'=>1,'rowalign'=>1,'rowlines'=>1,'rowspacing'=>1,'side'=>1,'width'=>1);
			$tags['mtd']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'columnalign'=>1,'columnspan'=>1,'groupalign'=>1,'rowalign'=>1,'rowspan'=>1);
			$tags['mtr']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'columnalign'=>1,'groupalign'=>1,'rowalign'=>1);
			$tags['maction']=array('actiontype'=>1,'class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'mathcolor'=>1,'selection'=>1);
			$tags['mstyle']=array('dir'=>1,'decimalpoint'=>1,'displaystyle'=>1,'infixlinebreakstyle'=>1,'scriptlevel'=>1,'scriptminsize'=>1,'scriptsizemultiplier'=>1);
			$tags['mglyph']=array('alt'=>1,'class'=>1,'id'=>1,'style'=>1,'href'=>1,'mathbackground'=>1,'height'=>1,'src'=>1,'valign'=>1,'width'=>1);
			$tags['mspace']=array('class'=>1,'id'=>1,'style'=>1,'depth'=>1,'mathbackground'=>1,'height'=>1,'linebreak'=>1,'width'=>1);
			$tags['mgroupalign']=array('class'=>1,'id'=>1,'style'=>1,'href'=>1,'groupalign'=>1);
			$tags['mstack']=array('mathcolor'=>1,'mathbackground'=>1,'align'=>1,'stackalign'=>1,'charalign'=>1,'charspacing'=>1);
			$tags['mlongdiv']=array('mathcolor'=>1,'mathbackground'=>1,'align'=>1,'stackalign'=>1,'charalign'=>1,'charspacing'=>1,'longdivstyle'=>1);
			return $tags;
		}
	
	
	}
	
}
if(class_exists('wpcues_basic_quiz'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('wpcues_basic_quiz', 'activate'));
    register_deactivation_hook(__FILE__, array('wpcues_basic_quiz', 'deactivate'));
	register_uninstall_hook(__FILE__, array( 'wpcues_basic_quiz', 'uninstall_wpprocue' ) );
    // instantiate the plugin class
    $wpcues_basic_quiz = new wpcues_basic_quiz();
}
// Add a link to the settings page onto the plugin page
if(isset($wpcues_basic_quiz))
{
    // Add the settings link to the plugins page
    function proquiz_settings_link($links)
    { 
        $settings_link = '<a href="edit.php?post_type=wpcuequiz&page=wpcuequizsetting">Settings</a>'; 
        array_unshift($links, $settings_link); 
        return $links; 
    }

    $plugin = plugin_basename(__FILE__); 
    add_filter("plugin_action_links_$plugin", 'proquiz_settings_link');
}
/* EOF */