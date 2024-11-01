<?php

interface MigrationInterface
{
    public function run();

    public function getVersionNumber();
}
