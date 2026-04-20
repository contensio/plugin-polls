<?php

/**
 * Simple Polls - Contensio plugin.
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
    protected string $ns = 'contensio-polls';

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->ns);
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Settings hub card
        Hook::add('contensio/admin/settings-cards', function () {
            return view($this->ns . '::partials.settings-hub-card')->render();
        });
    }
}
