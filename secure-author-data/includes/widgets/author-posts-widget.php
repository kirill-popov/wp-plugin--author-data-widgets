<?php

namespace Secure_Author_Data_Plugin\Widgets;

use Exception;
use Secure_Author_Data_Plugin\Widgets\Interfaces\Author_Secure_Data_Widget_Interface;
use Secure_Author_Data_Plugin\Widgets\Notices\Notice;
use WP_Widget;

class Author_Posts_Widget extends WP_Widget implements Author_Secure_Data_Widget_Interface
{
	use Notice;

	private $nonce_name;
	private $front_nonce_name;
	private $db_messages_key;

    public function __construct() {
		parent::__construct(
			'author_posts_widget', // Base ID
			'Author_Posts_Widget', // Name
			array( 'description' => __( 'Widget that displays Author\'s posts count and special message.', 'secure-author-data' ) ) // Args
		);

		$this->nonce_name = $this->get_nonce_name();
		$this->front_nonce_name = $this->get_front_nonce_name();
		$this->db_messages_key = 'secure_author_widget_messages';

		add_action('wp_ajax_widget_author_autocomplete', [$this, 'widget_author_autocomplete']);
		// add_action('wp_ajax_nopriv_widget_author_autocomplete', [$this, 'widget_author_autocomplete']);
		add_action('wp_ajax_save_widget_author_messages', [$this, 'widget_author_message']);
		// add_action('wp_ajax_nopriv_save_widget_author_messages', [$this, 'widget_author_message']);

		add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
		add_action('wp_enqueue_scripts', [$this, 'load_front_scripts'], 99);
	}

	private function get_nonce_name() {
		return '_' . strtolower(get_class($this)) . '_nonce';
	}

	private function get_front_nonce_name() {
		return '_nonce';
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance): void
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$author_id = apply_filters('widget_title', $instance['author_id']);
		$message = apply_filters('widget_title', $instance['message']);
		$author = null;

		echo $before_widget;

		if (!empty($title)) {
			echo $before_title . $title . $after_title;
		}

		if (!empty($author_id && is_numeric($author_id))) {
			$author = get_user_by('ID', $author_id);
			if ($author) {
				echo '<p>' . __('Posts count', 'secure-author-data') . ': ' . count_user_posts($author_id) . '</p>';
			}
		}

		if (!empty($message)) {
			echo '<p>' . $message . '</p>';
		}
		?>
		<div class="messages-block">
			<?php
			$author_messages = get_user_meta($author_id, $this->db_messages_key, true);
			?>
			<h6>Messages:</h6>
			<ul class="messages-list">
				<?php
				if (!empty($author_messages)) {
					foreach ($author_messages as $msg) {
						?>
						<li><?php echo esc_html($msg);?></li>
						<?php
					}
				}
				?>
			</ul>
		</div>
		<?php
		if ($author && is_user_logged_in()) {
			?>
			<form action="POST" class="author-messages-form">
				<input type="hidden" name="author_id" value="<?php echo $author_id;?>">
				<textarea name="message" cols="30" rows="2"></textarea>
				<input type="hidden" name="<?php echo $this->front_nonce_name;?>" value="<?php echo wp_create_nonce($this->front_nonce_name);?>">
				<input type="submit" value="Add message">
			</form>
			<?php
		}

