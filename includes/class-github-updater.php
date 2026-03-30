<?php
/**
 * GitHub updater integration for Syntekpro Animations.
 *
 * This class allows WordPress sites to receive plugin update notices
 * when a newer GitHub release is published.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Syntekpro_Animations_GitHub_Updater {

    private $plugin_file;
    private $plugin_basename;
    private $plugin_slug = 'syntekpro-animations';
    private $repo_owner = 'syntekpro';
    private $repo_name = 'syntekpro-animations';
    private $transient_key = 'syntekpro_anim_github_release';

    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_basename = plugin_basename($plugin_file);

        add_filter('pre_set_site_transient_update_plugins', array($this, 'inject_update_info'));
        add_filter('plugins_api', array($this, 'inject_plugin_info'), 20, 3);
        add_action('upgrader_process_complete', array($this, 'purge_cached_release'), 10, 2);
    }

    public function inject_update_info($transient) {
        if (!is_object($transient) || empty($transient->checked) || !is_array($transient->checked)) {
            return $transient;
        }

        $release = $this->get_latest_release_data();
        if (empty($release) || empty($release['version']) || empty($release['package'])) {
            return $transient;
        }

        $installed_version = isset($transient->checked[$this->plugin_basename]) ? $transient->checked[$this->plugin_basename] : SYNTEKPRO_ANIM_VERSION;
        if (version_compare($release['version'], $installed_version, '<=')) {
            return $transient;
        }

        $transient->response[$this->plugin_basename] = (object) array(
            'slug' => $this->plugin_slug,
            'plugin' => $this->plugin_basename,
            'new_version' => $release['version'],
            'url' => $release['url'],
            'package' => $release['package'],
            'tested' => get_bloginfo('version'),
            'requires' => '5.8',
            'requires_php' => '7.4',
        );

        return $transient;
    }

    public function inject_plugin_info($result, $action, $args) {
        if ('plugin_information' !== $action || empty($args->slug) || $args->slug !== $this->plugin_slug) {
            return $result;
        }

        $release = $this->get_latest_release_data();

        return (object) array(
            'name' => 'Syntekpro Animations',
            'slug' => $this->plugin_slug,
            'version' => !empty($release['version']) ? $release['version'] : SYNTEKPRO_ANIM_VERSION,
            'author' => '<a href="https://syntekpro.com">Syntekpro</a>',
            'author_profile' => 'https://syntekpro.com',
            'requires' => '5.8',
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.4',
            'homepage' => 'https://github.com/' . $this->repo_owner . '/' . $this->repo_name,
            'download_link' => !empty($release['package']) ? $release['package'] : '',
            'sections' => array(
                'description' => __('Professional animation and slider toolkit for WordPress with visual builders, presets, and advanced runtime controls.', 'syntekpro-animations'),
                'changelog' => !empty($release['body']) ? wp_kses_post(wpautop($release['body'])) : __('See CHANGELOG.md in the plugin package for full release notes.', 'syntekpro-animations'),
            ),
            'external' => true,
        );
    }

    public function purge_cached_release($upgrader, $hook_extra) {
        if (!is_array($hook_extra) || empty($hook_extra['type']) || $hook_extra['type'] !== 'plugin') {
            return;
        }

        delete_site_transient($this->transient_key);
    }

    private function get_latest_release_data() {
        $cached = get_site_transient($this->transient_key);
        if (is_array($cached) && !empty($cached['version'])) {
            if (empty(get_option('syntekpro_anim_update_latest_version', ''))) {
                update_option('syntekpro_anim_update_latest_version', (string) $cached['version']);
            }
            return $cached;
        }

        update_option('syntekpro_anim_update_last_checked', current_time('mysql'));

        $request_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            rawurlencode($this->repo_owner),
            rawurlencode($this->repo_name)
        );

        $response = wp_remote_get(
            $request_url,
            array(
                'timeout' => 20,
                'headers' => array(
                    'Accept' => 'application/vnd.github+json',
                    'User-Agent' => 'Syntekpro-Animations-Updater/' . SYNTEKPRO_ANIM_VERSION,
                ),
            )
        );

        if (is_wp_error($response) || (int) wp_remote_retrieve_response_code($response) !== 200) {
            update_option('syntekpro_anim_update_last_result', 'error');
            return array();
        }

        $payload = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($payload) || empty($payload['tag_name'])) {
            update_option('syntekpro_anim_update_last_result', 'invalid_payload');
            return array();
        }

        $version = ltrim((string) $payload['tag_name'], 'vV');
        $package = $this->detect_release_zip_url($payload);

        $release = array(
            'version' => $version,
            'url' => !empty($payload['html_url']) ? (string) $payload['html_url'] : 'https://github.com/' . $this->repo_owner . '/' . $this->repo_name,
            'package' => $package,
            'body' => !empty($payload['body']) ? (string) $payload['body'] : '',
        );

        update_option('syntekpro_anim_update_last_result', 'ok');
        update_option('syntekpro_anim_update_latest_version', $version);

        set_site_transient($this->transient_key, $release, 6 * HOUR_IN_SECONDS);

        return $release;
    }

    private function detect_release_zip_url($payload) {
        if (!empty($payload['assets']) && is_array($payload['assets'])) {
            foreach ($payload['assets'] as $asset) {
                if (empty($asset['browser_download_url']) || empty($asset['name'])) {
                    continue;
                }

                $name = strtolower((string) $asset['name']);
                if (substr($name, -4) === '.zip' && strpos($name, 'syntekpro-animations') !== false) {
                    return (string) $asset['browser_download_url'];
                }
            }

            foreach ($payload['assets'] as $asset) {
                if (!empty($asset['browser_download_url']) && !empty($asset['name']) && substr(strtolower((string) $asset['name']), -4) === '.zip') {
                    return (string) $asset['browser_download_url'];
                }
            }
        }

        if (!empty($payload['zipball_url'])) {
            return (string) $payload['zipball_url'];
        }

        return '';
    }
}
