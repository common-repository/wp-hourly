<?php

define('WPH_DB_VERSION_OPTION_NAME', 'wph_db_version');
define('WPH_MIGRATION_IN_PROGRESS_OPTION_NAME', 'wph_migration_in_progress');

require_once(__DIR__ . '/MigrationInterface.php');

function wphMigrateIfNeeded()
{
    $dbVersion = get_option(WPH_DB_VERSION_OPTION_NAME, '0.0.0');
    $migrationInProgress = get_option(WPH_MIGRATION_IN_PROGRESS_OPTION_NAME);

    if ($dbVersion >= WPH_VERSION || $migrationInProgress == '1') {
        return;
    }

    update_option(WPH_MIGRATION_IN_PROGRESS_OPTION_NAME, '1');

    $lastMigration = $dbVersion;
    foreach (wphGetSortedMigrations($dbVersion) as $migrationData) {
        require_once __DIR__ . '/' . $migrationData[0];
        /** @var MigrationInterface $migration */
        $migration = new $migrationData[1];
        $migration->run();

        $lastMigration = $migration->getVersionNumber();
        update_option(WPH_DB_VERSION_OPTION_NAME, $migration->getVersionNumber());
    }

    if ($lastMigration < WPH_VERSION) {
        update_option(WPH_DB_VERSION_OPTION_NAME, WPH_VERSION);
    }

    update_option(WPH_MIGRATION_IN_PROGRESS_OPTION_NAME, '0');
}

function wphGetSortedMigrations($currentVersion)
{
    $allFiles = scandir(__DIR__);

    $migrations = [];
    foreach ($allFiles as $file) {
        if (!preg_match('/V(\d*_\d*_\d*)Migration/', $file, $matches)) {
            continue;
        }

        $version = str_replace('_', '.', $matches[1]);
        if ($version > $currentVersion) {
            $migrations[] = [$file, $matches[0], $version];
        }
    }

    usort($migrations, function($a, $b) { return $a[2] > $b[2]; });

    return $migrations;
}