		echo $after_widget;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance): void
	{
		$author = isset($instance['author']) ? $instance['author'] : '';
		$author_id = isset($instance['author_id']) ? $instance['author_id'] : '';
		$message = isset($instance['message']) ? $instance['message'] : '';
		$notice = $this->getNotice();

		if (!empty($notice)) {
			echo $notice;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_name('author');?>"><?php _e('Author:');?></label>
			<input type="text" class="author-data-widget-author"
			autocomplete="false"
				name="<?php echo $this->get_field_name('author');?>"
				id="<?php echo $this->get_field_id('author');?>"
				value="<?php echo esc_attr($author);?>"
			/>
			<input type="hidden"
				name="<?php echo $this->get_field_name('author_id');?>"
				value="<?php echo esc_attr($author_id);?>"
			>
		</p>
		<p>
			<label for="<?php echo $this->get_field_name('message');?>"><?php _e('Message:');?></label>
			<textarea
				cols="30" rows="3"
				name="<?php echo $this->get_field_name('message');?>"
				id="<?php echo $this->get_field_id('message');?>"
			><?php echo $message;?></textarea>
		</p>
		<input type="hidden" name="<?php echo $this->get_field_name('title');?>" value="Secure Author Data">
		<?php wp_nonce_field(get_class($this), $this->get_field_name($this->nonce_name));?>
		<?php
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance): array
	{
		if (!isset($new_instance[$this->nonce_name])
		|| !wp_verify_nonce($new_instance[$this->nonce_name], get_class($this))) {
			$this->setErrorNotice('Invalid nonce.');
			return $old_instance;
		}

		$data = [
			'title' => (!empty($new_instance['title']) ? strip_tags($new_instance['title']) : ''),
			'author' => (!empty($new_instance['author']) ? strip_tags($new_instance['author']) : ''),
			'message' => (!empty($new_instance['message']) ? strip_tags($new_instance['message']) : ''),
		];

		if (!empty($new_instance['author'])) {
			if (!empty($new_instance['author_id']) && is_numeric($new_instance['author_id'])) {
				$data['author_id'] = $new_instance['author_id'];
				$this->setSuccessNotice('Saved.');
			} else {
				$data['author_id'] = !empty($old_instance['author_id']) ? $old_instance['author_id'] : '';
				$this->setErrorNotice('Wrong Author. You have to select Author from the list.');
			}
		}
		return $data;
	}

	public function load_admin_scripts(): void
	{
		$screen = get_current_screen();

		if ('widgets' == $screen->id) {
			wp_enqueue_script('widget_admin_script', plugin_dir_url(__FILE__) . 'js/admin_script.js', array('jquery'));
		}
	}

	public function load_front_scripts(): void
	{
		wp_enqueue_script('widget_script', plugin_dir_url(__FILE__) . 'js/script.js', array('jquery'));
		wp_localize_script('widget_script', 'myajax',
			[
				'url' => admin_url('admin-ajax.php')
			]
		);
	}

    public function load_styles(): void
	{
	}


	function widget_author_autocomplete() {
		$result = [];
		$search = !empty($_POST['s']) ? esc_sql($_POST['s']) : '';

		if (!empty($search)) {
			$search = explode(' ', $search);

			if (empty($search[1])) {
				$meta_query = [
					'relation' => 'OR',
					[
						'key'     => 'first_name',
						'value'   => '^'.trim($search[0]),
						'compare' => 'REGEXP'
					],
					[
						'key'     => 'last_name',
						'value'   => '^'.trim($search[0]),
						'compare' => 'REGEXP'
					]
				];
			} else {
				$meta_query = [
					'relation' => 'AND',
					[
						'key'     => 'first_name',
						'value'   => '^'.trim($search[0]),
						'compare' => 'REGEXP'
					],
					[
						'key'     => 'last_name',
						'value'   => '^'.trim($search[1]),
						'compare' => 'REGEXP'
					]
				];
			}

			$args = [
				'role' => 'Author',
				'meta_query' => $meta_query,
				'fields' => 'all_with_meta',
				'orderby' => 'meta_value',
				'meta_key' => 'first_name',
			];

			$users = get_users($args);
			foreach ($users as $user) {
				$result[] = [
					'id' => $user->ID,
					'name' => $user->first_name . ' ' . $user->last_name
				];
			}
		}

		wp_send_json($result);
	}

	function widget_author_message() {
		$result = [
			'success' => false,
			'message' => '',
		];

		try {
			if (empty($_POST[$this->front_nonce_name])
			|| !wp_verify_nonce($_POST[$this->front_nonce_name], $this->front_nonce_name)) {
				throw new Exception("Invalid nonce.");
			}

			if (empty($_POST['author_id'])
			|| !is_numeric($_POST['author_id'])) {
				throw new Exception("Wrong Author ID value.");
			}

			if (empty($_POST['message'])) {
				throw new Exception("Empty message.");
			}

			$user = get_user_by('ID', $_POST['author_id']);
			if (!$user) {
				throw new Exception("User not found.");
			}

			$widget_messages = get_user_meta($_POST['author_id'], $this->db_messages_key, true);
			$widget_messages = !empty($widget_messages) ? $widget_messages : [];
			$widget_messages[] = esc_sql($_POST['message']);
			update_user_meta($_POST['author_id'], $this->db_messages_key, $widget_messages);

			$result['success'] = true;
			$result['message'] = 'Saved.';

		} catch (Exception $e) {
			$result['message'] = $e->getMessage();
		}

		wp_send_json($result);
	}
}
