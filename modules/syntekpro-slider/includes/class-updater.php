<?php
/**
 * GitHub-based self-hosted update checker.
 *
 * Checks GitHub Releases for new versions and surfaces them
 * in the WordPress updates UI — no wordpress.org listing required.
 *
 * @package SyntekPro_Slider
 */

defined( 'ABSPATH' ) || exit;

class SPSLIDER_Updater {

    /** @var string Full path to the main plugin file. */
    private $file;

    /** @var string Plugin basename (e.g. SyntekPro-Slider/syntekpro-slider.php). */
    private $basename;

    /** @var string Current plugin version. */
    private $version;

    /** @var string GitHub owner/repo. */
    private $repo = 'syntekpro/SyntekPro-Slider';

    /** @var string GitHub API root. */
    private $api = 'https://api.github.com';

    /** @var string Cache transient key. */
    private $transient_key = 'spslider_update_check';

    /** @var int Cache lifetime in seconds (12 hours). */
    private $cache_ttl = 43200;

    /**
     * Constructor.
     *
     * @param string $file    Full path to main plugin file.
     * @param string $version Current plugin version.
     */
    public function __construct( $file, $version ) {
        $this->file     = $file;
        $this->basename = plugin_basename( $file );
        $this->version  = $version;
    }

    /**
     * Register WordPress hooks.
     */
    public function init() {
        add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'check_update' ] );
        add_filter( 'plugins_api',                           [ $this, 'plugin_info' ], 20, 3 );
        add_action( 'upgrader_process_complete',             [ $this, 'clear_cache' ], 10, 2 );
    }

    /**
     * Inject update data into WordPress transient.
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $release = $this->get_latest_release();
        if ( ! $release ) {
            return $transient;
        }

        $remote_version = ltrim( $release['tag_name'], 'vV' );
        if ( version_compare( $this->version, $remote_version, '<' ) ) {
            $transient->response[ $this->basename ] = (object) [
                'slug'        => dirname( $this->basename ),
                'plugin'      => $this->basename,
                'new_version' => $remote_version,
                'url'         => $release['html_url'],
                'package'     => $this->get_zip_url( $release ),
                'icons'       => [
                    '1x' => SPSLIDER_URL . 'assets/img/SyntekPro Slider Colored Icon.png',
                ],
                'banners'     => [],
                'tested'      => '6.7',
                'requires'    => '5.8',
                'requires_php'=> '7.4',
            ];
        }

        return $transient;
    }

    /**
     * Supply full plugin info for the "View Details" popup.
     */
    public function plugin_info( $result, $action, $args ) {
        if ( 'plugin_information' !== $action ) {
            return $result;
        }

        if ( ! isset( $args->slug ) || dirname( $this->basename ) !== $args->slug ) {
            return $result;
        }

        $release = $this->get_latest_release();
        if ( ! $release ) {
            return $result;
        }

        $remote_version = ltrim( $release['tag_name'], 'vV' );

        return (object) [
            'name'            => 'SyntekPro Slider',
            'slug'            => dirname( $this->basename ),
            'version'         => $remote_version,
            'author'          => '<a href="https://syntekpro.com">SyntekPro</a>',
            'author_profile'  => 'https://syntekpro.com',
            'homepage'        => 'https://plugins.syntekpro.com/slider',
            'requires'        => '5.8',
            'tested'          => '6.7',
            'requires_php'    => '7.4',
            'download_link'   => $this->get_zip_url( $release ),
            'trunk'           => $this->get_zip_url( $release ),
            'last_updated'    => $release['published_at'] ?? '',
            'sections'        => [
                'description'  => 'The most powerful drag-and-drop slider plugin for WordPress. Build stunning, responsive sliders with 21 advanced features — no coding required.',
                'changelog'    => nl2br( esc_html( $release['body'] ?? '' ) ),
            ],
            'banners'         => [],
        ];
    }

    /**
     * Clear cached release data after an upgrade.
     */
    public function clear_cache( $upgrader, $options ) {
        if ( 'update' === ( $options['action'] ?? '' )
             && 'plugin' === ( $options['type'] ?? '' ) ) {
            delete_transient( $this->transient_key );
        }
    }

    /**
     * Fetch latest release from GitHub (with caching).
     *
     * @return array|null
     */
    private function get_latest_release() {
        $cached = get_transient( $this->transient_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $url = $this->api . '/repos/' . $this->repo . '/releases/latest';
        $response = wp_remote_get( $url, [
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'SyntekPro-Slider/' . $this->version,
            ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['tag_name'] ) ) {
            return null;
        }

        set_transient( $this->transient_key, $body, $this->cache_ttl );

        return $body;
    }

    /**
     * Get the zip download URL from a release (prefer .zip asset, fall back to source).
     *
     * @param  array $release GitHub release data.
     * @return string
     */
    private function get_zip_url( $release ) {
        // Prefer an explicitly uploaded .zip asset
        if ( ! empty( $release['assets'] ) && is_array( $release['assets'] ) ) {
            foreach ( $release['assets'] as $asset ) {
                if ( '.zip' === substr( $asset['name'], -4 ) ) {
                    return $asset['browser_download_url'];
                }
            }
        }
        // Fall back to GitHub auto-generated source zip
        return $release['zipball_url'] ?? '';
    }
}
