<?php
/**
 * In-Plugin Help System
 * Contextual help and tooltips for users
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Help_System {
    
    public function __construct() {
        add_action('admin_footer', array($this, 'add_help_widget'));
        add_action('wp_ajax_syntekpro_get_help', array($this, 'ajax_get_help'));
    }
    
    /**
     * Add floating help widget to admin pages
     */
    public function add_help_widget() {
        // Only show on Syntekpro pages
        if (!isset($_GET['page']) || strpos($_GET['page'], 'syntekpro-animations') === false) {
            return;
        }
        ?>
        <div id="syntekpro-help-widget" class="syntekpro-help-widget">
            <button class="help-widget-toggle" onclick="toggleHelpWidget()">
                <span class="help-icon">?</span>
            </button>
            <div class="help-widget-content">
                <div class="help-header">
                    <h3>📚 Quick Help</h3>
                    <button class="close-help" onclick="toggleHelpWidget()">×</button>
                </div>
                <div class="help-body">
                    <div class="help-section">
                        <h4>🚀 Getting Started</h4>
                        <ul>
                            <li><a href="?page=syntekpro-animations-builder">Animation Builder</a> - Create custom animations</li>
                            <li><a href="?page=syntekpro-animations-presets">Animation Presets</a> - Browse all effects</li>
                            <li><a href="?page=syntekpro-animations-docs">Full Documentation</a> - Complete guides</li>
                        </ul>
                    </div>
                    
                    <div class="help-section">
                        <h4>💡 Quick Tips</h4>
                        <div class="help-tip">
                            <strong>Shortcode Format:</strong>
                            <code>[sp_animate type="fadeIn"]Content[/sp_animate]</code>
                        </div>
                        <div class="help-tip">
                            <strong>Timeline Animation:</strong>
                            <code>[sp_timeline][sp_animate...]...[/sp_timeline]</code>
                        </div>
                        <div class="help-tip">
                            <strong>Hover Effects:</strong>
                            <code>[sp_hover_effect type="scale"]Content[/sp_hover_effect]</code>
                        </div>
                    </div>
                    
                    <div class="help-section">
                        <h4>🎨 Popular Animations</h4>
                        <div class="help-animations-grid">
                            <span class="help-anim-tag">fadeIn</span>
                            <span class="help-anim-tag">fadeInUp</span>
                            <span class="help-anim-tag">slideLeft</span>
                            <span class="help-anim-tag">zoomIn</span>
                            <span class="help-anim-tag">rotateIn</span>
                            <span class="help-anim-tag">bounceIn</span>
                        </div>
                    </div>
                    
                    <div class="help-section">
                        <h4>🆘 Need Support?</h4>
                        <p><a href="https://syntekpro.com/support" target="_blank" class="help-button">Contact Support →</a></p>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .syntekpro-help-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 999999;
        }
        
        .help-widget-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .help-widget-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(229, 57, 53, 0.6);
        }
        
        .help-icon {
            color: white;
            font-size: 28px;
            font-weight: bold;
            line-height: 1;
        }
        
        .help-widget-content {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 400px;
            max-height: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
            display: none;
            overflow: hidden;
        }
        
        .syntekpro-help-widget.active .help-widget-content {
            display: block;
            animation: slideInUp 0.3s ease;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .help-header {
            background: linear-gradient(135deg, #e53935 0%, #c62828 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .help-header h3 {
            margin: 0;
            font-size: 18px;
            color: white;
        }
        
        .close-help {
            background: transparent;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }
        
        .help-body {
            padding: 20px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .help-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .help-section:last-child {
            border-bottom: none;
        }
        
        .help-section h4 {
            margin: 0 0 15px 0;
            color: #1565c0;
            font-size: 16px;
        }
        
        .help-section ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .help-section li {
            margin-bottom: 8px;
        }
        
        .help-section a {
            color: #1565c0;
            text-decoration: none;
        }
        
        .help-section a:hover {
            text-decoration: underline;
        }
        
        .help-tip {
            background: #f5f5f5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        
        .help-tip strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        
        .help-tip code {
            display: block;
            background: #2d2d2d;
            color: #a5d6ff;
            padding: 8px;
            border-radius: 4px;
            font-size: 12px;
            overflow-x: auto;
        }
        
        .help-animations-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .help-anim-tag {
            background: #e3f2fd;
            color: #1565c0;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .help-button {
            display: inline-block;
            background: #2e7d32;
            color: white !important;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .help-button:hover {
            background: #1b5e20;
            transform: translateY(-2px);
        }
        
        @media (max-width: 782px) {
            .help-widget-content {
                width: 90vw;
                right: 5vw;
            }
        }
        </style>
        
        <script>
        function toggleHelpWidget() {
            const widget = document.getElementById('syntekpro-help-widget');
            widget.classList.toggle('active');
        }
        </script>
        <?php
    }
    
    /**
     * Ajax handler for contextual help
     */
    public function ajax_get_help() {
        check_ajax_referer('syntekpro_help_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to access help data.', 'syntekpro-animations')), 403);
        }
        
        $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
        
        $help_content = $this->get_help_content($topic);
        
        wp_send_json_success($help_content);
    }
    
    /**
     * Get help content for specific topic
     */
    private function get_help_content($topic) {
        $help = array(
            'animation-types' => array(
                'title' => 'Animation Types',
                'content' => 'We offer 50+ animation types including Fade, Slide, Zoom, Rotate, Bounce, and more.'
            ),
            'shortcodes' => array(
                'title' => 'Using Shortcodes',
                'content' => 'Use [sp_animate] shortcode with type, duration, and other parameters.'
            ),
            'timeline' => array(
                'title' => 'Timeline Builder',
                'content' => 'Create multi-step animation sequences with [sp_timeline] shortcode.'
            )
        );
        
        return isset($help[$topic]) ? $help[$topic] : array(
            'title' => 'Help',
            'content' => 'Select a topic to learn more.'
        );
    }
}

// Initialize
new Syntekpro_Help_System();
