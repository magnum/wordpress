<?php
/**
 * Agent Open Ticket
 *
 * @package     AutomatorWP\Integrations\Awesome_Support\Triggers\Agent_Open_Ticket
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Awesome_Support_Agent_Open_Ticket extends AutomatorWP_Integration_Trigger {

    public $integration = 'awesome_support';
    public $trigger = 'awesome_support_agent_open_ticket';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'label'             => __( 'Agent opens a ticket', 'automatorwp' ),
            'select_option'     => __( 'Agent <strong>opens</strong> a ticket', 'automatorwp' ),
            /* translators: %1$s: Number of times. */
            'edit_label'        => sprintf( __( 'Agent opens a ticket %1$s time(s)', 'automatorwp' ), '{times}' ),
            'log_label'         => __( 'Agent opens a ticket', 'automatorwp' ),
            'action'            => array(
                'wpas_post_new_ticket_admin',
                'wpas_open_ticket_after',
            ),
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 1,
            'options'           => array(
                'times' => automatorwp_utilities_times_option()
            ),
            'tags' => array(
                automatorwp_utilities_post_tags( __( 'Ticket', 'automatorwp' ) ),
                'times' => automatorwp_utilities_times_tag( true )
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param int $ticket_id Ticket ID
     */
    public function listener( $ticket_id ) {

        $user_id = intval( get_post_meta( $ticket_id, '_wpas_assignee', true ) );

        automatorwp_trigger_event( array(
            'trigger' => $this->trigger,
            'post_id' => $ticket_id,
            'user_id' => $user_id,
        ) );

    }

}

new AutomatorWP_Awesome_Support_Agent_Open_Ticket();