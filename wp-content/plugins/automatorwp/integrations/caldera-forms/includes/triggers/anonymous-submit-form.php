<?php
/**
 * Submit Form
 *
 * @package     AutomatorWP\Integrations\Caldera_Forms\Triggers\Anonymous_Submit_Form
 * @author      AutomatorWP <contact@automatorwp.com>, Ruben Garcia <rubengcdev@gmail.com>
 * @since       1.0.0
 */
// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

class AutomatorWP_Caldera_Forms_Anonymous_Submit_Form extends AutomatorWP_Integration_Trigger {

    public $integration = 'caldera_forms';
    public $trigger = 'caldera_forms_anonymous_submit_form';

    /**
     * Register the trigger
     *
     * @since 1.0.0
     */
    public function register() {

        automatorwp_register_trigger( $this->trigger, array(
            'integration'       => $this->integration,
            'anonymous'         => true,
            'label'             => __( 'Guest submits a form', 'automatorwp' ),
            'select_option'     => __( 'Guest submits <strong>a form</strong>', 'automatorwp' ),
            /* translators: %1$s: Post title. %2$s: Number of times. */
            'edit_label'        => sprintf( __( 'Guest submits %1$s', 'automatorwp' ), '{post}' ),
            /* translators: %1$s: Post title. */
            'log_label'         => sprintf( __( 'Guest submits %1$s', 'automatorwp' ), '{post}' ),
            'action'            => 'caldera_forms_submit_post_process',
            'function'          => array( $this, 'listener' ),
            'priority'          => 10,
            'accepted_args'     => 4,
            'options'           => array(
                'post' => automatorwp_utilities_ajax_selector_option( array(
                    'field'             => 'post',
                    'name'              => __( 'Form:', 'automatorwp' ),
                    'option_none_value' => 'any',
                    'option_none_label' => __( 'any form', 'automatorwp' ),
                    'action_cb'         => 'automatorwp_caldera_forms_get_forms',
                    'options_cb'        => 'automatorwp_caldera_forms_options_cb_form',
                    'default'           => 'any'
                ) ),
                'times' => automatorwp_utilities_times_option(),
            ),
            'tags' => array_merge(
                array(
                    'form_field:FIELD_NAME' => array(
                        'label'     => __( 'Form field value', 'automatorwp' ),
                        'type'      => 'text',
                        'preview'   => __( 'Form field value, replace "FIELD_NAME" by the field name', 'automatorwp' ),
                    ),
                ),
                automatorwp_utilities_times_tag()
            )
        ) );

    }

    /**
     * Trigger listener
     *
     * @since 1.0.0
     *
     * @param $form
     * @param $referrer
     * @param $process_id
     * @param $entry_id
     */
    public function listener( $form, $referrer, $process_id, $entry_id ) {

        // Bail if user is logged in
        if ( is_user_logged_in() ) {
            return;
        }

        $form_id = $form['ID']; // Caldera Forms ID can be strings
        $form_fields = automatorwp_caldera_forms_get_form_fields_values( $form, $entry_id );

        // Trigger submit form event
        automatorwp_trigger_event( array(
            'trigger'       => $this->trigger,
            'form_id'       => $form_id,
            'form_fields'   => $form_fields,
        ) );

    }

    /**
     * Anonymous deserves check
     *
     * @since 1.0.0
     *
     * @param bool      $deserves_trigger   True if anonymous deserves trigger, false otherwise
     * @param stdClass  $trigger            The trigger object
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return bool                         True if anonymous deserves trigger, false otherwise
     */
    public function anonymous_deserves_trigger( $deserves_trigger, $trigger, $event, $trigger_options, $automation ) {

        // Don't deserve if post is not received
        if( ! isset( $event['form_id'] ) ) {
            return false;
        }

        // Bail if post doesn't match with the trigger option
        if( $trigger_options['post'] !== 'any' && $event['form_id'] !== $trigger_options['post'] ) {
            return false;
        }

        return $deserves_trigger;

    }

    /**
     * Register the required hooks
     *
     * @since 1.0.0
     */
    public function hooks() {

        // Log meta data
        add_filter( 'automatorwp_user_completed_trigger_log_meta', array( $this, 'log_meta' ), 10, 6 );

        // Log fields
        add_filter( 'automatorwp_log_fields', array( $this, 'log_fields' ), 10, 5 );

        parent::hooks();
    }

    /**
     * Trigger custom log meta
     *
     * @since 1.0.0
     *
     * @param array     $log_meta           Log meta data
     * @param stdClass  $trigger            The trigger object
     * @param int       $user_id            The user ID
     * @param array     $event              Event information
     * @param array     $trigger_options    The trigger's stored options
     * @param stdClass  $automation         The trigger's automation object
     *
     * @return array
     */
    function log_meta( $log_meta, $trigger, $user_id, $event, $trigger_options, $automation ) {

        // Bail if action type don't match this action
        if( $trigger->type !== $this->trigger ) {
            return $log_meta;
        }

        $log_meta['form_fields'] = ( isset( $event['form_fields'] ) ? $event['form_fields'] : array() );

        return $log_meta;

    }

    /**
     * Action custom log fields
     *
     * @since 1.0.0
     *
     * @param array     $log_fields The log fields
     * @param stdClass  $log        The log object
     * @param stdClass  $object     The trigger/action/automation object attached to the log
     *
     * @return array
     */
    public function log_fields( $log_fields, $log, $object ) {

        // Bail if log is not assigned to an trigger
        if( $log->type !== 'trigger' ) {
            return $log_fields;
        }

        // Bail if trigger type don't match this trigger
        if( $object->type !== $this->trigger ) {
            return $log_fields;
        }

        $log_fields['form_fields'] = array(
            'name' => __( 'Fields Submitted', 'automatorwp' ),
            'desc' => __( 'Information about the fields values sent on this form submission.', 'automatorwp' ),
            'type' => 'text',
        );

        return $log_fields;

    }

}

new AutomatorWP_Caldera_Forms_Anonymous_Submit_Form();