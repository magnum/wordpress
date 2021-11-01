<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}


class AjaxHookEventManager {

    public static $pendingEvents = array();
    public static $DIV_ID_FOR_AJAX_EVENTS = "pys_ajax_events";
    private static $_instance;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    public function __construct() {

    }

    public function addHooks() {


        if(EventsWoo()->isEnabled()) {

            // use for fb server only because ajax request cause bugs in woo
            if(Facebook()->enabled()
                && Facebook()->isServerApiEnabled()
                && PYS()->getOption( 'woo_remove_from_cart_enabled')
            ) {
                add_action( 'woocommerce_remove_cart_item', array($this, 'trackRemoveFromCartEvent'), 10, 2);
            }

            if ( isEventEnabled('woo_add_to_cart_enabled')
                && PYS()->getOption('woo_add_to_cart_on_button_click')
            )
            {

                if(PYS()->getOption('woo_add_to_cart_catch_method') == "add_cart_hook") {
                    add_action( 'wp_footer', array( __CLASS__, 'addDivForAjaxPixelEvent')  );
                    add_action( 'woocommerce_add_to_cart',array(__CLASS__, 'trackWooAddToCartEvent'),40, 6);
                } else {

                    if(Facebook()->enabled() && Facebook()->isServerApiEnabled()) {
                        add_action( 'woocommerce_add_to_cart',array(__CLASS__, 'trackWooFacebookAddToCartEvent'),40, 6);
                    }

                }

            }
        }



    }

    /**
     * @param String $cart_item_key
     * @param \WC_Cart $cart
     */

    function trackRemoveFromCartEvent ($cart_item_key,$cart) {
        $eventId = 'woo_remove_from_cart';

        $url = $_SERVER['HTTP_HOST'].strtok($_SERVER["REQUEST_URI"], '?');
        $postId = url_to_postid($url);
        $cart_id = wc_get_page_id( 'cart' );
        $item = $cart->get_cart_item($cart_item_key);
        $is_ajax_request = wp_doing_ajax();
        if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'yith_wacp_add_item_cart') {
            $is_ajax_request = true;
        }

        if(isset($item['variation_id'])) {
            $product_id = $item['variation_id'];
        } else {
            $product_id = $item['product_id'];
        }


