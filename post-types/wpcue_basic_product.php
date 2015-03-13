<?php
/**
*WpCueBasicProduct class
*/
if(!class_exists('WpCueBasicProduct'))
{
    class WpCueBasicProduct
    {
        const POST_TYPE = "wpcuebasicproduct";
		private $wpprocuesetting;
		/**
		* The Constructor
		*/
		public function __construct(){
			// register actions
			add_action('init', array(&$this, 'init'));
			$this->wpprocuesetting=get_option('wpcuequiz_setting');
		} // END public function __construct()
		/**
		* hook into WP's init action hook
		*/
		public function init(){
			// Initialize Post Type
			$this->create_post_type();
			add_filter('post_row_actions',array($this,'my_product_list'),11,2);
			add_action('wp_ajax_addproduct_action',array(&$this,'add_newproduct'));
			add_action('wp_ajax_trashproduct_action',array(&$this,'trash_product'));
			add_action('wp_ajax_wpcuefetchitemlist_pageaction',array(&$this,'get_itemlist'));
			add_action('wp_ajax_wpcuesaveitemlist_pageaction',array(&$this,'save_itemlist'));
			add_action('wp_ajax_wpcueremove_item',array(&$this,'remove_item'));
			add_shortcode('wpcuebasicproduct',array(&$this,'product_shortcode'));
			add_filter('get_edit_post_link',array(&$this,'edit_product_link'),10, 3);
			add_action('admin_head',array(&$this,'reset_post_new_link'));
			add_filter('manage_wpcuebasicproduct_posts_columns',array(&$this,'new_product_columns'));
			add_action('manage_wpcuebasicproduct_posts_custom_column',array(&$this,'cusotm_product_columns'),10,2);
			add_filter('query_vars',array(&$this,'wpcuequizbasic_add_trigger'));
			add_action('template_redirect',array(&$this,'wpcuequizbasic_trigger_check'));
		} // END public function init()

		/**
		* Create the post type
		*/
		public function create_post_type(){
			$labels = array(
				'name'               => _x( 'Products', 'post type general name', 'wpcues-basic-quiz' ),
				'singular_name'      => _x( 'Badge', 'post type singular name', 'wpcues-basic-quiz' ),
				'menu_name'          => _x( 'Products', 'admin menu', 'wpcues-basic-quiz' ),
				'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'wpcues-basic-quiz' ),
				'add_new'            => _x( 'Add New', 'Product', 'wpcues-basic-quiz' ),
				'add_new_item'       => __( 'Add New Product', 'wpcues-basic-quiz' ),
				'new_item'           => __( 'New Product', 'wpcues-basic-quiz' ),
				'edit_item'          => __( 'Edit Product', 'wpcues-basic-quiz' ),
				'view_item'          => __( 'View Product', 'wpcues-basic-quiz' ),
				'all_items'          => __( 'All Products', 'wpcues-basic-quiz' ),
				'search_items'       => __( 'Search Products', 'wpcues-basic-quiz' ),
				'parent_item_colon'  => __( 'Parent Products:', 'wpcues-basic-quiz' ),
				'not_found'          => __( 'No Products found.', 'wpcues-basic-quiz' ),
				'not_found_in_trash' => __( 'No Products found in Trash.', 'wpcues-basic-quiz' )
			);
			$args = array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => true,
				'capability_type'    => 'post',
				'show_ui'=>false,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'supports'           => array( 'title', 'editor'),
				'rewrite' => array('slug'=>'product')
		
			);
			register_post_type(self::POST_TYPE,$args);
		}
		/**
		* create new badge
		*/
		public function set_product(){
			$post=get_default_post_to_edit(self::POST_TYPE,true);
			return $post;
		}
		/**
		*Edit row actions for Level table
		*/
		public function my_product_list($actions,$post){
			if($post->post_type=='wpcuebasicproduct' && 'trash' != $post->post_status ){
				$post_type_object = get_post_type_object( $post->post_type );
				$can_edit_post = current_user_can( 'edit_post', $post->ID );
				unset($actions['edit']);
				$action = '&action=edit';
				$posturl='&post='.$post->ID;
				$action='<a href="'.admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct'.$action.$posturl)).'">Edit</a>';
				$actions['edit']=$action;
				unset($actions['inline hide-if-no-js']);
				$actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="' . esc_attr( __( 'Edit this item inline' ) ) . '">' . __( 'Quick&nbsp;Edit' ) . '</a>';
				unset($actions['trash']);
				$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash' ) . "</a>";
				unset($actions['view']);
				
			}
			return $actions;
		}
		/**
		*Trash Level
		*/
		public function trash_product(){
			
			$post_id=$_POST['postid'];
			$trashed = $locked = 0;
			if ( !current_user_can( 'delete_post', $post_id) )
					wp_die( __('You are not allowed to move this item to the Trash.') );

			if ( wp_check_post_lock( $post_id ) ) {
					$locked++;
					continue;
			}
			$post_ids=array();
			$post_ids[0]=$post_id;
			
			if ( !wp_trash_post($post_id) )
				wp_die( __('Error in moving to Trash.') );
				$trashed++;
				$sendback=admin_url('edit.php?post_type=wpcuebasicproduct');
				$sendback = add_query_arg( array('trashed' => $trashed, 'ids' => join(',', $post_ids), 'locked' => $locked ), $sendback );
				echo json_encode(array('msg'=>'success','redirecturl'=>$sendback));
				die();
			}
		
		public function reset_post_new_link(){
			global $post_new_file,$post_type_object;
			if (!isset($post_type_object) || 'wpcuebasicproduct' != $post_type_object->name) return false;
			$post_new_file ='edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct';
		}
		public function edit_product_link($url,$post_id, $context ){
			global $typenow;
			if($typenow=='wpcuebasicproduct'){
				$action='&action=edit';
				$posting='&post='.$post_id;
				$url=admin_url(sprintf('edit.php?post_type=wpcuebasicquiz&page=wpcuequizproduct'. $action.$posting));
			}
			return $url;
		}
		public function get_itemlist(){
			$itemtype=$_POST['itemtype'];global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_productinfo';
			$paged=$_POST['page'];$selectall=$_POST['selectall'];
			$productid=$_POST['productid'];
			$content='<div class="itemeditorbox"><div class="selectallbutton">';
			$content.='<input type="button" class="selectallitem button button-secondary" value="'.__('Select All','wpcues-basic-quiz').'" name="selectallitems"/>';
			$content.='<input type="hidden" name="itemtype" id="itemtype" value="'.$itemtype.'">';
			$content.='<input type="hidden" name="selectallstatus" value="'.$selectall.'"></div><div class="itempage"><table>';
			if($itemtype==1){
				$existingquizitem=$wpdb->get_col($wpdb->prepare("select itemid from $table_name where itemtype=1 and productid=%d",$productid));
				$args = array('post__not_in'=>$existingquizitem,'posts_per_page' => 10,'paged' => $paged,'post_type' => 'wpcuebasicquiz' );
				$postslist = new WP_Query( $args );
				if($postslist->max_num_pages > 0){
					while ($postslist->have_posts()){
						$postslist->the_post();
						$quiz=$postslist->post;
						$content.='<tr><td style="width:7%"><input type="checkbox" name="importitems[]" value="'.$quiz->ID.'"';
						if(!(empty($selectall))){$content.= ' checked';}
						$content.='><input type="hidden" name="importitemtitles[]" value="'.$quiz->post_title.'">';
						$content.='</td><td style="width:92%;">'.$quiz->post_title.'</td></tr>';
					}
				}else{$content.='<tr><td>No Quiz present</td></tr>';}
				$content.='</table></div><div class="itempagelinks">';
				if($postslist->max_num_pages >1 ){
					for($i=1;$i <= $postslist->max_num_pages;$i++){
						$content.='<a href="#" data-value="'.$i.'" class="pageitemlink';$content.=($paged==$i)? ' active':'';
						$content.='">'.$i.'</a>';
					}
				}
				$content.='</div>';
			}else{
				$existingquizcatitem=$wpdb->get_col($wpdb->prepare("select itemid from $table_name where itemtype=2 and productid=%d",$productid));
				$args=array('taxonomy'=>'wpcuebasicquizcat');
				if(!empty($existingquizcatitem)){$args['exclude']=$existingquizcatitem;}
				$categories = get_categories($args);$posts_per_page=10;
				$categoriescount=count($categories);
				if(!(empty($categories))){
					$i = ($paged-1) * $posts_per_page;
					$maxnum=$paged * $posts_per_page;if($maxnum >$categoriescount){$maxnum=$categoriescount;}
					for( $i; $i < $maxnum; $i++ ) {
						$category = $categories[$i];
						$content.='<tr><td style="width:7%"><input type="checkbox" name="importitems[]" value="'.$category->term_id .'"';
						if(!(empty($selectall))){$content.= ' checked';}
						$content.='><input type="hidden" name="importitemtitles[]" value="'.$category->name.'">';
						$content.='</td><td style="width:92%;">'.$category->name.'</td></tr>';
						$i++;
					}
				}else{
					$content.='<tr><td>No Quiz Category having quizzess is present.</td></tr>';
				}
				$content.='</table></div><div class="itempagelinks">';
				if($categoriescount > 1){
					for($i=1;$i <= $categoriescount;$i++){
						$content.='<a href="#" data-value="'.$i.'" class="pageitemlink';
						$content.=($paged==$i)? ' active':'';
						$content.='">'.$i.'</a>';
					}
				}
				$content.='</div>';
			}
			$content.='<div class="importitembuttons"><div class="saveimportitem button button-secondary">';
			$content.=__('Add','wpcues-basic-quiz');
			$content.='</div><div class="cancelimportitem button button-secondary">';
			$content.=__('Cancel','wpcues-basic-quiz');
			$content.='</div></div></div>';
			echo json_encode(array('msg'=>'success','content'=>$content));
			die();
		}
		public function save_itemlist(){
			ob_start();
			$item=$_POST['items'];global $wpdb;$tablename=$wpdb->prefix.'wpcuequiz_productinfo';
			$productid=$_POST['productid'];
			$itemtype=$_POST['itemtype'];$value='';$content='';
			$totitem=count($item);$i=1;$stat=0;
			if(!(empty($item))){
				foreach($item as  $itemid){
					$value.='('.$productid.','.$itemid.','.$itemtype.')';
					if(($totitem >1 ) && ($i < $totitem)){$value.=',';}
					$i++;
				}
				$stat=$wpdb->query("INSERT INTO $tablename ( productid, itemid, itemtype ) VALUES $value");
			}
			if($stat){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failure'));
			}
			echo ob_get_clean();
			die();
		}
		public function remove_item(){
			$itemid=$_POST['itemid'];
			$productid=$_POST['productid'];
			global $wpdb;$tablename=$wpdb->prefix.'wpcuequiz_productinfo';
			$stat=$wpdb->delete($tablename,array( 'productid' => $productid,'itemid'=>$itemid),array('%d','%d'));
			if($stat){
				echo json_encode(array('msg'=>'success'));
			}else{
				echo json_encode(array('msg'=>'failure'));
			}
			die();
		}
		public function product_shortcode($atts){
			$productid=$atts[0];
			$product=get_post($productid);
			$productmeta=get_post_custom($productid);$disablesale=0;
			$current_user = wp_get_current_user();$userid=$current_user->ID;
			global $wpdb;$table_name=$wpdb->prefix.'wpcuequiz_productsale';
			$currentuserstat=$wpdb->get_var($wpdb->prepare("select count(id) as counter from $table_name where productid=%d and userid=%d",$productid,$userid));
			if(empty($currentuserstat)){
				$productsaled=$wpdb->get_var($wpdb->prepare("select count(id) as counter from $table_name where productid=%d",$productid));
				if($productsaled >= $productmeta['wpcueproductunits'][0]){$disablesale=1;}
				$productsale=DateTime::createFromFormat('d/m/Y',$productmeta['wpcueproductexpiry'][0]);
				$currentdate=new DateTime("now");
				if($currentdate > $productsale){$disablesale=1;}
			}
			$content='<div class="postcontainer" id="wpcueprodsc-'.$productid.'">';
			$content.='<div class="title"><h2>'.$product->post_title.'</h2>';
			$wpprocuesetting=$this->wpprocuesetting;
			if(empty($currentuserstat)){
				if(empty($disablesale)){
					$content.='<div class="productsale">'.$this->productsale_button($product->post_title,$product->post_content,$productmeta['wpcueproductprice'][0],$productmeta['wpcueproductcurrency'][0],$productid).'</div>';;
				}else{
					$content.='<div class="outstockmsg">Out of Stock</div>';}
			}
			$content.='</div><div class="productcontent">'.$product->post_content.'</div>';
			return $content;
			
		}
		public function productsale_button($product_title,$product_desc,$productprice,$productcurrency,$productid){
			$wpprocuesetting=$this->wpprocuesetting;$content='';
			if($wpprocuesetting['payment']['method'] == 1){
				$content.='<form name="stripepayment" id="stripepayment" action="" method="post">';
				$content.='<input type="hidden" name="product" value="'.$productid.'">';
				$content.='<input type="hidden" name="token" value="">';
				$content.= '<button id="stripebutton">Purchase</button></form>';
				$content.="<script src='https://checkout.stripe.com/checkout.js'></script>";
				wp_enqueue_script('customstrip-popup', plugins_url( '../js/wpcuebasicquiz-stripepopup.js', __FILE__ ));
				$product=array('producttitle'=>$product_title,
								'productdesc'=>$product_desc,
								'productprice'=>$productprice,
								'productcurrency'=>$productcurrency,
								'productid'=>$productid,
								'stripe'=>$wpprocuesetting['stripe']['publickey']
						);
				wp_localize_script('customstrip-popup','productdet',$product);
			}
			return $content;
		}
		/**
		*Add New columns for Product table
		*/
		public function new_product_columns($columns){
			$columns['productshortcode']=__('Product Shortcode','trws');
			return $columns;
		}
		/**
		*New custom column handles
		*/
		public function cusotm_product_columns($column,$post_id){
			if($column=='productshortcode'){
				echo '[wpcuebasicproduct '.$post_id.']';
			}
		}
		public function wpcuequizbasic_add_trigger($vars){
			 $vars[] = 'wpcuequiz_stripe';
			return $vars;
		}
		public function wpcuequizbasic_trigger_check(){
			if(intval(get_query_var('wpcuequiz_stripe')) == 1) {
				$wpprocuesetting=$this->wpprocuesetting;
				$token=$_POST['token'];
				$productid=$_POST['product'];
				 $current_user = wp_get_current_user();
				$productmeta=get_post_custom($productid);
				require_once(sprintf("%s/../lib/stripe/Stripe.php", dirname(__FILE__)));
				try{
					Stripe::setApiKey($wpprocuesetting['stripe']['privatekey']);
					$charge = Stripe_Charge::create(array(
						'amount' => $productmeta['wpcueproductprice'][0]*100, // Amount in cents!
						'currency' => $productmeta['wpcueproductcurrency'][0],
						'card' => $token,
						'description' => $current_user->user_email
					));
				}catch(Stripe_CardError $e){
				}
				if($charge->paid == true){ 
					global $wpdb;
					$table_name=$wpdb->prefix.'wpcuequiz_productsale';
					$wpdb->insert($table_name,
							array('productid'=>$productid,'userid'=>$current_user->ID,'purchasedate'=>current_time( 'mysql' )),
							array('%d','%d','%s') 
						);	
				}
				
			}
		}
    } // END class WpCueBasicBadge
} // END if(!class_exists('WpCueBasicBadge'))
/* EOF */