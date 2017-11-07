<?php

defined( 'ABSPATH' ) or die();

class test_AlwaysAllowAdminComments extends WP_UnitTestCase {

	private static $meta_key = 'c2c_always_allow_admin_comments';

	public function tearDown() {
		parent::tearDown();
		$this->unset_current_user();
		// Ensure the filter gets removed
		remove_filter( 'c2c_always_allow_admin_comments_disable', array( $this, 'disable_admin_commenting_on_specified_post' ), 10, 2 );
		remove_filter( 'c2c_always_allow_admin_comments_disable', array( $this, 'enable_admin_commenting_on_specified_post' ), 10, 2 );
	}


	//
	//
	// HELPER FUNCTIONS
	//
	//


	private function create_user( $role, $set_as_current = true ) {
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		if ( $set_as_current ) {
			wp_set_current_user( $user_id );
		}
		return $user_id;
	}

	// helper function, unsets current user globally. Taken from post.php test.
	private function unset_current_user() {
		global $current_user, $user_ID;

		$current_user = $user_ID = null;
	}


	//
	//
	// FUNCTIONS FOR HOOKING ACTIONS/FILTERS
	//
	//


	public function disable_admin_commenting_on_specified_post( $status, $post_id ) {
		$post = get_post( $post_id );
		return 'Admin cannot comment' === $post->post_title ? true : $status;
	}

	public function enable_admin_commenting_on_specified_post( $status, $post_id ) {
		$post = get_post( $post_id );
		return 'Admin cannot comment' === $post->post_title ? false : $status;
	}


	//
	//
	// TESTS
	//
	//


	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_AlwaysAllowAdminComments' ) );
	}

	public function test_version() {
		$this->assertEquals( '1.1.1', c2c_AlwaysAllowAdminComments::get_instance()->version() );
	}

	public function test_instance_object_is_returned() {
		$this->assertTrue( is_a( c2c_AlwaysAllowAdminComments::get_instance(), 'c2c_AlwaysAllowAdminComments' ) );
	}

	public function test_admin_can_comment_when_comments_closed() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'closed' ) );
		$user_id = $this->create_user( 'administrator' );

		$this->assertTrue( comments_open( $post_id ) );
	}

	public function test_non_admin_cannot_comment_when_comments_closed() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'closed' ) );

		$user_id = $this->create_user( 'editor' );
		$this->assertFalse( comments_open( $post_id ) );

		$user_id = $this->create_user( 'subscriber' );
		$this->assertFalse( comments_open( $post_id ) );

		$this->unset_current_user();
		$this->assertFalse( comments_open( $post_id ) );
	}

	public function test_anyone_can_comment_when_comments_open() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'open' ) );

		$user_id = $this->create_user( 'administrator' );
		$this->assertTrue( comments_open( $post_id ) );

		$user_id = $this->create_user( 'editor' );
		$this->assertTrue( comments_open( $post_id ) );

		$user_id = $this->create_user( 'subscriber' );
		$this->assertTrue( comments_open( $post_id ) );

		$this->unset_current_user();
		$this->assertTrue( comments_open( $post_id ) );
	}

	public function test_admin_can_comment_on_old_posts() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'open', 'post_date' => '2010-10-01 13:12:25', 'post_date_gmt' => '2010-10-01 13:12:25' ) );

		update_option( 'close_comments_for_old_posts', '1' );
		$x = get_option( 'close_comments_for_old_posts' );
		$y = (int) get_option( 'close_comments_days_old' );
		// In case WP closed comments on older posts
		$this->assertEquals( true, $x );
		$this->assertFalse( comments_open( $post_id ) );

		update_option( 'close_comments_for_old_posts', '0' );
	}

	public function test_post_setting_can_disable_admin_commenting() {
		$post_id1 = $this->factory->post->create( array( 'post_title' => 'Admin cannot comment', 'comment_status' => 'closed' ) );
		$post_id2 = $this->factory->post->create( array( 'post_title' => 'Aaaa', 'comment_status' => 'closed' ) );
		$user_id = $this->create_user( 'administrator' );

		$obj = c2c_AlwaysAllowAdminComments::get_instance();
		$obj->set_admin_can_comment_on_post( false, $post_id1 );

		$this->assertFalse( comments_open( $post_id1 ) );
		$this->assertTrue(  comments_open( $post_id2 ) );
		$this->assertTrue(  $obj->is_admin_commenting_disabled( $post_id1 ) );
		$this->assertTrue(  $obj->is_admin_commenting_disabled( $post_id1, false ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2 ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2, false ) );
	}

	public function test_filter_can_disable_admin_commenting() {
		$post_id1 = $this->factory->post->create( array( 'post_title' => 'Admin cannot comment', 'comment_status' => 'closed' ) );
		$post_id2 = $this->factory->post->create( array( 'post_title' => 'Aaaa', 'comment_status' => 'closed' ) );
		$user_id = $this->create_user( 'administrator' );

		$this->assertTrue( comments_open( $post_id1 ) );
		$this->assertTrue( comments_open( $post_id2 ) );

		$obj = c2c_AlwaysAllowAdminComments::get_instance();
		add_filter( 'c2c_always_allow_admin_comments_disable', array( $this, 'disable_admin_commenting_on_specified_post' ), 10, 2 );

		$this->assertFalse( comments_open( $post_id1 ) );
		$this->assertTrue(  comments_open( $post_id2 ) );
		$this->assertTrue(  $obj->is_admin_commenting_disabled( $post_id1 ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id1, false ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2 ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2, false ) );
	}

	public function test_filter_overrides_post_setting() {
		$post_id1 = $this->factory->post->create( array( 'post_title' => 'Admin cannot comment', 'comment_status' => 'closed' ) );
		$post_id2 = $this->factory->post->create( array( 'post_title' => 'Aaaa', 'comment_status' => 'closed' ) );
		$user_id = $this->create_user( 'administrator' );

		$this->assertTrue( comments_open( $post_id1 ) );
		$this->assertTrue( comments_open( $post_id2 ) );

		// Directly disable admin commenting in the post, but enable it via filter.
		$obj = c2c_AlwaysAllowAdminComments::get_instance();
		$obj->set_admin_can_comment_on_post( false, $post_id1 );
		add_filter( 'c2c_always_allow_admin_comments_disable', array( $this, 'enable_admin_commenting_on_specified_post' ), 10, 2 );

		$this->assertTrue(  comments_open( $post_id1 ) );
		$this->assertTrue(  comments_open( $post_id2 ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id1 ) );
		$this->assertTrue(  $obj->is_admin_commenting_disabled( $post_id1, false ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2 ) );
		$this->assertFalse( $obj->is_admin_commenting_disabled( $post_id2, false ) );
	}

	public function test_set_admin_can_comment_on_post() {
		$post_id = $this->factory->post->create( array( 'comment_status' => 'closed' ) );

		$this->assertFalse( comments_open( $post_id ) );

		$user_id = $this->create_user( 'administrator' );

		$this->assertTrue( comments_open( $post_id ) );

		// Directly disable admin commenting in the post, but enable it via filter.
		$obj = c2c_AlwaysAllowAdminComments::get_instance();
		$obj->set_admin_can_comment_on_post( true, $post_id );

		$this->assertTrue( comments_open( $post_id ) );

		$obj->set_admin_can_comment_on_post( false, $post_id );

		$this->assertFalse( comments_open( $post_id ) );
	}

	public function test_meta_is_registered() {
		$this->assertTrue( registered_meta_key_exists( 'post', self::$meta_key ) );
	}

}
