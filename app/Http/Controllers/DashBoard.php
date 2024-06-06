<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\Expenses;

class DashBoard extends Controller
{
    

    function ViewTotalSales() {
        $totalSales = Sales::sum('Amount');
        return response()->json(['sales' => $totalSales]);
    }
    
    function ViewTotalExpenses() {
        $totalExpenses = Expenses::sum('Amount');
        return response()->json(['expenses' => $totalExpenses]);
    }



}
