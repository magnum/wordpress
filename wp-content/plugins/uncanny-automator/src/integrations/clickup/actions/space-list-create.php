<?php
namespace Uncanny_Automator;

/**
 * Class Space_List_Create
 *
 * @package Uncanny_Automator
 */
class Space_List_Create {

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

		$this->set_action_code( 'CLICKUP_LIST_CREATE' );

		$this->set_action_meta( 'CLICKUP_LIST_CREATE_META' );

		$this->set_is_pro( false );

		$this->set_support_link( Automator()->get_author_support_link( $this->get_action_code(), 'knowledge-base/clickup/' ) );

		$this->set_requires_user( false );

		$this->set_sentence(
			sprintf(
				/* translators: Action sentence */
				esc_attr__( 'Create {{a list:%1$s}} in {{a specific folder:%2$s}} {{a specific space:%3$s}} in {{a specific team:%4$s}}', 'uncanny-automator' ),
				$this->get_action_meta(),
				'FOLDER:' . $this->get_action_meta(),
				'SPACE:' . $this->get_action_meta(),
				'TEAM:' . $this->get_action_meta()
			)
		);

		$this->set_readable_sentence( esc_attr__( 'Create {{a list}} in {{a specific folder}} in {{a specific space}} in {{a specific team}}', 'uncanny-automator' ) );

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
					$this->get_action_meta() => $this->get_helpers()->get_action_fields( $this, 'space-list-create-fields' ),
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
				'action'    => 'create_list',
				'folder_id' => isset( $parsed['FOLDER'] ) ? sanitize_text_field( $parsed['FOLDER'] ) : 0,
				'name'      => isset( $parsed[ $this->get_action_meta() ] ) ? sanitize_text_field( $parsed[ $this->get_action_meta() ] ) : '',
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
