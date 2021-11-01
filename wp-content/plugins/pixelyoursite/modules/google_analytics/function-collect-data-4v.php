<?php
use PixelYourSite\GA\Helpers;

function getSearchEventDataV4() {

    if ( ! PixelYourSite\GA()->getOption( 'search_event_enabled' ) ) {
        return false;
    }

    $event_category = 'WordPress';
    $search_term    = empty( $_GET['s'] ) ? null : $_GET['s'];

    if ( PixelYourSite\isWooCommerceActive() && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product' ) {
        $event_category = 'WooCommerce';
    }

    if ( PixelYourSite\isEddActive() && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'download' ) {
        $event_category = 'Easy Digital Downloads';
    }

    $user = wp_get_current_user();
    if ( $user->ID !== 0 ) {
        $user_roles = implode( ',', $user->roles );
    } else {
        $user_roles = 'guest';
    }

    $params['event_category'] = $event_category;
    $params['search_term']    = $search_term;
    $params['non_interaction'] = PixelYourSite\GA()->getOption( 'search_event_non_interactive' );
    //$params['post_id'] = ;
    $params['post_type'] = "page";
    $params['content_name'] = wp_get_document_title();
    $params['user_role'] = $user_roles;

    return array(
        'name'  => 'search',
        'data'  => $params,
    );

}

function getCompleteRegistrationEventParamsV4() {
    if ( ! PixelYourSite\GA()->getOption( 'complete_registration_event_enabled' ) ) {
        return false;
    }

    return array(
        'name' => 'sign_up',
        'data' => array(
            'content_name'    => get_the_title(),
            'event_url'       => \PixelYourSite\getCurrentPageUrl(true),
            'method'          => \PixelYourSite\getUserRoles(),
            'non_interaction' => PixelYourSite\GA()->getOption( 'complete_registration_event_non_interactive' ),
        ),
    );
}