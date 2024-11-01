<?php

final class V3_1_0Migration implements MigrationInterface
{
    const VERSION = '3.1.0';

    public function run()
    {
        global $wpdb;

        $wpdb->query("DROP VIEW IF EXISTS {$wpdb->prefix}unpaid_hours");
    }

    public function getVersionNumber()
    {
        return self::VERSION;
    }
}
