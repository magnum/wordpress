<?php

include_once 'Utilities.php';
require_once dirname(__FILE__) . '/includes/lib/mo-saml-options-enum.php';

class MoSAMLLogger
{
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    const ERROR = 'ERROR';
    const Critical ="CRITICAL";
    private $log_file_writable = FALSE;

	/**
	 * @return bool
	 */
	public function is_log_file_writable() {
		return $this->log_file_writable;
	}
    private $plugin_directory;
    private $wp_config_editor;
	protected $cached_logs = array();


	public function __construct()
    {

        //For setting up debug directory for log files
	    add_action( 'plugins_loaded', array( $this, 'write_cached_logs' ) );
	    $upload_dir = wp_upload_dir( null, false );
	    $this->define('MO_SAML_DIRC',$upload_dir['basedir'] . '/mo-saml-logs/');

        // Debug directory for log files
        //if directory doesn't exist then create
        if(is_writable($upload_dir['basedir'])){
	            $this->log_file_writable = TRUE;
                if (!is_dir(MO_SAML_DIRC))
                    self::create_files();
	        }
    }

    /**
     * Add a log entry along with the log level
     *
     * @param string $log_message
     * @param string $log_level
     */

    public function add_log($log_message = "", $log_level = self::INFO)
    {
	    $e = new Exception();
	    $trace = $e->getTrace();
	    //position 0 would be the line that called this function so we ignore it
	    $last_call = $trace[1];
	    //if log is off then we will not add log messages
	    $message = "";
	    // for adding date and time of log message

	    $message = $message . date("Y-m-d") . "" . date("h:i:sa")." UTC " ."{$log_level}";
	    $message = $message . ' : ' . $last_call['file'] . ' : ' . $last_call['function'] . ' : ' . $last_call['line'];
	    $message =  $message.' '. str_replace(array ( "\r", "\n", "\t"), '', rtrim($log_message))  . PHP_EOL;
	    $message = PHP_EOL.preg_replace("/[,]/", "\n",$message);

        /*if(!$this->is_debugging_enabled())
            if(defined(ABSPATH))
                return;
            else{
                $this->cache_log($message);
            }
			*/

	    if(! MoSAMLLogger::is_debugging_enabled() )
	        return;
		if($this->log_file_writable) {
			$log_file = @fopen( self::get_log_file_path( 'mo_saml' ), "a+" );
			if ( $log_file ) {
				@fwrite( $log_file, $message );
			}
			fclose( $log_file );
		}
		}

	/**
	 * Cache log to write later.
	 *
	 * @param string $entry Log entry text.
	 * @param string $handle Log entry handle.
	 */
	protected function cache_log( $entry ) {
		$this->cached_logs[] = array(
			'entry'  => $entry,
			'handle' => 'mo_saml',
		);
	}

	/**
	 * Write cached logs.
	 */
	public function write_cached_logs() {
		foreach ( $this->cached_logs as $log ) {
			$this->add( $log['entry'], $log['handle'] );
		}
	}

