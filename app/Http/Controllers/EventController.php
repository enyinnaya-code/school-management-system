<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index()
    {
        return view('events.calendar');
    }

    public function fetch()
    {
        $events = Event::with('creator')->get()->map(function ($event) {
            return [
                'id'              => $event->id,
                'title'           => $event->title,
                'description'     => $event->description ?? '',
                'start'           => $event->start_date->toIso8601String(),
                'end'             => $event->end_date?->toIso8601String(),
                'allDay'          => $event->is_all_day,
                'backgroundColor' => $event->color,
                'borderColor'     => $event->color,
                'extendedProps'   => [
                    'description'   => $event->description ?? '',
                    'created_by'    => $event->created_by,
                    'creator_name'  => $event->creator?->name ?? 'Unknown',
                ],
            ];
        });

        return response()->json($events);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'is_all_day'  => 'sometimes|boolean',
            'color'       => 'sometimes|string',
        ]);

        Event::create([
            'created_by'  => Auth::id(),
            'title'       => $request->title,
            'description' => $request->description,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
            'is_all_day'  => $request->boolean('is_all_day'),
            'color'       => $request->color ?? '#3788d8',
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Event $event)
    {
        $this->authorizeEvent($event);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'is_all_day'  => 'sometimes|boolean',
            'color'       => 'sometimes|string',
        ]);

        $event->update($request->only([
            'title',
            'description',
            'start_date',
            'end_date',
            'is_all_day',
            'color'
        ]));

        return response()->json(['success' => true]);
    }

    public function destroy(Event $event)
    {
        $this->authorizeEvent($event);
        $event->delete();

        return response()->json(['success' => true]);
    }

    private function authorizeEvent(Event $event)
    {
        $allowedTypes = [1, 2, 7]; // Super Admin, Admin, Principal

        if (!in_array(Auth::user()->user_type, $allowedTypes) && $event->created_by !== Auth::id()) {
            abort(403, 'You do not have permission to modify this event.');
        }
    }
}
