<?php

namespace App\Providers;

use App\AttributeValue;
use App\Bibliography;
use App\Comment;
use App\Entity;
use App\Reference;
use App\Observers\BibliographyObserver;
use App\Observers\CommentObserver;
use App\Observers\EntityObserver;
use App\Observers\EntityAttributeObserver;
use App\Observers\ReferenceObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Bibliography::observe(BibliographyObserver::class);
        Comment::observe(CommentObserver::class);
        Entity::observe(EntityObserver::class);
        AttributeValue::observe(EntityAttributeObserver::class);
        Reference::observe(ReferenceObserver::class);
        //
    }
}
