<?php

/**
 * Simple Polls — Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Polls;

use Contensio\Support\Hook;
use Illuminate\Support\ServiceProvider;

class PollsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'polls');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Settings hub card
        Hook::add('contensio/admin/settings-cards', function () {
            return view('polls::partials.settings-hub-card')->render();
        });
    }
}
