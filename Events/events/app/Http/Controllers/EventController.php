<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index()
    {
        $events = $this->eventService->getPublishedEvents(10);
            
        return view('events.index', compact('events'));
    }

    public function show(Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }
        return view('events.show', compact('event'));
    }
}
