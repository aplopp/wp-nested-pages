<?php namespace NestedPages\Entities\Listing;

use NestedPages\Helpers;
use NestedPages\Entities\Confirmation\ConfirmationFactory;
use NestedPages\Entities\Post\PostDataFactory;
use NestedPages\Entities\Post\PostRepository;
use NestedPages\Entities\User\UserRepository;
use NestedPages\Entities\PostType\PostTypeRepository;
use NestedPages\Entities\Listing\ListingRepository;

/**
* Primary Post Listing
*/
class Listing {

	/**
	* Post Type
	* @var object
	*/
	private $post_type;

	/**
	* Hierarchical Taxonomies
	* @var array
	*/
	private $h_taxonomies;

	/**
	* Flat Taxonomies
	* @var array
	*/
	private $f_taxonomies;

	/**
	* Post Data Factory
	*/
	private $post_data_factory;

	/**
	* Post Data
	* @var object
	*/
	private $post;

	/**
	* Post Repository
	*/
	private $post_repo;

	/**
	* Post Type Repository
	*/
	private $post_type_repo;

	/**
	* Listing Repository
	*/
	private $listing_repo;

	/**
	* Confirmation Factory
	*/
	private $confirmation;

	/**
	* User Repository
	*/
	private $user;


	public function __construct($post_type)
	{
		$this->setPostType($post_type);
		$this->post_repo = new PostRepository;
		$this->user = new UserRepository;
		$this->confirmation = new ConfirmationFactory;
		$this->post_type_repo = new PostTypeRepository;
		$this->listing_repo = new ListingRepository;
		$this->post_data_factory = new PostDataFactory;
	}


	/**
	* Called by Menu Class
	* Instantiates Listing Class
	* @since 1.2.0
	*/
	public static function admin_menu($post_type) {
		$class_name = get_class();
		$classinstance = new $class_name($post_type);
		return array(&$classinstance, "listPosts");
	}


	/**
	* Set the Post Type
	* @since 1.1.16
	*/
	private function setPostType($post_type)
	{
		$this->post_type = get_post_type_object($post_type);
	}


	/**
	* The Main View
	* Replaces Default Post Listing
	*/
	public function listPosts()
	{
		include( Helpers::view('listing') );
	}


	/**
	* Set the Taxonomies for Post Type
	*/
	private function setTaxonomies()
	{
		$this->h_taxonomies = $this->post_type_repo->getTaxonomies($this->post_type->name, true);
		$this->f_taxonomies = $this->post_type_repo->getTaxonomies($this->post_type->name, false);
	}


	/**
	* Opening list tag <ol>
	* @param array $pages - array of page objects from current query
	* @param int $count - current count in loop
	*/
	private function listOpening($pages, $count)
	{
		// Get array of child pages
		$children = array();
		$all_children = $pages->posts;
		foreach($all_children as $child){
			array_push($children, $child->ID);
		}
		// Compare child pages with user's toggled pages
		$compared = array_intersect($this->listing_repo->visiblePages(), $children);

		if ( $count == 1 ) {
			echo ( $this->user->canSortPages() ) 
				? '<ol class="sortable nplist visible">' 
				: '<ol class="sortable no-sort nplist" visible>';
		} else {
			echo '<ol class="nplist';
			if ( count($compared) > 0 ) echo ' visible" style="display:block;';
			echo '">';	
		} 
	}


	/**
	* Set Post Data
	* @param object post object
	*/
	private function setPost($post)
	{
		$this->post = $this->post_data_factory->build($post);
	}


	/**
	* Get count of published posts
	* @param object $pages (WP Query object)
	*/
	private function publishCount($pages)
	{
		$publish_count = 1;
		foreach ( $pages->posts as $p ){
			if ( $p->post_status !== 'trash' ) $publish_count++;
		}
		return $publish_count;
	}


	/**
	* Loop through all the pages and create the nested / sortable list
	* Recursive Method, called in page.php view
	*/
	private function loopPosts($parent_id = 0, $count = 0)
	{
		$this->setTaxonomies();
		$post_type = ( $this->post_type->name == 'page' ) ? array('page', 'np-redirect') : array($this->post_type->name);
		$query_args = array(
			'post_type' => $post_type,
			'posts_per_page' => -1,
			'orderby' => 'menu_order',
			'post_status' => array('publish', 'pending', 'draft', 'private', 'future', 'trash'),
			'post_parent' => $parent_id,
			'order' => 'ASC'
		);
		$pages = new \WP_Query(apply_filters('nestedpages_page_listing', $query_args));
		
		if ( $pages->have_posts() ) :
			$count++;

			if ( $this->publishCount($pages) > 1 ){
				$this->listOpening($pages, $count);			
			}
			
			while ( $pages->have_posts() ) : $pages->the_post();

				global $post;
				$this->setPost($post);

				if ( $this->post->status !== 'trash' ) :

					echo '<li id="menuItem_' . $this->post->id . '" class="page-row';

					// Published?
					if ( $this->post->status == 'publish' ) echo ' published';
					
					// Hidden in Nested Pages?
					if ( $this->post->np_status == 'hide' ) echo ' np-hide';

					// Taxonomies
					echo ' ' . $this->post_repo->getTaxonomyCSS($this->post->id, $this->h_taxonomies);
					echo ' ' . $this->post_repo->getTaxonomyCSS($this->post->id, $this->f_taxonomies, false);
					
					echo '">';
					
					$count++;

					$row_view = ( $this->post->type !== 'np-redirect' ) ? 'partials/row' : 'partials/row-link';
					include( Helpers::view($row_view) );

				endif; // trash status
				
				$this->loopPosts($this->post->id, $count);

				if ( $this->post->status !== 'trash' ) {
					echo '</li>';
				}				

			endwhile; // Loop
			
			if ( $this->publishCount($pages) > 1 ){
				echo '</ol>';
			}

		endif; wp_reset_postdata();
	}


}