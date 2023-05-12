<?php

declare(strict_types=1);

define('TEST_HOST', \Ssh\Environment::get('TEST_HOST') ?? 'localhost');
define('TEST_USER', \Ssh\Environment::get('TEST_USER') ?? get_current_user());
define('TEST_PASSWORD', \Ssh\Environment::get('TEST_PASSWORD') ?? '1234');
