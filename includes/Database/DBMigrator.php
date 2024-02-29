<?php

namespace ADReviewManager\Database;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once(WPRM_DIR.'includes/Database/Migrations/ReviewTable.php');


class DBMigrator
{
    const WPRMDBV = WPRM_DB_VERSION;

    public static function run($network_wide = false)
    {
        global $wpdb;

        if ($network_wide) {
            // Retrieve all site IDs from this network (WordPress >= 4.6 provides easy to use functions for that).
            if (function_exists('get_sites') && function_exists('get_current_network_id')) {
                $site_ids = get_sites(array('fields' => 'ids', 'network_id' => get_current_network_id()));
            } else {
                $site_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs WHERE site_id = $wpdb->siteid;");
            }
            // Install the plugin for all these sites.
            foreach ($site_ids as $site_id) {
                switch_to_blog($site_id);
                self::migrate();
                restore_current_blog();
            }
        } else {
            self::migrate();
        }
    }

    public static function migrate()
    {
        \ADReviewManager\Database\Migrations\ReviewTable::migrate();
        \ADReviewManager\Database\Migrations\Rating::migrate();
        \ADReviewManager\Database\Migrations\ReviewComment::migrate();
        \ADReviewManager\Database\Migrations\ReviewMedia::migrate();
        \ADReviewManager\Database\Migrations\CustomFeedback::migrate();
        // we are good. It's a new installation
        if (get_option('WPRM_DB_VERSION') < self::WPRMDBV) {
            self::maybeUpgradeDB();
        } else {
            // we are good. It's a new installation
            update_option('WPRM_DB_VERSION', self::WPRMDBV, false);
        }
    }

    public static function maybeUpgradeDB()
    {
        if (get_option('WPRM_DB_VERSION') < self::WPRMDBV) {
            // We need to upgrade the database
            self::forceUpgradeDB();
        }
    }

    // If needed in future
    public static function forceUpgradeDB()
    {
        // We are upgrading the DB forcedly
        // upgrade and migrate new tables
        update_option('WPRM_DB_VERSION', self::WPRMDBV, false);
    }
}
