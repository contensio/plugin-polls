<?php

/**
 * Simple Polls — Contensio plugin.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

namespace Contensio\Polls\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Poll extends Model
{
    protected $table = 'polls';

    protected $fillable = [
        'question',
        'status',
        'show_results',
        'allow_guests',
        'ends_at',
    ];

    protected $casts = [
        'allow_guests' => 'boolean',
        'ends_at'      => 'datetime',
    ];

    const STATUS_DRAFT  = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    const SHOW_ALWAYS      = 'always';
    const SHOW_AFTER_VOTE  = 'after_vote';
    const SHOW_AFTER_CLOSE = 'after_close';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function options()
    {
        return $this->hasMany(PollOption::class)->orderBy('sort_order');
    }

    public function votes()
    {
        return $this->hasMany(PollVote::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED
            || ($this->ends_at && now()->gt($this->ends_at));
    }

    public function totalVotes(): int
    {
        return $this->votes()->count();
    }

    /**
     * Check whether this request has already voted on this poll.
     * Checks user ID first (logged-in), then falls back to IP.
     */
    public function hasVoted(Request $request): bool
    {
        $query = $this->votes()->where('poll_id', $this->id);

        if ($request->user()) {
            return $query->where('user_id', $request->user()->id)->exists();
        }

        return $query->where('ip_address', $request->ip())->exists();
    }

    /**
     * Whether results should be visible to this request right now.
     */
    public function canSeeResults(Request $request): bool
    {
        return match($this->show_results) {
            self::SHOW_ALWAYS      => true,
            self::SHOW_AFTER_VOTE  => $this->hasVoted($request) || $this->isClosed(),
            self::SHOW_AFTER_CLOSE => $this->isClosed(),
            default                => false,
        };
    }

    /**
     * Build the results array for JSON responses and the bar chart.
     * Returns ['total' => int, 'options' => [['id', 'label', 'votes', 'pct'], ...]]
     */
    public function buildResults(): array
    {
        $options = $this->options()->withCount('votes')->get();
        $total   = $options->sum('votes_count');

        return [
            'total'   => $total,
            'options' => $options->map(fn ($opt) => [
                'id'    => $opt->id,
                'label' => $opt->label,
                'votes' => $opt->votes_count,
                'pct'   => $total > 0 ? round($opt->votes_count / $total * 100) : 0,
            ])->all(),
        ];
    }
}
