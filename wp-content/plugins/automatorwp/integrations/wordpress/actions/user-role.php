<?php
/**
 * User Role
 *
 * @package     AutomatorWP\Integrations\WordPress\Actions\User_Role
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_WordPress_User_Role extends AutomatorWP_Integration_Action {

    /**
     * Initialize the action
     *
     * @since 1.0.0
     */
    public function __construct( $integration ) {

        $this->integration = $integration;
        $this->action = $integration . '_user_role';

        parent::__construct();

    }

    /**
     * Register the action
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_action( $this->action, array(
            'integration'       => $this->integration,
            'label'             => __( 'Add, change or remove role to user', 'automatorwp' ),
            'select_option'     => __( 'Add, change or remove <strong>role</strong> to user', 'automatorwp' ),
            /* translators: %1$s: Operation (add, change or remove). %2$s: Role. %3$s: User. */
            'edit_label'        => sprintf( __( '%1$s role %2$s to %3$s', 'automatorwp' ), '{operation}', '{role}', '{user}' ),
            /* translators: %1$s: Operation (add, change or remove). %2$s: Role. %3$s: User. */
            'log_label'         => sprintf( __( '%1$s role %2$s to %3$s', 'automatorwp' ), '{operation}', '{role}', '{user}' ),
            'options'           => array(
                'operation' => array(
                    'from' => 'operation',
                    'fields' => array(
                        'operation' => array(
                            'name' => __( 'Operation:', 'automatorwp' ),
                            'type' => 'select',
                            'options' => array(
                                'add'       => __( 'Add', 'automatorwp' ),
                                'change'    => __( 'Change', 'automatorwp' ),
                                'remove'    => __( 'Remove', 'automatorwp' ),
                            ),
                            'default' => 'add'
                        ),
                    )
                ),
                'role' => array(
                    'from' => 'role',
                    'default' => '',
                    'fields' => array(
                        'role' => automatorwp_utilities_role_field( array(
                            'option_custom' => true,
                        ) ),
                        'role_custom' => automatorwp_utilities_custom_field( array(
                            'option_custom_desc' => __( 'Role name.', 'automatorwp' )
                        ) ),
                    )
                ),
                'user' => array(
                    'default' => __ ( 'user', 'automatorwp' ),
                    'fields' => array(
                        'user_id' => array(
                            'name' => __( 'User ID:', 'automatorwp' ),
                            'desc' => __( 'The user\'s ID to update their role. Leave empty to assign the user that completes the automation.', 'automatorwp' ),
                            'type' => 'text',
                            'default' => ''
                        ),
                    ),
                ),
            ),
            'tags' => array_merge(
                automatorwp_utilities_user_tags()
            )
        ) );

    }

    /**
     * Action execution function
     *
     * @since 1.0.0
     *
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     */
    public function execute( $action, $user_id, $action_options, $automation ) {

        // Shorthand
        $operation = $action_options['operation'];
        $role = $action_options['role'];
        $user_id_target = absint( $action_options['user_id'] );
        $this->user_data = array();

        if( $user_id_target === 0 ) {
            $user_id_target = $user_id;
        }

        $user = get_userdata( $user_id_target );

        $this->user_id = $user_id_target;

        // The user fields
        $user_fields = array(
            'user_login',
            'user_email',
            'first_name',
            'last_name',
            'user_url',
            'user_pass',
            'display_name',
        );

        foreach( $user_fields as $user_field ) {
                $this->user_data[$user_field] = $user->$user_field;
        }

        // Bail if user does not exists
        if( ! $user ) {
            return;
        }

        // Ensure operation default value
        if( empty( $operation ) ) {
            $operation = 'add';
        }

        // Bail if empty role to assign
        if( $role === 'any' || empty( $role ) ) {
            return;
        }

        $roles = automatorwp_get_editable_roles();

        // Bail if empty role to assign
        if( ! isset( $roles[$role] ) ) {
            return;
        }

        switch ( $operation ) {
            case 'add':
                // Add the role to the user
                $user->add_role( $role );
                break;
            case 'change':
                // Set the role to the user
                $user->set_role( $role );
                break;
            case 'remove':
                // Bail if user hasn't this role
                if( ! in_array( $role, $user->roles ) ) {
                    return;
                }

                // Don't remove any role if is the last role
                if( count( $user->roles ) === 1 ) {
                    return;
                }

                // Remove the role to the user
                $user->remove_role( $role );
                break;
        }

    }

    /**
     * Register required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_action_log_meta', array( $this, 'log_meta' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Action custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $action             The action object
     * @param int       $user_id            The user ID
     * @param array     $action_options     The action's stored options (with tags already passed)
     * @param stdClass  $automation         The action's automation object
     *
     * @return array
     */
    public function log_meta( $log_meta, $action, $user_id, $action_options, $automation ) {

        // Bail if action type don't match this action
        if( $action->type !== $this->action ) {
            return $log_meta;
        }

        // Store user fields
        $user_fields = array(
            'user_login',
            'user_email',
            'first_name',
            'last_name',
            'user_url',
            'user_pass',
            'display_name',
        );

        foreach( $user_fields as $user_field ) {
            $log_meta[$user_field] = $this->user_data[$user_field];
        }

        // Store user ID
        $log_meta['user_id'] = $this->user_id;

        return $log_meta;
    }

}

new AutomatorWP_WordPress_User_Role( 'wordpress' );
new AutomatorWP_WordPress_User_Role( 'users' );