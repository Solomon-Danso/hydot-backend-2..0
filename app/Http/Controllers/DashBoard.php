<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\Expenses;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    function ViewTotalYearlySales() {
        $currentYear = Carbon::now()->year;
        $salesData = [];

        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $totalSales = Sales::whereYear('updated_at', $year)->sum('Amount');
            $salesData[] = [
                'year' => $year,
                'amount' => $totalSales
            ];
        }

        return response()->json(['sales' => $salesData]);
    }

    function ViewMonthlySalesAndExpenses() {
        $currentYear = Carbon::now()->year;
        $monthlySales = DB::table('sales')
            ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('SUM(Amount) as total_sales'))
            ->whereYear('updated_at', $currentYear)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month')
            ->toArray();

        $monthlyExpenses = DB::table('expenses')
            ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('SUM(Amount) as total_expenses'))
            ->whereYear('updated_at', $currentYear)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $stackedChartData = [[], []];

        for ($i = 1; $i <= 12; $i++) {
            $sales = isset($monthlySales[$i]) ? $monthlySales[$i]->total_sales : 0;
            $expenses = isset($monthlyExpenses[$i]) ? $monthlyExpenses[$i]->total_expenses : 0;

            $stackedChartData[0][] = ['x' => $months[$i - 1], 'y' => (float)$sales];
            $stackedChartData[1][] = ['x' => $months[$i - 1], 'y' => (float)$expenses];
        }

        $stackedCustomSeries = [
            [
                'dataSource' => $stackedChartData[0],
                'xName' => 'x',
                'yName' => 'y',
                'name' => 'Earnings',
                'type' => 'StackingColumn',
                'background' => 'blue',
            ],
            [
                'dataSource' => $stackedChartData[1],
                'xName' => 'x',
                'yName' => 'y',
                'name' => 'Expense',
                'type' => 'StackingColumn',
                'background' => 'red',
            ],
        ];

        return response()->json([
            'stackedCustomSeries' => $stackedCustomSeries
        ]);
    }

    function ViewTotalSalesForCurrentMonth() {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get total sales for the current month
        $totalSales = DB::table('sales')
            ->whereYear('updated_at', $currentYear)
            ->whereMonth('updated_at', $currentMonth)
            ->sum('Amount');

        // Detect database type
        $databaseConnection = config('database.default');
        $databaseDriver = config("database.connections.$databaseConnection.driver");

        // Define the first day of the month
        $firstDayOfMonth = Carbon::now()->startOfMonth()->toDateString();

        // Build the query for weekly sales
        if ($databaseDriver == 'mysql') {
            $weeklySales = DB::table('sales')
                ->select(DB::raw("
                    WEEK(updated_at, 1) - WEEK('$firstDayOfMonth', 1) + 1 as week,
                    SUM(Amount) as total_sales
                "))
                ->whereYear('updated_at', $currentYear)
                ->whereMonth('updated_at', $currentMonth)
                ->groupBy(DB::raw("WEEK(updated_at, 1) - WEEK('$firstDayOfMonth', 1) + 1"))
                ->get()
                ->keyBy('week')
                ->toArray();
        } else if ($databaseDriver == 'sqlsrv') {
            $weeklySales = DB::table('sales')
                ->select(DB::raw("
                    DATEPART(WEEK, updated_at) - DATEPART(WEEK, '$firstDayOfMonth') + 1 as week,
                    SUM(Amount) as total_sales
                "))
                ->whereYear('updated_at', $currentYear)
                ->whereMonth('updated_at', $currentMonth)
                ->groupBy(DB::raw("DATEPART(WEEK, updated_at) - DATEPART(WEEK, '$firstDayOfMonth') + 1"))
                ->get()
                ->keyBy('week')
                ->toArray();
        }

        $SparklineAreaData = [];
        for ($week = 1; $week <= 5; $week++) {
            $sales = isset($weeklySales[$week]) ? $weeklySales[$week]->total_sales : 0;
            $SparklineAreaData[] = ['x' => $week, 'yval' => (float)$sales];
        }

        return response()->json([
            'SparklineAreaData' => $SparklineAreaData,
            'totalSales' => (float)$totalSales
        ]);
    }




function ThisYearSales() {
    $currentYear = Carbon::now()->year;
    $currentYearSales = Sales::whereYear('updated_at', $currentYear)->sum('Amount');
    
    return response()->json(['thisYearSales' => $currentYearSales ]);
}



}
