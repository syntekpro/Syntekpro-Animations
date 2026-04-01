<?php
defined( 'ABSPATH' ) || exit;

class SPSLIDER_Deactivator {
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
