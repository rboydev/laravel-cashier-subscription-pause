<?php

namespace CashierSubscriptionPause\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use LogicException;

/**
 * @property array|null $pause_collection
 * @extends WithPauseCollection
 */
trait UsesPauseCollection
{
    /**
     * @inerhitDoc
     */
    public function pause(string $behavior, Carbon $resumesAt = null)
    {
        if ($this->cancelled()) {
            throw new LogicException('Unable to pause subscription that is cancelled.');
        }

        $payload = [ 'behavior' => $behavior ];
        if ($resumesAt) {
            $payload['resumes_at'] = $resumesAt->timestamp;
        }

        $stripeSubscription = $this->owner->currentSubscription()->asStripeSubscription()->update(
            $this->stripe_id, [ 'pause_collection' => $payload]
        );

        $this->fill([
            'pause_collection' => $stripeSubscription->pause_collection,
        ])->save();

        return $this;
    }

    /**
     * @inerhitDoc
     */
    public function pauseBehaviorMarkUncollectible(Carbon $resumesAt = null)
    {
        return $this->pause(WithPauseCollection::BEHAVIOR_MARK_UNCOLLECTIBLE, $resumesAt);
    }

    /**
     * @inerhitDoc
     */
    public function pauseBehaviorKeepAsDraft(Carbon $resumesAt = null)
    {
        return $this->pause(WithPauseCollection::BEHAVIOR_KEEP_AS_DRAFT, $resumesAt);
    }

    /**
     * @inerhitDoc
     */
    public function pauseBehaviorVoid(Carbon $resumesAt = null)
    {
        return $this->pause(WithPauseCollection::BEHAVIOR_VOID, $resumesAt);
    }

    /**
     * @inerhitDoc
     */
    public function unpause()
    {
        if (!$this->paused()) {
            throw new LogicException('Unable to unpause subscription that is not paused.');
        }

        $stripeSubscription = $this->owner->stripe()->subscriptions->update(
            $this->stripe_id, [ 'pause_collection' => '', ]
        );

        $this->fill([
            'pause_collection' => $stripeSubscription->pause_collection,
        ])->save();

        return $this;
    }

    /**
     * @inerhitDoc
     */
    public function syncStripePauseCollection()
    {
        $subscription = $this->asStripeSubscription();

        $this->pause_collection = optional($subscription->pause_collection)->toArray();
        $this->save();

        return $this;
    }

    /**
     * @inerhitDoc
     */
    public function paused(?string $behavior = null)
    {
        $hasBehavior = is_array($this->pause_collection) && !empty($this->pause_collection['behavior']);
        if ($behavior) {
            return $hasBehavior && $this->pause_collection['behavior'] === $behavior;
        }

        return $hasBehavior;
    }

    /**
     * Filter query by pause_collection behavior.
     *
     * @param Builder $query
     * @param string|null $behavior
     *
     * @return Builder
     */
    public function scopePaused($query, string $behavior = null)
    {
        if (!$behavior) {
            return $query->whereNotNull('pause_collection')
                         ->where('pause_collection->behavior', '!=', '')
                         ->whereNotNull('pause_collection->behavior');
        }

        return $query->where('pause_collection->behavior', '=', $behavior);
    }

    /**
     * @inerhitDoc
     */
    public function notPaused(string $behavior = null)
    {
        return !$this->paused($behavior);
    }

    /**
     * Filter query by not paused payment collection.
     *
     * @param Builder $query
     * @param string|null $behavior
     *
     * @return Builder
     */
    public function scopeNotPaused($query, string $behavior = null)
    {
        if (!$behavior) {
            return $query->where(function ($query) {
                $query->whereNull('pause_collection')
                      ->orWhere('pause_collection->behavior', '=', '')
                      ->orWhereNull('pause_collection->behavior');
            });
        }

        return $query->where(function ($query) use ($behavior) {
            $query->where('pause_collection->behavior', '!=', $behavior)
                  ->orWhereNull('pause_collection')
                  ->orWhereNull('pause_collection->behavior');
        });
    }

    /**
     * @inerhitDoc
     */
    public function pauseResumesAtTimestamp(string $behavior = null)
    {
        if (!$this->paused($behavior) || empty($this->pause_collection['resumes_at'])) {
            return null;
        }

        return $this->pause_collection['resumes_at'];
    }

    /**
     * @inerhitDoc
     */
    public function pauseResumesAt(string $behavior = null)
    {
        if ($timestamp = $this->pauseResumesAtTimestamp($behavior)) {
            return Carbon::createFromTimestamp($timestamp);
        }

        return null;
    }
}
