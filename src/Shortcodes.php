<?php

namespace SUL;
//Prevent direct requests
defined( 'ABSPATH' ) || exit;

/**
 * Contains functions for rendering shortcodes.
 * 
 * Public methods are statically invoked.
 */
class Shortcodes {

    /**
     * Renders entries by re-using corresponding php-template for dynamic Gutenberg block.
     * 
     * This function captures the rendering output in a buffer and returns this buffer as a string.
     * 
     * @param boolean $include_style Whether styling must be included in the output. Default true.
     * @return string $html The HTML-output as captured in the output buffer,
     */
    private static function render_entries( $include_style = true ) {
        $html = '';
        ob_start();
        if ( $include_style ) {
            echo '<style>';
            include SUL_DIR . 'blocks/build/style-sul-entries.css';
            echo '</style>';
        }
        require_once SUL_DIR . 'blocks/build/sul-entries/render.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Wrapper for render entries, called to render no style
     * 
     * @return string $html The HTML-output as captured in the output buffer.
     */
    public static function render_entries_without_style() {
        return self::render_entries( false );
    }

    /**
     * Wrapper for render entries, called to render with style
     * 
     * @return string $html The HTML-output as captured in the output buffer.
     */
    public static function render_entries_with_style() {
        return self::render_entries( true );
    }
    
    /**
     * Renders sign-up form by re-using corresponding php-template for dynamic Gutenberg block.
     * 
     * This function captures the rendering output in a buffer and returns this buffer as a string.
     * 
     * @param boolean $include_style Whether styling must be included in the output. Default true. 
     * @return string $html The HTML-output as captured in the output buffer.
     */
    private static function render_sign_up( $include_style = true ) {
        $html = '';
        ob_start();
        if ( $include_style ) {
            echo '<style>';
            include SUL_DIR . 'blocks/build/style-sul-sign-up.css';
            echo '</style>';
        }
        require_once SUL_DIR . 'blocks/build/sul-sign-up/render.php';
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Wrapper for render sign-up, called to render no style
     * 
     * @return string $html The HTML-output as captured in the output buffer.
     */
    public static function render_sign_up_without_style() {
        return self::render_sign_up( false );
    }

    /**
     * Wrapper for render sign-up, called to render with  style
     * 
     * @return string $html The HTML-output as captured in the output buffer.
     */
    public static function render_sign_up_with_style() {
        return self::render_sign_up( true );
    }

}
?>