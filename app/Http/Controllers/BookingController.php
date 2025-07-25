<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingListingFormRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(BookingListingFormRequest $request)
    {
        $bookings = Booking::query()
                        ->with(['customer', 'service'])    
                        ->when($request->filled("service_id"), 
                            fn($query) => $query->where("service_id", $request->service_id))
                        ->when($request->filled("status"), 
                            fn($query) => $query->where("status", $request->status))
                        ->when($request->filled("date_from") && $request->isNotFilled("date_to"), 
                            fn($query) => $query->whereDate("starts_at", ">=", $request->date_from))
                        ->when($request->filled("date_from") && $request->filled("date_to"), 
                            fn($query) => $query->whereDate("starts_at", ">=",  $request->date_from)->whereDate("ends_at", "<=", $request->date_to))
                        ->latest('starts_at')
                        ->paginate();
       
        return BookingResource::collection($bookings);
    }

}
