<?php

namespace App\Console\Commands;

use illuminate\Support\Str;
use App\Models\Event;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications to all event attendees that event is near!!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')->whereBetween('start_time', [now(), now()->addDay()])->get();
        $eventCount = $events->count();
        $eventLabel = Str::plural('event', $eventCount);
        $this->info("Found {$eventCount} {$eventLabel}");

        $events->each(function ($event) {
            $event->attendees->each(function ($attendee) {
                $this->info("Notifying the user {$attendee->user_id}");
            });
        });

        $this->info('Send reminder notification successfully!!');
    }
}