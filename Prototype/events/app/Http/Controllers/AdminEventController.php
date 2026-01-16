<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\EventService;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class AdminEventController extends Controller
{
    protected $eventService;
    protected $categoryService;

    public function __construct(EventService $eventService, CategoryService $categoryService)
    {
        $this->eventService = $eventService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $filters = $request->only(['search', 'category']);
            $events = $this->eventService->getAdminEvents($filters, 10);
            
            return view('admin.events.partials.table', compact('events'))->render();
        }

        $filters = $request->only(['search', 'category']);
        $events = $this->eventService->getAdminEvents($filters, 10);
        $categories = $this->categoryService->getAllCategories();
        
        return view('admin.events.index', compact('events', 'categories'));
    }

    public function store(Request $request)
    {
        // Validation logic stays in controller (or moves to FormRequest)
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'event_date' => 'required|date',
            'categories' => 'required|array',
            'status' => 'required|in:draft,published',
            'image' => 'nullable|image',
        ]);

        $this->eventService->createEvent($validated, $request->file('image'));

        return response()->json(['message' => 'Event created successfully']);
    }

    public function update(Request $request, Event $event)
    {
         $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            'event_date' => 'required|date',
            'categories' => 'required|array',
            'status' => 'required|in:draft,published',
             'image' => 'nullable|image',
        ]);

        $this->eventService->updateEvent($event, $validated, $request->file('image'));

        return response()->json(['message' => 'Event updated successfully']);
    }

    public function destroy(Event $event)
    {
        $this->eventService->deleteEvent($event);
        return response()->json(['message' => 'Event deleted successfully']);
    }
}