        if( $cart_id==$postId) {
            PYS()->getLog()->debug('trackRemoveFromCartEvent send fb server with out browser event');
            $event = new SingleEvent("woo_remove_from_cart",EventTypes::$STATIC);
            $eventData = Facebook()->getEventData($eventId,$item);
            $event->addParams($eventData['data']);
            $event->addParams(getStandardParams());
            if(isset($_COOKIE['pys_landing_page'])){
                $event->addParams(['landing_page'=>$_COOKIE['pys_landing_page']]);
            }

            unset($eventData['data']);
            $event->addPayload($eventData);

            if(isset($_COOKIE["pys_fb_event_id"])) {
                $eventID = json_decode(stripslashes($_COOKIE["pys_fb_event_id"]))->RemoveFromCart;
            } else {
                $eventID = (new EventIdGenerator())->guidv4();
            }

            $data = $event->getData();

            $data = EventsManager::filterEventParams($data,"woo",[
                'event_id'=>$event->getId(),
                'pixel'=>Facebook()->getSlug(),
                'product_id'=>$product_id
            ]);

            $serverEvent = FacebookServer()->createEvent($eventID,$data['name'],$data['params']);
            $ids = (array)$event->payload["pixelIds"];

            if($is_ajax_request) {
                FacebookServer()->sendEvent($ids,array($serverEvent));
            } else {
                FacebookServer()->addAsyncEvents(array(array("pixelIds" => $ids, "event" => $serverEvent )));
            }
        }
    }

    static function trackWooFacebookAddToCartEvent($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        if(isset($cart_item_data['woosb_parent_id'])) return; // fix for WPC Product Bundles for WooCommerce (Premium) product

        $is_ajax_request = wp_doing_ajax();
        if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'yith_wacp_add_item_cart') {
            $is_ajax_request = true;
        }
        PYS()->getLog()->debug('trackWooFacebookAddToCartEvent is_ajax_request '.$is_ajax_request);
        if( !empty($variation_id)
            && $variation_id > 0
            && ( !Facebook()->getOption( 'woo_variable_as_simple' )
                ||  !Facebook\Helpers\isDefaultWooContentIdLogic() )
        ) {
            $_product_id = $variation_id;
        } else {
            $_product_id = $product_id;
        }

        $event = EventsWoo()->getEvent("woo_add_to_cart_on_button_click");
        if(is_array($event) && count($event) > 0) {
            $event = $event[0];
        }
        $event->args = ['productId' => $_product_id,'quantity' => $quantity];
        $isSuccess = Facebook()->addParamsToEvent( $event );
        if ( !$isSuccess ) {
            return; // event is disabled or not supported for the pixel
        }

        // prepare event data
        if(isset($_COOKIE['pys_landing_page']))
            $event->addParams(['landing_page'=>$_COOKIE['pys_landing_page']]);
        $eventData = $event->getData();
        $eventData = EventsManager::filterEventParams($eventData,"woo",[
            'event_id'=>$event->getId(),
            'pixel'=>Facebook()->getSlug(),
            'product_id'=>$product_id
        ]);

        if(isset($_COOKIE["pys_fb_event_id"])) {
            $eventID = json_decode(stripslashes($_COOKIE["pys_fb_event_id"]))->AddToCart;
        } else {
            return; // not send if event id is empty
        }

        $ids = (array)$event->payload["pixelIds"];
        $serverEvent = FacebookServer()->createEvent($eventID,$eventData['name'],$eventData['params']);

        if($is_ajax_request) {
            FacebookServer()->sendEvent($ids,array($serverEvent));
        } else {
            FacebookServer()->addAsyncEvents(array(array("pixelIds" => $ids, "event" => $serverEvent )));
        }
    }


    static function trackWooAddToCartEvent($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {

        if(isset($cart_item_data['woosb_parent_id'])) return; // fix for WPC Product Bundles for WooCommerce (Premium) product

        $is_ajax_request = wp_doing_ajax();
        if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'yith_wacp_add_item_cart') {
            $is_ajax_request = true;
        }
        $standardParams = getStandardParams();

        PYS()->getLog()->debug('trackWooAddToCartEvent is_ajax_request '.$is_ajax_request);

        foreach ( PYS()->getRegisteredPixels() as $pixel ) {

            if( !empty($variation_id)
                && $variation_id > 0
                && ( !$pixel->getOption( 'woo_variable_as_simple' )
                    || ( $pixel->getSlug() == "facebook"
                        && !Facebook\Helpers\isDefaultWooContentIdLogic()
                    )
                )
            ) {
                $_product_id = $variation_id;
            } else {
                $_product_id = $product_id;
            }


            $event = new SingleEvent('woo_add_to_cart_on_button_click',EventTypes::$STATIC);
            $event->args = ['productId' => $_product_id,'quantity' => $quantity];
            $isSuccess = $pixel->addParamsToEvent( $event );
            if ( !$isSuccess ) {
                continue; // event is disabled or not supported for the pixel
            }

            if(count($event->params) == 0) {
                // add product params
                // use for not update bing and pinterest, need remove in next updates
                $eventData = $pixel->getEventData('woo_add_to_cart_on_button_click',$product_id);
                if($eventData) {
                    $event->addParams($eventData['params']);
                    unset($eventData['params']);
                    $event->addPayload($eventData);
                }
            }



            // add standard params
            if($pixel->getSlug() != "ga" || $pixel->isUse4Version()) {
                $event->addParams($standardParams);
            }

            // prepare event data
            $eventData = $event->getData();
            $eventData = EventsManager::filterEventParams($eventData,"woo",[
                'event_id'=>$event->getId(),
                'pixel'=>$pixel->getSlug(),
                'product_id'=>$product_id
            ]);

            AjaxHookEventManager::$pendingEvents["woo_add_to_cart_on_button_click"][ $pixel->getSlug() ] = $eventData;

            if($pixel->getSlug() == "facebook" && Facebook()->isServerApiEnabled()) {
                $name = $eventData['name'];
                $data = $eventData['params'];
                $eventID = isset($eventData['eventID']) ? $eventData['eventID'] : false;
                $ids = (array)$event->payload["pixelIds"];
                $event = FacebookServer()->createEvent($eventID,$name,$data);

                if($is_ajax_request) {
                    FacebookServer()->sendEvent($ids,array($event));
                } else {
                    FacebookServer()->addAsyncEvents(array(array("pixelIds" => $ids, "event" => $event )));
                }
            }
        }

        if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
            add_filter('wc_add_to_cart_message_html',array(__CLASS__, 'addPixelCodeToAddToCarMessage'),90,3);
        } elseif ($is_ajax_request) {
            add_filter('woocommerce_add_to_cart_fragments', array(__CLASS__, 'addPixelCodeToAddToCartFragment'));
        } else {
            add_action("wp_footer",array(__CLASS__, 'printEvent'));
            // self::printEvent();
        }

    }

    public static function printEvent() {
        $pixelsEventData = self::$pendingEvents["woo_add_to_cart_on_button_click"];
        if( !is_null($pixelsEventData) ) {
            PYS()->getLog()->debug('trackWooAddToCartEvent printEvent is footer');
            echo "<div id='pys_late_event' style='display:none' dir='".json_encode($pixelsEventData)."'></div>";
            unset(self::$pendingEvents["woo_add_to_cart_on_button_click"]);
        }
    }

    public  static function addDivForAjaxPixelEvent(){

        echo self::getDivForAjaxPixelEvent();
        ?>
        <script>
            var node = document.getElementsByClassName('woocommerce-message')[0];
            if(node && document.getElementById('pys_late_event')) {
                var messageText = node.textContent.trim();
                if(!messageText) {
                    node.style.display = 'none';
                }
            }
        </script>
        <?php
    }

    public  static function getDivForAjaxPixelEvent($content = ''){
        return "<div id='".self::$DIV_ID_FOR_AJAX_EVENTS."'>" . $content . "</div>";
    }

    public static function addPixelCodeToAddToCarMessage($message, $products, $show_qty) {
        $pixelsEventData = self::$pendingEvents["woo_add_to_cart_on_button_click"];
        if( !is_null($pixelsEventData) ){
            $message .= "<div id='pys_late_event' dir='".json_encode($pixelsEventData)."'></div>";
            unset(self::$pendingEvents["woo_add_to_cart_on_button_click"]);
        }
        return $message;
    }

    public static function addPixelCodeToAddToCartFragment($fragments) {


        $pixelsEventData = self::$pendingEvents["woo_add_to_cart_on_button_click"];
        if( !is_null($pixelsEventData) ){
            PYS()->getLog()->debug('addPixelCodeToAddToCartFragment send data with fragment');
            $pixel_code = self::generatePixelCode($pixelsEventData);
            $fragments['#'.self::$DIV_ID_FOR_AJAX_EVENTS] =
                self::getDivForAjaxPixelEvent($pixel_code);
            unset(self::$pendingEvents["woo_add_to_cart_on_button_click"]);
        }

        return $fragments;
    }

    public static function generatePixelCode($pixelsEventData){

        ob_start();
        //$cartHashKey = apply_filters( 'woocommerce_cart_hash_key', 'wc_cart_hash_' . md5( get_current_blog_id() . '_' . get_site_url( get_current_blog_id(), '/' ) . get_template() ) );
        ?>
        <script>
            function pys_getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? v[2] : null;
            }
            function pys_setCookie(name, value, days) {
                var d = new Date;
                d.setTime(d.getTime() + 24*60*60*1000*days);
                document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString();
            }
            var name = 'pysAddToCartFragmentId';
            var cartHash = "<?=WC()->cart->get_cart_hash()?>";

            if(pys_getCookie(name) != cartHash) { // prevent re send event if user update page
                <?php foreach ($pixelsEventData as $slug => $eventData) : ?>

                var pixel = getPixelBySlag('<?=$slug?>');
                var event = <?=json_encode($eventData)?>;
                pixel.fireEvent(event.name, event);

                <?php  endforeach; ?>
                pys_setCookie(name,cartHash,90)
            }
        </script>
        <?php

        $code = ob_get_clean();
        return $code;
    }




}