    /**
     *  Logs critical errors
     */
    function log_critical_errors(){
	    $error = error_get_last();
	    if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
		    $this->add_log(
		    /* translators: 1: error message 2: file name and path 3: line number */
			    sprintf( __( '%1$s in %2$s on line %3$s', 'mo' ), $error['message'], $error['file'], $error['line'] ) . PHP_EOL, self::Critical );
		    do_action( 'miniOrange-down-error', $error );
	    }
    }

	/**
	 * Get all log files in the log directory.
	 *
	 * @since 4.9.0
	 * @return array
	 */
	public static function get_log_files() {
		$files  = @scandir( MO_SAML_DIRC );
		$result = array();
		if ( ! empty( $files ) ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( '.', '..' ), true ) ) {
					if ( ! is_dir( $value ) && strstr( $value, '.log' ) ) {
						$result[ sanitize_title( $value ) ] = $value;
					}
				}
			}
		}

		return $result;
	}
	/**
     * Deletes all the files in the Log directory older than 7 Days
     */

	public static function delete_logs_before_timestamp( $timestamp = 0 ) {
		if ( ! $timestamp ) {
			return;
		}
		$log_files = self::get_log_files();
		foreach ( $log_files as $log_file ) {
			$last_modified = filemtime( trailingslashit( MO_SAML_DIRC ) . $log_file );
			if ( $last_modified < $timestamp ) {
				@unlink( trailingslashit( MO_SAML_DIRC ) . $log_file ); // @codingStandardsIgnoreLine.
			}
		}
	}
	/**
	* Get the file path of current log file used by plugins
     */
	public static function get_log_file_path( $handle ) {
		if ( function_exists( 'wp_hash' ) ) {
			return trailingslashit( MO_SAML_DIRC) . self::get_log_file_name( $handle );
		} else {
			return false;
		}
	}
	/**
     * To get the log for based on the time
     */

	public static function get_log_file_name( $handle ) {
		if ( function_exists( 'wp_hash' ) ) {
			$date_suffix = date( 'Y-m-d', time() );
			$hash_suffix = wp_hash( $handle );
			return sanitize_file_name( implode( '-', array( $handle, $date_suffix, $hash_suffix ) ) . '.log' );
		} else {
			_doing_it_wrong( __METHOD__, __( 'This method should not be called before plugins_loaded.', 'miniorange' ), mo_saml_options_plugin_constants::Version );
			return false;
		}
	}


	/**
	 * Used to show the UI part of the log feature to user screen.
	 */
	static function mo_saml_log_page() {

	    $debug_transaction = MoSAMLLogger::is_debug_transaction();
        $debugging_enabled = MoSAMLLogger::is_debugging_enabled();
	    if($debug_transaction){
            $debugging_enabled = MO_SAML_DEBUG_TRANSACTION;
        }
        if((isset($_REQUEST['page']) && $_REQUEST['page'] === 'mo_saml_enable_debug_logs')){
            echo '<style>
                
                .miniorange_debug_container{
                    width: 55%;
                }
                
                .mo_back_to_home{
                    display: block;
                    padding: 5px;
                    color: #3c434a;
                    border: 1px solid #2f6062;
                    margin-bottom: 10px;
                    width: 235px;
                    background: #f6f7f7;
                    border-radius: 2px;
                }
                
                .mo_back_to_home:hover{
                    color: slategray;
                }
                
                .mo_back_to_home a{
                    font-size: 16px;
                    color: #000;
                    text-decoration: none;
                }
                
                .mo_back_to_home a:hover{
                    color: slategray;
                }
                
                .notice p,div.updated p,div.error p{
                margin: 5px !important;
                }
                .notice,div.updated,div.error {
	                margin: 5px 15px 0px 2px !important;
}

                .mo_saml_logs{
                text-align:left;
                margin-left: 50px;
    
                }
                .mo_saml_support_layout{
                width: auto;
                margin-right: 1rem;
                margin-bottom: ;                }
            </style>
            <div class = "wrap">
                <h1>';?>
                  <?php _e('miniOrange SSO using SAML 2.0','miniorange-saml-20-single-sign-on');?>&nbsp
                <a id="license_upgrade" class="add-new-h2 add-new-hover" style="background-color: orange !important; border-color: orange; font-size: 16px; color: #000;" href="<?php echo add_query_arg( array( 'tab' => 'licensing' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>"><?php _e('Premium Plans | Upgrade Now','miniorange-saml-20-single-sign-on'); ?></a>
                <a class="add-new-h2" href="https://faq.miniorange.com/kb/saml-single-sign-on/" target="_blank"><?php _e('FAQs','miniorange-saml-20-single-sign-on');?></a>
                <a class="add-new-h2" href="https://forum.miniorange.com/" target="_blank"><?php _e('Ask questions on our forum','miniorange-saml-20-single-sign-on');?></a>
                </h1>
                </div> <?php
        }
        else{
	        echo '<style>
                .mo_saml_logs{
                    text-align:center;
                }
                .mo_back_to_home{
                    display: none;
                }
            </style>';        }
		?>

    <div class="miniorange_debug_container" style="margin-top: 1rem;">
        <div class="mo_back_to_home">
            <span class="dashicons dashicons-arrow-left-alt" style="vertical-align: bottom;"></span>
            <a href="<?php echo mo_saml_add_query_arg( array( 'tab' => 'save' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>">Back to Plugin Configurations</a>
        </div>
        <div class="mo_saml_support_layout" id="container">
            <form action="" method="post" id="mo_saml_logger">
                <?php wp_nonce_field( 'mo_saml_logger' ); ?>
                <input type="hidden" name="option" value="mo_saml_logger"/>
                <h1>SAML Debug Tools</h1>
                <hr>
                <p>If you are facing any issues with the SSO, please follow these steps for easier debugging.</p>
                <ul>
                    <li>
                        <h3 style="line-height: 1.5;"><b>Step 1: </b>Enable the Debug Logs option below and reproduce the issue</h3>
                    </li>
                    <li>
                        <label class="switch">
                            <input type="checkbox" id="mo_saml_enable_debug_logs" name="mo_saml_enable_debug_logs"
                                   value="true" onchange="submit();"
                                <?php
                                    if ($debugging_enabled)
                                        echo ' checked ';
                                    ?>/>
                            <span class="slider round"></span>
                        </label>
                        <span style="padding-left:5px;">
                            <b>Enable miniOrange SAML Debug Logs</b>
                            <div><span></span></div>
                        </span>
                    </li>
                    <div class="mo_saml_logs">
                        <input type="submit" class="button button-primary button-large saml-debug-button" name="clear" value="Clear Log Files"
                        <?php
	                    if (!$debugging_enabled)
		                    echo ' disabled ';
	                    ?>
                        >
                    </div>
                    <li>
                        <div class="call-setup-div">
                            <p class="call-setup-heading"><strong><font color="#dc143c">Note: </font><u>If your wp-config.php is not writable</u>, follow the steps below to Enable debug logs Manually</strong></p>
                            <ul>
                                <li style="line-height: 1.75;">
                                    Copy this code <code id="popovercode" data-toggle="popover"  data-html="true" data-trigger="hover" data-content="To disable miniOrange SAML logs simply delete this line from the <a href='https://wordpress.org/support/article/editing-wp-config-php/'>wp-config.php</a> file ">define('MO_SAML_LOGGING', true);</code>
                                    and paste it in the <a href="https://wordpress.org/support/article/editing-wp-config-php/">wp-config.php</a> file before the line
                                    <br> <code>/* That's all, stop editing! Happy publishing. */</code> to enable the miniOrange SAML logs.
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
                <h3 style="line-height: 1.5;"><b>Step 2: </b> Download the Debug Log File and Plugin Configurations</h3>

                <div class="mo_saml_logs">
                    <input type="submit" class="button button-primary button-large saml-debug-button" name="download" value="Download Debug Logs"
                    <?php
                    if (!$debugging_enabled)
	                    echo ' disabled ';
                    ?>
                    >
                </div>
            </form>
            <h4>Send this file to us at <a style="color:#dc143c" href="mailto:samlsupport@xecurify.com">samlsupport@xecurify.com</a></h4>
        </div>
        <br><br>
    </div>
            <?php

	}
    /**
     * Creates files Index.html for directory listing
     * and local .htaccess rule to avoid hotlinking
    */
	private static function create_files() {

		$upload_dir      = wp_get_upload_dir();

		$files = array(

			array(
				'base'    => MO_SAML_DIRC,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
			array(
				'base'    => MO_SAML_DIRC,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'wb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}
		}

	}
	/**
	 * Check if a constant is defined if not define a cosnt
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * To check if Debug constant is defined and logs are enabled
	 * @return bool
	 */
	static function is_debugging_enabled() {
		if ( ! defined( 'MO_SAML_LOGGING' ) ) {
			return false;
		} else {
			return MO_SAML_LOGGING;
		}
	}

	static function is_debug_transaction(){
	    if(defined('MO_SAML_DEBUG_TRANSACTION')){
	        return true;
        }else{
	        return false;
        }
    }
}