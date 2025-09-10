<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::latest()->get();
        return view('company.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Normalize status to lowercase to tolerate UI capitalization (e.g., "Deactivate")
        if ($request->has('status') && is_string($request->status)) {
            $request->merge(['status' => strtolower(trim($request->status))]);
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            // Accept human labels or integers; mapped below
            'status' => 'nullable|string|in:active,inactive,deactivate,block,delete,0,1,2,3',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required|string|max:50',
            'addresses.*.address' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'nullable|string|max:100',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.designation' => 'nullable|string|max:100',
            'contacts.*.is_primary' => 'nullable|boolean',
            'notes' => 'nullable|array',
            'notes.*.note' => 'nullable|string|max:1000',
            // Keep note statuses aligned with UI chips
            'notes.*.status' => 'nullable|integer|in:0,1,2,3',
        ]);

        // Set the created_by field to the current authenticated user's ID
        $validated['created_by'] = Auth::user()->id;

        // Convert status string to integer
        // Map incoming values to desired numeric statuses: 0=Delete, 1=Active, 2=Inactive, 3=Block
        $statusMap = [
            'delete' => 0,
            '0' => 0,
            'active' => 1,
            '1' => 1,
            'inactive' => 2,
            'deactivate' => 2,
            '2' => 2,
            'block' => 3,
            '3' => 3,
        ];
        $validated['status'] = $statusMap[strtolower((string)($validated['status'] ?? 'active'))] ?? 1;

        // Create the company
        $company = Company::create($validated);

        // Handle locations relationship
        if (isset($validated['location_ids'])) {
            $company->locations()->attach($validated['location_ids']);
        }

        // Handle addresses
        if (isset($validated['addresses'])) {
            foreach ($validated['addresses'] as $addressData) {
                if (!empty($addressData['address']) && !empty($addressData['type'])) {
                    $company->addresses()->create($addressData);
                }
            }
        }

        // Handle contacts
        if (isset($validated['contacts'])) {
            foreach ($validated['contacts'] as $contactData) {
                if (!empty($contactData['name'])) {
                    $company->contacts()->create($contactData);
                }
            }
        }

        // Handle notes
        if (isset($validated['notes'])) {
            foreach ($validated['notes'] as $noteData) {
                if (!empty($noteData['note']) && trim($noteData['note']) !== '') {
                    $noteData['created_by'] = Auth::user()->id;
                    $noteData['status'] = (int)($noteData['status'] ?? 1);
                    $company->notes()->create($noteData);
                }
            }
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully',
                'company' => $company->load(['locations', 'addresses', 'contacts', 'notes'])
            ]);
        }

        return redirect()->route('company.index')->with('success', 'Company created successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $company = Company::with(['locations', 'addresses', 'contacts', 'notes'])->findOrFail($id);

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'company' => $company
            ]);
        }

        return view('company.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $company = Company::with(['locations', 'addresses', 'contacts', 'notes'])->findOrFail($id);

            // Check if request is AJAX
            if (request()->ajax()) {
                // Debug: Log the addresses data
                Log::info('Company addresses data:', [
                    'company_id' => $company->id,
                    'addresses' => $company->addresses->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'company' => $company
                ]);
            }

            return redirect()->route('company.index');
        } catch (\Exception $e) {
            Log::error('Error in CompanyController::edit: ' . $e->getMessage());

            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load company data. Please try again.'
                ], 500);
            }

            return redirect()->route('company.index')->with('error', 'Failed to load company data.');
        }
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
        $company = Company::findOrFail($id);

        // Normalize status to lowercase to tolerate UI capitalization (e.g., "Deactivate")
        if ($request->has('status') && is_string($request->status)) {
            $request->merge(['status' => strtolower(trim($request->status))]);
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            // Accept human labels or integers; mapped below
            'status' => 'nullable|string|in:active,inactive,deactivate,block,delete,0,1,2,3',
            'location_ids' => 'nullable|array',
            'location_ids.*' => 'exists:locations,id',
            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required|string|max:50',
            'addresses.*.address' => 'nullable|string|max:255',
            'addresses.*.city' => 'nullable|string|max:100',
            'addresses.*.state' => 'nullable|string|max:100',
            'addresses.*.country' => 'nullable|string|max:100',
            'addresses.*.zip_code' => 'nullable|string|max:20',
            'contacts' => 'nullable|array',
            'contacts.*.name' => 'nullable|string|max:255',
            'contacts.*.email' => 'nullable|email|max:255',
            'contacts.*.phone' => 'nullable|string|max:20',
            'contacts.*.designation' => 'nullable|string|max:100',
            'contacts.*.is_primary' => 'nullable|boolean',
            'notes' => 'nullable|array',
            'notes.*.note' => 'required_with:notes.*.status|nullable|string|max:1000',
            // Match create validation to avoid mismatch errors
            'notes.*.status' => 'nullable|integer|in:0,1,2,3',
        ]);

        // Set the updated_by field to the current authenticated user's ID
        $validated['updated_by'] = Auth::user()->id;

        // Convert status string to integer
        // Map incoming values to desired numeric statuses: 0=Delete, 1=Active, 2=Inactive, 3=Block
        $statusMap = [
            'delete' => 0,
            '0' => 0,
            'active' => 1,
            '1' => 1,
            'inactive' => 2,
            'deactivate' => 2,
            '2' => 2,
            'block' => 3,
            '3' => 3,
        ];
        $validated['status'] = $statusMap[strtolower((string)($validated['status'] ?? 'active'))] ?? 1;

        // Update the company
        $company->update($validated);

        // Handle locations relationship
        if (isset($validated['location_ids'])) {
            $company->locations()->sync($validated['location_ids']);
        }

        // Handle addresses - replace all existing addresses
        if (isset($validated['addresses'])) {
            $company->addresses()->delete(); // Remove existing addresses
            foreach ($validated['addresses'] as $addressData) {
                if (!empty($addressData['address']) && !empty($addressData['type'])) {
                    $company->addresses()->create($addressData);
                }
            }
        }

        // Handle contacts - replace all existing contacts
        if (isset($validated['contacts'])) {
            $company->contacts()->delete(); // Remove existing contacts
            foreach ($validated['contacts'] as $contactData) {
                if (!empty($contactData['name'])) {
                    $company->contacts()->create($contactData);
                }
            }
        }

        // Handle notes - replace all existing notes
        if (isset($validated['notes'])) {
            $company->notes()->delete(); // Remove existing notes
            foreach ($validated['notes'] as $noteData) {
                if (!empty($noteData['note']) && trim($noteData['note']) !== '') {
                    $noteData['created_by'] = Auth::user()->id;
                    $noteData['status'] = (int)($noteData['status'] ?? 1);
                    $company->notes()->create($noteData);
                }
            }
        }

        // Check if request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'company' => $company->load(['locations', 'addresses', 'contacts', 'notes', 'createdByUser', 'updatedByUser'])
            ]);
        }

        // Check if request came from show page
        if ($request->has('from_show') || strpos($request->header('referer'), '/company/') !== false) {
            return redirect()->route('company.show', $company->id)->with('success', 'Company updated successfully');
        }

        return redirect()->route('company.index')->with('success', 'Company updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        // Check if request is AJAX
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully'
            ]);
        }

        return redirect()->route('company.index')->with('success', 'Company deleted successfully');
    }

    /**
     * Get data for DataTables.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            $query = Company::with(['locations', 'addresses', 'contacts', 'notes', 'createdByUser', 'updatedByUser']);

            // Apply search filter
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('industry', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%")
                        ->orWhere('phone', 'like', "%{$searchValue}%");
                });
            }

            // Apply column-specific filtering
            if ($request->has('name_filter') && !empty($request->name_filter)) {
                $query->where('name', 'like', "%{$request->name_filter}%");
            }

            if ($request->has('industry_filter') && !empty($request->industry_filter)) {
                $query->where('industry', 'like', "%{$request->industry_filter}%");
            }

            if ($request->has('website_filter') && !empty($request->website_filter)) {
                $query->where('website', 'like', "%{$request->website_filter}%");
            }

            if ($request->has('email_filter') && !empty($request->email_filter)) {
                $query->where('email', 'like', "%{$request->email_filter}%");
            }

            if ($request->has('phone_filter') && !empty($request->phone_filter)) {
                $query->where('phone', 'like', "%{$request->phone_filter}%");
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->start_date;
                $endDate = $request->end_date;
                $endDate = date('Y-m-d', strtotime($endDate . ' +1 day'));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Apply status filter
            if ($request->has('status_filter') && !empty($request->status_filter)) {
                $statusFilter = $request->status_filter;
                if (!is_array($statusFilter)) {
                    $statusFilter = [$statusFilter];
                }

                $query->where(function ($q) use ($statusFilter) {
                    foreach ($statusFilter as $status) {
                        $normalized = strtolower($status);
                        if ($normalized === 'active' || $normalized === '1' || $normalized === 1) {
                            $q->orWhere('status', 1);
                        } else if ($normalized === 'inactive' || $normalized === '2' || $normalized === 2) {
                            $q->orWhere('status', 2);
                        } else if ($normalized === 'block' || $normalized === 'blocked' || $normalized === '3' || $normalized === 3) {
                            $q->orWhere('status', 3);
                        } else if ($normalized === 'delete' || $normalized === 'deleted' || $normalized === '0' || $normalized === 0) {
                            $q->orWhere('status', 0);
                        }
                    }
                });
            }

            // Count filtered records
            $recordsFiltered = $query->count();

            // Apply ordering
            if ($request->has('order') && !empty($request->order)) {
                foreach ($request->order as $order) {
                    $columnName = $this->getColumnName($request->columns[$order['column']]['data']);
                    if ($columnName) {
                        $query->orderBy($columnName, $order['dir']);
                    }
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Apply pagination
            $query->skip($request->start)->take($request->length);

            // Get results
            $companies = $query->get();

            // Format data for DataTables
            $data = [];
            // $canViewAuditFields = \App\Helpers\PermissionHelper::canViewAuditFields();

            foreach ($companies as $company) {
                $companyData = [
                    'id' => $company->id,
                    'name' => $company->name,
                    'industry' => $company->industry,
                    'website' => $company->website,
                    'email' => $company->email,
                    'phone' => $company->phone,
                    'created_at' => $company->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $company->updated_at->format('Y-m-d H:i:s'),
                    'status' => match ((int) $company->status) {
                        0 => 'Delete',
                        1 => 'Active',
                        2 => 'Inactive',
                        3 => 'Block',
                        default => 'Inactive',
                    },
                    'locations_count' => $company->locations->count(),
                    'addresses_count' => $company->addresses->count(),
                    'contacts_count' => $company->contacts->count(),
                    'notes_count' => $company->notes->count(),
                ];

                // Only include audit fields if user has permission
                // if ($canViewAuditFields) {
                $companyData['created_by'] = $company->createdByUser ? $company->createdByUser->name : 'N/A';
                $companyData['updated_by'] = $company->updatedByUser ? $company->updatedByUser->name : 'N/A';
                // }

                $data[] = $companyData;
            }

            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => Company::count(),
                'recordsFiltered' => $recordsFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CompanyController::getData: ' . $e->getMessage());
            return response()->json([
                'draw' => $request->draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Failed to load company data. Please try again.'
            ], 500);
        }
    }

    /**
     * Get the actual column name from DataTables column data.
     *
     * @param  string  $columnData
     * @return string|null
     */
    private function getColumnName($columnData)
    {
        $columnMap = [
            'name' => 'name',
            'industry' => 'industry',
            'website' => 'website',
            'email' => 'email',
            'phone' => 'phone',
            'status' => 'status',
            'created_at' => 'created_at',
            'updated_at' => 'updated_at',
        ];

        // Only include audit columns if user has permission
        // if (\App\Helpers\PermissionHelper::canViewAuditFields()) {
        $columnMap['created_by'] = 'created_by';
        $columnMap['updated_by'] = 'updated_by';
        // }

        return $columnMap[$columnData] ?? null;
    }
}
