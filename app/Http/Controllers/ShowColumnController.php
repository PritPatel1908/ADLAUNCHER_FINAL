<?php

namespace App\Http\Controllers;

use App\Models\ShowColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowColumnController extends Controller
{
    /**
     * Get column visibility preferences for the current user and table.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getColumns(Request $request)
    {
        $table = $request->input('table');
        $userId = Auth::id();
        
        $columns = ShowColumn::where('user_id', $userId)
            ->where('table', $table)
            ->get();
            
        return response()->json([
            'success' => true,
            'columns' => $columns
        ]);
    }
    
    /**
     * Update column visibility preference.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateColumn(Request $request)
    {
        $request->validate([
            'table' => 'required|string',
            'column_name' => 'required|string',
            'is_show' => 'required|boolean|in:0,1,true,false',
        ]);
        
        $userId = Auth::id();
        $table = $request->input('table');
        $columnName = $request->input('column_name');
        $isShow = $request->input('is_show');
        
        $column = ShowColumn::updateOrCreate(
            [
                'user_id' => $userId,
                'table' => $table,
                'column_name' => $columnName,
            ],
            [
                'is_show' => $isShow,
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Column visibility updated successfully',
            'column' => $column
        ]);
    }
}
