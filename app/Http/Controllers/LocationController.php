<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $locations = Location::latest()->get();
        return view('location.index', compact('locations'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('location.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'status' => 'nullable|integer|in:0,1,2,3',
        ]);

        // Set the created_by field to the current authenticated user's ID
        $validated['created_by'] = Auth::user()->id;

        // Create the location
        $location = Location::create($validated);

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Location created successfully',
                'location' => $location
            ]);
        }

        return redirect()->route('location.index')->with('success', 'Location created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $location = Location::findOrFail($id);

        // Check if request is AJAX
        if (request()->ajax()) {
            $location->created_at = $location->created_at->format('d M Y, h:i A');
            return response()->json([
                'location' => $location
            ]);
        }

        return view('location.show', compact('location'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $location = Location::findOrFail($id);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'location' => $location
            ]);
        }

        return redirect()->route('location.index');
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $location = Location::findOrFail($id);

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'status' => 'nullable|integer',
        ]);

        // Set the updated_by field to the current authenticated user's ID
        $validated['updated_by'] = Auth::user()->id;

        // Update the location
        $location->update($validated);

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully',
                'location' => $location
            ]);
        }

        return redirect()->route('location.index')->with('success', 'Location updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Location deleted successfully'
            ]);
        }

        return redirect()->route('location.index')->with('success', 'Location deleted successfully');
    }

    /**
     * Get locations data for DataTables
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $search = $request->get('search.value');
        $sortBy = $request->get('sort_by', 'newest'); // Default to newest

        // Get order column and direction from DataTables
        $orderColumn = $request->get('order.0.column');
        $orderDir = $request->get('order.0.dir');
        $columns = $request->get('columns');

        // Query builder for locations
        $query = Location::query();

        // Apply search if provided
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // Apply individual column filters
        if ($request->has('name_filter') && !empty($request->name_filter)) {
            $query->where('name', 'like', "%{$request->name_filter}%");
        }

        if ($request->has('email_filter') && !empty($request->email_filter)) {
            $query->where('email', 'like', "%{$request->email_filter}%");
        }

        if ($request->has('address_filter') && !empty($request->address_filter)) {
            $query->where('address', 'like', "%{$request->address_filter}%");
        }

        if ($request->has('city_filter') && !empty($request->city_filter)) {
            $query->where('city', 'like', "%{$request->city_filter}%");
        }

        if ($request->has('state_filter') && !empty($request->state_filter)) {
            $query->where('state', 'like', "%{$request->state_filter}%");
        }

        if ($request->has('country_filter') && !empty($request->country_filter)) {
            $query->where('country', 'like', "%{$request->country_filter}%");
        }

        if ($request->has('zip_code_filter') && !empty($request->zip_code_filter)) {
            $query->where('zip_code', 'like', "%{$request->zip_code_filter}%");
        }

        // Apply date range filter
        if ($request->has('start_date') && $request->has('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            // Add one day to end date to include the entire end date
            $endDate = date('Y-m-d', strtotime($endDate . ' +1 day'));

            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->has('status_filter') && !empty($request->status_filter)) {
            // Handle array of status values from checkboxes
            $statusFilter = $request->status_filter;

            // If it's a string, convert to array for consistent handling
            if (!is_array($statusFilter)) {
                $statusFilter = [$statusFilter];
            }

            $query->where(function ($q) use ($statusFilter) {
                foreach ($statusFilter as $status) {
                    if (strtolower($status) === 'active') {
                        $q->orWhere('status', Location::STATUS_ACTIVE);
                    } else if (strtolower($status) === 'inactive') {
                        $q->orWhere('status', Location::STATUS_INACTIVE);
                    } else if (strtolower($status) === 'blocked') {
                        $q->orWhere('status', Location::STATUS_BLOCKED);
                    } else if (strtolower($status) === 'deleted') {
                        $q->orWhere('status', Location::STATUS_DELETED);
                    }
                }
            });
        }

        // Get total count
        $totalRecords = $query->count();

        // Apply column sorting if provided by DataTables
        if ($orderColumn !== null && isset($columns[$orderColumn]['name'])) {
            $columnName = $columns[$orderColumn]['name'];
            if ($columnName !== 'action') { // Skip sorting for action column
                $query->orderBy($columnName, $orderDir);
            }
        } else {
            // Apply sorting based on sort_by parameter if no column sorting
            if ($sortBy === 'oldest') {
                $query->oldest('id');
            } else {
                $query->latest('id'); // Default to newest
            }
        }

        // Apply pagination
        $locations = $query->skip($start)
            ->take($length)
            ->get();

        // Format data for DataTables
        $data = [];
        // $canViewAuditFields = \App\Helpers\PermissionHelper::canViewAuditFields();

        foreach ($locations as $location) {
            $locationData = [
                'id' => $location->id,
                'name' => $location->name,
                'email' => $location->email,
                'address' => $location->address,
                'city' => $location->city,
                'state' => $location->state,
                'country' => $location->country,
                'zip_code' => $location->zip_code,
                'status' => $location->status_text,
                'created_at' => $location->created_at->format('d M Y, h:i A'),
                'updated_at' => $location->updated_at ? $location->updated_at->format('d M Y, h:i A') : null
            ];

            // Only include audit fields if user has permission
            // if ($canViewAuditFields) {
            $locationData['created_by'] = $location->created_by ? User::find($location->created_by)->name : null;
            $locationData['updated_by'] = $location->updated_by ? User::find($location->updated_by)->name : null;
            // }

            $data[] = $locationData;
        }

        return response()->json([
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data
        ]);
    }
}
