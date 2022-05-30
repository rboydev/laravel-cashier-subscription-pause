<?php

namespace CashierSubscriptionPause\Eloquent;

use Carbon\Carbon;

/**
 * @link https://stripe.com/docs/api/subscriptions/object#subscription_object-pause_collection
 */
interface WithPauseCollection
{
    const BEHAVIOR_MARK_UNCOLLECTIBLE = 'mark_uncollectible';
    const BEHAVIOR_KEEP_AS_DRAFT      = 'keep_as_draft';
    const BEHAVIOR_VOID               = 'void';

    /**
     * Retrieve and save the Stripe pause_collection of the subscription.
     *
     * @return static
     */
    public function syncStripePauseCollection();

    /**
     * Pause subscription.
     *
     * @param string $behavior
     * @param Carbon|null $resumesAt
     *
     * @return $this
     *
     * @throws \LogicException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function pause(string $behavior, Carbon $resumesAt = null);

    /**
     * Pause with behavior mark_uncollectible.
     *
     * @param Carbon|null $resumesAt
     *
     * @return $this
     */
    public function pauseBehaviorMarkUncollectible(Carbon $resumesAt = null);

    /**
     * Pause with behavior keep_as_draft.
     *
     * @param Carbon|null $resumesAt
     *
     * @return $this
     */
    public function pauseBehaviorKeepAsDraft(Carbon $resumesAt = null);

    /**
     * Pause with behavior void.
     *
     * @param Carbon|null $resumesAt
     *
     * @return $this
     */
    public function pauseBehaviorVoid(Carbon $resumesAt = null);

    /**
     * Resume the paused subscription.
     *
     * @return static
     *
     * @throws \LogicException
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function unpause();

    /**
     * Check is current subscription paused.
     *
     * @param string|null $behavior - Check specific behavior, if null will check any behavior.
     *
     * @return bool
     */
    public function paused(string $behavior = null);

    /**
     * Check is current subscription not paused.
     *
     * @param string|null $behavior - Check specific behavior, if null will check any behavior.
     *
     * @return bool
     */
    public function notPaused(string $behavior = null);

    /**
     * Get auto pause resumes timestamp.
     *
     * @param string|null $behavior
     *
     * @return string|null
     */
    public function pauseResumesAtTimestamp(string $behavior = null);

    /**
     * Get auto pause resumes datetime.
     *
     * @param string|null $behavior - Check specific behavior, if null will check any behavior.
     *
     * @return Carbon|null
     */
    public function pauseResumesAt(string $behavior = null);
}
