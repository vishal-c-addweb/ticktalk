<?php

namespace App\Observers;

use App\Events\TicketEvent;
use App\Events\TicketRequesterEvent;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\UniversalSearch;

class TicketObserver
{

    public function saving(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $ticket->last_updated_by = user()->id;
        }
    }

    public function creating(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            $ticket->added_by = user()->id;
        }
    }

    public function created(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            // Send admin notification
            event(new TicketEvent($ticket, 'NewTicket'));

            if($ticket->requester){
                event(new TicketRequesterEvent($ticket, $ticket->requester));
            }
        }
    }

    public function updated(Ticket $ticket)
    {
        if (!isRunningInConsoleOrSeeding()) {
            if ($ticket->isDirty('agent_id')) {
                event(new TicketEvent($ticket, 'TicketAgent'));
            }
        }
    }

    public function deleting(Ticket $ticket)
    {
        $universalSearches = UniversalSearch::where('searchable_id', $ticket->id)->where('module_type', 'ticket')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }

        $notifiData = ['App\Notifications\NewTicket','App\Notifications\NewTicketReply','App\Notifications\NewTicketRequester','App\Notifications\TicketAgent'];

        Notification::whereIn('type', $notifiData)
            ->whereNull('read_at')
            ->where('data', 'like', '{"id":'.$ticket->id.',%')
            ->delete();
    }

}
