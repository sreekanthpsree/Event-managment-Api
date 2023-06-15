<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;

    function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');
    }

    private $relations = ['user', 'attendees', 'attendees.user'];

    public function index()
    {
        $query = $this->loadRelationships(Event::query(), $this->relations);

        return EventResource::collection($query->latest()->paginate());
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time'
        ]);
        $event = Event::create([...$data, 'user_id' => $request->user()->id]);

        return new EventResource($this->loadRelationships($event));
    }


    public function show(Event $event)
    {
        $event->load('user', 'attendees');
        return new EventResource($this->loadRelationships($event));
    }

    public function update(Request $request, Event $event)
    {
        // if (Gate::denies('update-event', $event)) {
        //     abort(403, 'You are not authorised to update the event');
        // }
        $this->authorize('update-event', $event);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'string|nullable',
            'start_time' => 'date|sometimes',
            'end_time' => 'date|sometimes|after:start_time'
        ]);
        $event->update($data);
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}