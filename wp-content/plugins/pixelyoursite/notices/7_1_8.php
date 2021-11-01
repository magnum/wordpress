<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function adminGetPromoNoticesContent() {
    return [
        'woo' => [

          [
              'disabled' => false,
            //  'from'     => 1,
            //  'to'       => 2,
              'content'  => '</br>PIXELYOURSITE KEY INFO:</br></br>Learn about Facebook Aggregated Event Measurement: <a href="https://www.pixelyoursite.com/aem" target="_blank">Click here</a>
</br></br>
How to verify your domanin on Facebook: <a href="https://www.pixelyoursite.com/verify-domain-facebook" target="_blank">Click here</a>
</br></br>
Find out about Custom Conversion using the Signal event: <a href="https://www.pixelyoursite.com/signal-custom-conversions-aem" target="_blank">Click here</a></br></br>'
          ],


        ],
        'edd' => [

          [
              'disabled' => false,
            //  'from'     => 0,
            //  'to'       => 1,
             'content'  => '</br>PIXELYOURSITE KEY INFO:</br></br>Learn about Facebook Aggregated Event Measurement: <a href="https://www.pixelyoursite.com/aem" target="_blank">Click here</a>
</br></br>
How to verify your domani on Facebook: <a href="https://www.pixelyoursite.com/verify-domain-facebook" target="_blank">Click here</a>
</br></br>
Find out about Custom Conversion using the Signal event: <a href="https://www.pixelyoursite.com/signal-custom-conversions-aem" target="_blank">Click here</a></br></br>'
          ],

        ],
        'no_woo_no_edd' => [

          [
              'disabled' => false,
          //    'from'     => 0,
          //    'to'       => 1,
              'content'  => '</br>PIXELYOURSITE KEY INFO:</br></br>Learn about Facebook Aggregated Event Measurement: <a href="https://www.pixelyoursite.com/aem" target="_blank">Click here</a>
</br></br>
How to verify your domani on Facebook: <a href="https://www.pixelyoursite.com/verify-domain-facebook" target="_blank">Click here</a>
</br></br>
Find out about Custom Conversion using the Signal event: <a href="https://www.pixelyoursite.com/signal-custom-conversions-aem" target="_blank">Click here</a></br></br>'
          ],

        ],
    ];
}
