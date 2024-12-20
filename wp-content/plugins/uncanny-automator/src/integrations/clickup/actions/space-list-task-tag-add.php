<?php
namespace Uncanny_Automator;

/**
 * Class Space_List_Task_Tag_Add
 *
 * @package Uncanny_Automator
 */
class Space_List_Task_Tag_Add {

	use Recipe\Actions;

	/**
	 * Method __construct
	 *
	 * @return void
	 */
	public function __construct() {

		$this->setup_action();

		$this->set_helpers( new ClickUp_Helpers( false ) );

	}

	/**
	 * Setups the Action.
	 *
	 * @return void
	 */
	public function setup_action() {

		$this->set_integration( 'CLICKUP' );

		$this->set_action_code( 'CLICKUP_SPACE_LIST_TASK_TAG_ADD' );

		$this->set_action_meta( 'CLICKUP_SPACE_LIST_TASK_TAG_ADD_META' );

		$this->set_is_pro( false );

		$this->set_support_link( Automator()->get_author_support_link( $this->get_action_code(), 'knowledge-base/clickup/' ) );

		$this->set_requires_user( false );

		$this->set_sentence(
			sprintf(
				/* translators: Action sentence */
				esc_attr__(
					'Add {{a tag:%1$s}} to {{a specific task:%2$s}} in {{a specific list:%3$s}} of {{a specific space:%4$s}}',
					'uncanny-automator'
				),
				$this->get_action_meta(),
				'TASK:' . $this->get_action_meta(),
				'LIST:' . $this->get_action_meta(),
				'SPACE:' . $this->get_action_meta()
			)
		);

		$this->set_readable_sentence(
			esc_attr__(
				'Add {{a tag}} to {{a specific task}} in {{a specific list}} of {{a specific space}}',
				'uncanny-automator'
			)
		);

		$this->set_options_callback( array( $this, 'load_options' ) );

		$this->set_background_processing( true );

		$this->register_action();

	}

	/**
	 * Loads options.
	 *
	 * @return void.
	 */
	public function load_options() {
		return Automator()->utilities->keep_order_of_options(
			array(
				'options_group' => array(
					$this->get_action_meta() => $this->get_helpers()->get_action_fields( $this, 'space-list-task-tag-fields' ),
				),
			)
		);
	}

	/**
	 * Processes the action.
	 *
	 * @return void.
	 */
	public function process_action( $user_id, $action_data, $recipe_id, $args, $parsed ) {

		try {

			$body = array(
				'action'   => 'task_add_tag',
				'task_id'  => isset( $parsed['TASK'] ) ? sanitize_text_field( $parsed['TASK'] ) : 0,
				'tag_name' => isset( $parsed[ $this->get_action_meta() ] ) ? sanitize_text_field( $parsed[ $this->get_action_meta() ] ) : '',
			);

			$response = $this->get_helpers()->api_request(
				$this->get_helpers()->get_client(),
				$body,
				$action_data
			);

			Automator()->complete->action( $user_id, $action_data, $recipe_id );

		} catch ( \Exception $e ) {

			$action_data['complete_with_errors'] = true;

			Automator()->complete->action( $user_id, $action_data, $recipe_id, $e->getMessage() );

		}

	}

}
