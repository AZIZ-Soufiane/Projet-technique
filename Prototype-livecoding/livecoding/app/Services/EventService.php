<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{


    /**
     * Get events for the admin area with filtering options.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAdminEvents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Event::with(['categories']);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }



        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Create a new event.
     *
     * @param array $data
     * @param UploadedFile|null $image
     * @return Event
     */
    public function createEvent(array $data, ?UploadedFile $image = null): Event
    {
        $event = new Event($data);
        $event->user_id = auth()->id() ?? 1; // Fallback

        if ($image) {
            $event->image = $this->uploadImage($image);
        }

        $event->save();

        if (isset($data['categories'])) {
            $event->categories()->sync($data['categories']);
        }

        return $event;
    }

    /**
     * Update an existing event.
     *
     * @param Event $event
     * @param array $data
     * @param UploadedFile|null $image
     * @return Event
     */
    public function updateEvent(Event $event, array $data, ?UploadedFile $image = null): Event
    {
        $event->fill($data);

        if ($image) {
            // Optional: Delete old image if exists
            // if ($event->image) {
            //     Storage::disk('public')->delete($event->image);
            // }
            $event->image = $this->uploadImage($image);
        }

        $event->save();

        if (isset($data['categories'])) {
            $event->categories()->sync($data['categories']);
        }

        return $event;
    }

    /**
     * Delete an event.
     *
     * @param Event $event
     * @return bool|null
     */
    public function deleteEvent(Event $event): ?bool
    {
        // Optional: Delete image from storage
        // if ($event->image) {
        //     Storage::disk('public')->delete($event->image);
        // }
        return $event->delete();
    }

    /**
     * Handle image upload.
     *
     * @param UploadedFile $image
     * @return string
     */
    protected function uploadImage(UploadedFile $image): string
    {
        return $image->store('events', 'public');
    }
}
