<?php

namespace Uncanny_Automator;

/**
 * Class UOG_USERREDEEMS_GROUPKEY
 *
 * @package Uncanny_Automator
 */
class UOG_USERREDEEMS_GROUPKEY {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'UOG';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'REDEEMSGROUPKEY';
		$this->trigger_meta = 'UNCANNYGROUPS';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$trigger = array(
			'author'              => Automator()->get_author_name( $this->trigger_code ),
			'support_link'        => Automator()->get_author_support_link( $this->trigger_code, 'integration/uncanny-groups/' ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - Uncanny Groups */
			'sentence'            => sprintf( esc_attr__( 'A user redeems {{a group:%1$s}} key', 'uncanny-automator' ), $this->trigger_meta ),
			/* translators: Logged-in trigger - Uncanny Groups */
			'select_option_name'  => esc_attr__( 'A user redeems {{a group}} key', 'uncanny-automator' ),
			'action'              => 'ulgm_user_redeems_group_key',
			'priority'            => 20,
			'accepted_args'       => 2,
			'validation_function' => array( $this, 'user_redeems' ),
			'options'             => array(
				Automator()->helpers->recipe->uncanny_groups->options->all_ld_groups( null, $this->trigger_meta ),
			),
		);

		Automator()->register->trigger( $trigger );
	}

	/**
	 * @param $user_id
	 * @param $code
	 */
	public function user_redeems( $user_id, $code ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( empty( $user_id ) ) {
			return;
		}

		if ( is_array( $code ) && 'success' !== $code['result'] ) {
			return;
		}

		$recipes = Automator()->get->recipes_from_trigger_code( $this->trigger_code );
		if ( empty( $recipes ) ) {
			return;
		}
		$required_group     = Automator()->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = array();
		//Add where option is set to Any product
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {
				$trigger_id = $trigger['ID'];//return early for all products
				if ( isset( $required_group[ $recipe_id ] ) && isset( $required_group[ $recipe_id ][ $trigger_id ] ) ) {
					if (
						intval( '-1' ) === intval( $required_group[ $recipe_id ][ $trigger_id ] ) ||
						absint( $code['ld_group_id'] ) === absint( $required_group[ $recipe_id ][ $trigger_id ] )
					) {
						$matched_recipe_ids[ $recipe_id ] = array(
							'recipe_id'  => $recipe_id,
							'trigger_id' => $trigger_id,
						);
					}
				}
			}
		}

		if ( empty( $matched_recipe_ids ) ) {
			return;
		}
		foreach ( $matched_recipe_ids as $matched_recipe_id ) {
			$pass_args = array(
				'code'             => $this->trigger_code,
				'meta'             => $this->trigger_meta,
				'recipe_to_match'  => $matched_recipe_id['recipe_id'],
				'trigger_to_match' => $matched_recipe_id['trigger_id'],
				'ignore_post_id'   => true,
				'user_id'          => $user_id,
				'is_signed_in'     => true,
			);

			$args = Automator()->maybe_add_trigger_entry( $pass_args, false );

			if ( isset( $args ) ) {
				foreach ( $args as $result ) {
					if ( true === $result['result'] ) {

						$trigger_meta = array(
							'user_id'        => $user_id,
							'trigger_id'     => $result['args']['trigger_id'],
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						);

						$trigger_meta['meta_key']   = 'code_details';
						$trigger_meta['meta_value'] = maybe_serialize( $code );
						Automator()->insert_trigger_meta( $trigger_meta );

						Automator()->maybe_trigger_complete( $result['args'] );
					}
				}
			}
		}
	}
}
