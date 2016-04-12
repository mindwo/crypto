<?php

namespace mindwo\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class CalendarController extends Controller
{
    /**
     * Kalendāra kontrolieris
     */

    /**
     * Izgūst kalendāra ieraksta datus pēc norādītā ieraksta ID
     * 
     * @param \Illuminate\Http\Request $request AJAX POST pieprasījuma objekts
     * @return Response JSON ar kalendāra ieraksta datiem
     */
    public function getEvent(Request $request)
    {
        $this->validate($request, [
            'item_id' => 'required|integer|exists:in_events,id'
        ]);

        $item_id = $request->input('item_id', 0);
        $event = DB::table('in_events')
                ->where('is_active', '=', 1)
                ->where('id', '=', $item_id)
                ->first();

        $htm = view('mindwo\pages::elements.event_info', [
            'title' => $event->title,
            'description' => $event->description,
            'time_from' => format_event_time($event->event_time_from),
            'time_to' => format_event_time($event->event_time_to),
            'picture' => $event->picture_guid,
            'address' => $event->address
                ])->render();

        return response()->json(['success' => 1, 'html' => $htm]);
    }

}
