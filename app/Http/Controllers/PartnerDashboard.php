<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales;
use App\Models\Expenses;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Customers;
use App\Models\OurPortfolioProjects;
use App\Models\AuditTrial;
use Illuminate\Support\Facades\Log;
use App\Models\Visitors;

class PartnerDashBoard extends Controller
{


    function ViewTotalPendingSales(Request $req) {
        $totalSales = Sales::where("Created_By_Id", $req->AdminId)->
        sum('Amount');
        $share = 0.1*$totalSales;
        return response()->json(['sales' => $share]);
    }

    function ViewTotalApprovedSales(Request $req) {
        $totalSales = Sales::where("Created_By_Id", $req->AdminId)->
        where("IsApproved", true)->
        sum('Amount');
        $share = 0.1*$totalSales;
        return response()->json(['sales' => $share]);
    }

    function ViewPendingSales(Request $req) {
        // Ensure the AdminId is provided
        $adminId = $req->input('AdminId');

        if (!$adminId) {
            return response()->json(['error' => 'AdminId is required'], 400);
        }

        // Retrieve the pending sales
        $totalSales = Sales::where("Created_By_Id", $adminId)
            ->where("IsApproved", false)
            ->orderBy('updated_at', "desc")
            ->get();

        return response()->json($totalSales);
    }



    function ViewTotalExpenses() {
        $totalExpenses = Expenses::sum('Amount');
        return response()->json(['expenses' => $totalExpenses]);
    }

    function ViewTotalYearlySales() {
        $currentYear = Carbon::now()->year;
        $salesData = [];
        $totalSalesOver5Years = 0;

        // Step 1: Calculate total sales for each year and the total sales over 5 years
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            $totalSales = Sales::whereYear('updated_at', $year)->sum('Amount');
            $salesData[] = [
                'year' => $year,
                'amount' => $totalSales
            ];
            $totalSalesOver5Years += $totalSales;
        }

        // Step 2: Calculate the percentage for each year's sales
        foreach ($salesData as &$data) {
            $data['percentage'] = $totalSalesOver5Years > 0 ? ($data['amount'] / $totalSalesOver5Years) * 100 : 0;
        }

        return response()->json(['sales' => $salesData]);
    }


    public function ViewMonthlySalesAndExpenses(Request $req)
    {
        $currentYear = Carbon::now()->year;

        $adminId = $req->input('AdminId');

        if (!$adminId) {
            return response()->json(['message' => 'AdminId is required'], 400);
        }

        // Fetch monthly sales data for the current year
        $monthlySales = DB::table('sales')
            ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('SUM(Amount) as total_sales'))
            ->where("Created_By_Id", $adminId)
            ->where("IsApproved", true)
            ->whereYear('updated_at', $currentYear)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->get()
            ->keyBy('month')
            ->toArray();

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $stackedChartData = [];

        for ($i = 1; $i <= 12; $i++) {
            $sales = isset($monthlySales[$i]) ? $monthlySales[$i]->total_sales : 0;

            $stackedChartData[] = ['x' => $months[$i - 1], 'y' => 0.1 * (float)$sales];
        }

        $stackedCustomSeries = [
            [
                'dataSource' => $stackedChartData,
                'xName' => 'x',
                'yName' => 'y',
                'name' => 'Earnings',
                'type' => 'StackingColumn',
                'background' => 'blue',
            ],
        ];

        return response()->json([
            'stackedCustomSeries' => $stackedCustomSeries
        ]);
    }

    function ViewTotalSalesForCurrentMonth(Request $req) {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Ensure the AdminId is provided
        $adminId = $req->input('AdminId');

        if (!$adminId) {
            return response()->json(['message' => 'AdminId is required'], 400);
        }

        // Get total sales for the current month
        $totalSales = DB::table('sales')
            ->where("Created_By_Id", $adminId)
            ->where("IsApproved", true)
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
                ->where("Created_By_Id", $adminId)
                ->where("IsApproved", true)
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
                ->where("Created_By_Id", $adminId)
                ->where("IsApproved", true)
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
            $SparklineAreaData[] = ['x' => $week, 'yval' => 0.1*(float)$sales];
        }

        return response()->json([
            'SparklineAreaData' => $SparklineAreaData,
            'totalSales' => 0.1*(float)$totalSales
        ]);
    }



function ThisYearSales() {
    $currentYear = Carbon::now()->year;
    $currentYearSales = Sales::whereYear('updated_at', $currentYear)->sum('Amount');

    return response()->json(['thisYearSales' => $currentYearSales ]);
}

function TotalCustomers(){
    $c = Customers::Count();
    return response()->json(["customers"=>$c],200);
}

function EarningData() {
    $currentYear = Carbon::now()->year;
    $previousYear = $currentYear - 1;

    // 1. Count total customers from Customers table
    $totalCustomersCurrentYear = Customers::where("Created_By",$req->AdminId)->whereYear('created_at', $currentYear)->count();
    $totalCustomersPreviousYear = Customers::where("Created_By",$req->AdminId)->whereYear('created_at', $previousYear)->count();

    // 2. Count total products from OurPortfolioProjects table
    $totalProductsCurrentYear = OurPortfolioProjects::whereYear('created_at', $currentYear)->count();
    $totalProductsPreviousYear = OurPortfolioProjects::whereYear('created_at', $previousYear)->count();

    // 3. Sum current year sales
    $currentYearSales = Sales::whereYear('updated_at', $currentYear)->sum('Amount');

    // 4. Sum current year expenses
    $currentYearExpenses = Expenses::whereYear('updated_at', $currentYear)->sum('Amount');

    // Calculate the sales and expenses for the previous year to determine the percentage change
    $previousYearSales = Sales::whereYear('updated_at', $previousYear)->sum('Amount');
    $previousYearExpenses = Expenses::whereYear('updated_at', $previousYear)->sum('Amount');

    // Calculate percentage changes
    $customersPercentageChange = $totalCustomersPreviousYear > 0 ? (($totalCustomersCurrentYear - $totalCustomersPreviousYear) / $totalCustomersPreviousYear) * 100 : 100;
    $productsPercentageChange = $totalProductsPreviousYear > 0 ? (($totalProductsCurrentYear - $totalProductsPreviousYear) / $totalProductsPreviousYear) * 100 : 100;
    $salesPercentageChange = $previousYearSales > 0 ? (($currentYearSales - $previousYearSales) / $previousYearSales) * 100 : 100;
    $expensesPercentageChange = $previousYearExpenses > 0 ? (($currentYearExpenses - $previousYearExpenses) / $previousYearExpenses) * 100 : 100;

    // Determine increase or decrease
    $customersPercentage = ($customersPercentageChange >= 0 ? '+' : '') . number_format($customersPercentageChange, 2) . '%';
    $customersColor = $customersPercentageChange >= 0 ? 'green-600' : 'red-600';

    $productsPercentage = ($productsPercentageChange >= 0 ? '+' : '') . number_format($productsPercentageChange, 2) . '%';
    $productsColor = $productsPercentageChange >= 0 ? 'green-600' : 'red-600';

    $salesPercentage = ($salesPercentageChange >= 0 ? '+' : '') . number_format($salesPercentageChange, 2) . '%';
    $salesColor = $salesPercentageChange >= 0 ? 'green-600' : 'red-600';

    $expensesPercentage = ($expensesPercentageChange >= 0 ? '+' : '') . number_format($expensesPercentageChange, 2) . '%';
    $expensesColor = $expensesPercentageChange >= 0 ? 'red-600' : 'green-600';

    // Formatting amounts
    $formattedCurrentYearSales = number_format($currentYearSales, 2);
    $formattedCurrentYearExpenses = number_format($currentYearExpenses, 2);

    $earningData = [
        [
            'icon' => 'MdOutlineSupervisorAccount',
            'amount' => number_format($totalCustomersCurrentYear),
            'percentage' => $customersPercentage,
            'title' => 'Customers',
            'iconColor' => '#03C9D7',
            'iconBg' => '#E5FAFB',
            'pcColor' => $customersColor,
        ],


    ];

    return response()->json(['earningData' => $earningData]);
}


public function RecentTransaction(Request $req)
{
    // Fetch the admin ID from the request
    $adminId = $req->input('AdminId');

    if (!$adminId) {
        return response()->json(['message' => 'AdminId is required'], 400);
    }

    // Fetch recent sales data
    $salesData = Sales::select('updated_at as date', 'Amount as amount', 'PaymentReference as title')
        ->where("Created_By_Id", $adminId)
        ->orderBy('updated_at', 'desc')
        ->limit(5)
        ->get()
        ->toArray();

    // Process and format the sales data
    $formattedTransactions = array_map(function ($item) {
        return [
            'icon' => 'BsCurrencyDollar',
            'amount' => '+₵' . number_format(0.1 * $item['amount'], 2), // Adjust the amount as needed
            'title' => $item['title'],
            'desc' => 'Money Added',
            'iconColor' => '#03C9D7',
            'iconBg' => '#E5FAFB',
            'pcColor' => 'green-600',
        ];
    }, $salesData);

    return response()->json(['recentTransactions' => $formattedTransactions]);
}




public function YearlyContinent(Request $req)
{
    // Fetch the admin ID from the request
    $adminId = $req->input('AdminId');

    if (!$adminId) {
        return response()->json(['error' => 'AdminId is required'], 400);
    }

    // Fetch yearly sales data for the specified admin and approved status
    $yearlySales = Sales::selectRaw('YEAR(updated_at) as year, SUM(Amount) as total_sales')
        ->where("Created_By_Id", $adminId)
        ->where("IsApproved", true)
        ->groupByRaw('YEAR(updated_at)')
        ->orderByRaw('YEAR(updated_at) ASC')
        ->get()
        ->toArray();




    $salesByContinent = [];

    // Iterate over yearly sales data
    foreach ($yearlySales as $yearlySale) {
        $year = $yearlySale['year'];
        $totalSales = 0.1*intval($yearlySale['total_sales']); // Cast to integer

        // Fetch sales records for the current year, admin, and approved status
        $salesData = Sales::whereYear('updated_at', $year)
            ->where("Created_By_Id", $adminId)
            ->where("IsApproved", true)
            ->get();

        // Iterate over sales records to retrieve continent information
        foreach ($salesData as $sale) {
            // Find the customer associated with the sale
            $customer = Customers::where("UserId", $sale->CustomerId)->first();

            // If customer found, extract continent information
            if ($customer) {
                $continent = $customer->Continent;

                // Aggregate sales by continent for the current year
                if (!isset($salesByContinent[$continent])) {
                    $salesByContinent[$continent] = [];
                }

                // Store total sales for the current year and continent
                $salesByContinent[$continent][] = ['x' => mktime(0, 0, 0, 1, 1, $year), 'y' => $totalSales];
            }
        }
    }

    // Format the data as required
    $formattedData = [];

    foreach ($salesByContinent as $continent => $sales) {
        $formattedData[] = [
            'dataSource' => $sales, // Use sales data directly
            'xName' => 'x',
            'yName' => 'y',
            'name' => $continent,
            'width' => '2',
            'marker' => ['visible' => true, 'width' => 10, 'height' => 10],
            'type' => 'Line'
        ];
    }

    return response()->json($formattedData);
}


public function WeeklyStats()
{
    // Calculate the start and end of the current week (Monday to Sunday)
    $startOfWeek = Carbon::now()->startOfWeek()->format('Y-m-d');
    $endOfWeek = Carbon::now()->endOfWeek()->format('Y-m-d');

    // Query for the top seller (product with the highest sales revenue)
    $topSeller = Sales::select('Created_By_Id', 'Created_By_Name', DB::raw('SUM(amount) AS total_sales'))
        ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
        ->groupBy('Created_By_Id', 'Created_By_Name')
        ->orderByDesc('total_sales')
        ->first();

    // Query for the most viewed product
    $mostViewed = Sales::select('ProductName', DB::raw('COUNT(ProductName) AS total_views'))
        ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
        ->groupBy('ProductName')
        ->orderByDesc('total_views')
        ->first();

    // Query for the top engaged product (both high sales revenue and high number of views)
    $topEngaged = Sales::select('ProductName', DB::raw('SUM(amount) AS total_sales, COUNT(ProductName) AS total_views'))
        ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
        ->groupBy('ProductName')
        ->orderByDesc('total_sales')
        ->orderByDesc('total_views')
        ->first();

    // Format the data
    $weeklyStats = [
        [
            'icon' => 'FiShoppingCart', // Add appropriate icon
            'amount' => $topSeller ? "₵".number_format(0.1 * $topSeller->total_sales) : 'N/A',
            'title' => $topSeller ? $topSeller->Created_By_Name : 'N/A',
            'desc' => 'Top Seller of this week',
            'iconBg' => '#FB9678', // Add appropriate background color
            'pcColor' => 'green-600', // Add appropriate color
        ],
        [
            'icon' => 'GiSunkenEye', // Add appropriate icon
            'amount' => $mostViewed ? number_format($mostViewed->total_views) : 'N/A',
            'title' => $mostViewed ? $mostViewed->ProductName : 'N/A',
            'desc' => 'Most Viewed Product of this week',
            'iconBg' => 'rgb(254, 201, 15)', // Add appropriate background color
            'pcColor' => 'green-600', // Add appropriate color
        ],
        [
            'icon' => 'BsChatLeft', // Add appropriate icon
            'amount' => $topEngaged ? number_format($topEngaged->total_views) : 'N/A',
            'title' => $topEngaged ? $topEngaged->ProductName : 'N/A',
            'desc' => 'Most Engaging Product of this week',
            'iconBg' => '#00C292', // Add appropriate background color
            'pcColor' => 'green-600', // Add appropriate color
        ],
    ];

    return response()->json(['weeklyStats' => $weeklyStats]);
}


function TopCustomers() {
    // Query to fetch the top 3 customers with the highest amount paid in the Sales table
    $topCustomers = Sales::select('CustomerId', DB::raw('SUM(amount) AS total_amount'))
        ->groupBy('CustomerId')
        ->orderByDesc('total_amount')
        ->limit(5)
        ->get();

    // Initialize an array to store the portfolio stats data
    $portfolioStats = [];

    // Loop through the top customers
    foreach ($topCustomers as $customer) {
        // Fetch customer details from the Customer table based on CustomerId
        $customerDetails = Customers::select('UserId', 'Name', 'Picture')
            ->where('UserId', $customer->CustomerId)
            ->first();

        // Check if customer details are found
        if ($customerDetails) {
            // Push customer details to the portfolio stats array
            $portfolioStats[] = [
                'img' => $customerDetails->Picture, // Assuming Picture is the column name for the customer's image
                'amount' => number_format($customer->total_amount, 2), // Format amount
                'title' => $customerDetails->Name, // Assuming Name is the column name for the customer's name
                'pcColor' => 'green-600', // Add appropriate color
            ];
        }
    }

    // Return portfolio stats data
    return response()->json(['topCustomers' => $portfolioStats]);
}

function TopTrendingPortfolio() {
    // Query to fetch the top 5 customers with the highest amount paid in the Sales table
    $topCustomers = Sales::select('ProductId', DB::raw('SUM(amount) AS total_amount'))
        ->groupBy('ProductId')
        ->orderByDesc('total_amount')
        ->limit(5)
        ->get();

    // Initialize an array to store the portfolio stats data
    $portfolioStats = [];

    // Loop through the top customers
    foreach ($topCustomers as $customer) {
        // Fetch project details from the OurPortfolioProjects table based on ProductId
        $projectDetails = OurPortfolioProjects::select('ProjectId', 'Picture', 'ProjectName')
            ->where('ProjectId', $customer->ProductId)
            ->first();

        // Check if project details are found
        if ($projectDetails) {
            // Push project details to the portfolio stats array
            $portfolioStats[] = [
                'img' => $projectDetails->Picture, // Assuming Picture is the column name for the project's image
                'amount' => number_format($customer->total_amount, 2), // Format amount
                'title' => $projectDetails->ProjectName, // Assuming ProjectName is the column name for the project's name
                'pcColor' => 'green-600', // Add appropriate color
            ];
        }
    }

    // Return portfolio stats data
    return response()->json(['PortfolioStats' => $portfolioStats]);
}

function Auditing() {
    // Query to fetch audit trial records ordered by created_at in descending order
    $auditTrials = AuditTrial::orderByDesc('created_at')->get();

    // Return the audit trial records
    return response()->json(['auditTrials' => $auditTrials]);
}

function GetVisitors(){
    $v = Visitors::get();
    return response()->json(["visitors"=>$v],200);
}

function CountVisitors(){
    $v = Visitors::Count();
    return response()->json(["visitors"=>$v],200);
}

function CountCountryVisitors() {
    // Get today's and yesterday's date
    $today = Carbon::today()->toDateString();
    $yesterday = Carbon::yesterday()->toDateString();

    // Count today's visitors
    $todayVisitorsCount = DB::table('visitors')
        ->whereDate('created_at', $today)
        ->count();

    // Count yesterday's visitors
    $yesterdayVisitorsCount = DB::table('visitors')
        ->whereDate('created_at', $yesterday)
        ->count();

    // Calculate the percentage change for today's visitors
    if ($yesterdayVisitorsCount > 0) {
        $visitorPercentageChange = (($todayVisitorsCount - $yesterdayVisitorsCount) / $yesterdayVisitorsCount) * 100;
    } else {
        $visitorPercentageChange = $todayVisitorsCount > 0 ? 100 : 0;
    }

    // Count today's subscriptions from chats table where Purpose is "Subscriber"
    $todaySubscriptionsCount = DB::table('chats')
        ->whereDate('created_at', $today)
        ->where('purpose', 'Subscriber')
        ->count();

    // Count today's messages from chats table where Purpose is "Enquiry"
    $todayMessagesCount = DB::table('chats')
        ->whereDate('created_at', $today)
        ->where('purpose', 'Enquiry')
        ->count();

    // Count yesterday's subscriptions
    $yesterdaySubscriptionsCount = DB::table('chats')
        ->whereDate('created_at', $yesterday)
        ->where('purpose', 'Subscriber')
        ->count();

    // Count yesterday's messages
    $yesterdayMessagesCount = DB::table('chats')
        ->whereDate('created_at', $yesterday)
        ->where('purpose', 'Enquiry')
        ->count();

    // Calculate the percentage change for subscriptions
    if ($yesterdaySubscriptionsCount > 0) {
        $subscriptionsPercentageChange = (($todaySubscriptionsCount - $yesterdaySubscriptionsCount) / $yesterdaySubscriptionsCount) * 100;
    } else {
        $subscriptionsPercentageChange = $todaySubscriptionsCount > 0 ? 100 : 0;
    }

    // Calculate the percentage change for messages
    if ($yesterdayMessagesCount > 0) {
        $messagesPercentageChange = (($todayMessagesCount - $yesterdayMessagesCount) / $yesterdayMessagesCount) * 100;
    } else {
        $messagesPercentageChange = $todayMessagesCount > 0 ? 100 : 0;
    }

    // Count distinct active countries with visitors today
    $activeCountriesCountToday = DB::table('visitors')
        ->whereDate('created_at', $today)
        ->distinct('country')
        ->count('country');

    // Define the data array
    $data = [
        [
            'icon' => 'FaPeoplePulling',
            'amount' => strval($todayVisitorsCount),
            'percentage' => $visitorPercentageChange >= 0 ? '+' . number_format($visitorPercentageChange, 2) . '%' : number_format($visitorPercentageChange, 2) . '%',
            'title' => 'Today Visitors',
            'iconColor' => '#03C9D7',
            'iconBg' => '#E5FAFB',
            'pcColor' => $visitorPercentageChange >= 0 ? 'green-600' : 'red-600',
        ],
        [
            'icon' => 'FcGlobe',
            'amount' => strval($activeCountriesCountToday),
            'percentage' => '', // Percentage not provided in the sample data for Active Countries
            'title' => 'Active Countries',
            'iconColor' => 'rgb(255, 244, 229)',
            'iconBg' => 'rgb(254, 201, 15)',
            'pcColor' => '', // Percentage not provided in the sample data for Active Countries
        ],
        [
            'icon' => 'MdMarkEmailRead',
            'amount' => strval($todaySubscriptionsCount),
            'percentage' => $subscriptionsPercentageChange >= 0 ? '+' . number_format($subscriptionsPercentageChange, 2) . '%' : number_format($subscriptionsPercentageChange, 2) . '%',
            'title' => 'Today Subscriptions',
            'iconColor' => 'rgb(228, 106, 118)',
            'iconBg' => 'rgb(255, 244, 229)',
            'pcColor' => $subscriptionsPercentageChange >= 0 ? 'green-600' : 'red-600',
        ],
        [
            'icon' => 'IoChatbubbleEllipsesOutline',
            'amount' => strval($todayMessagesCount),
            'percentage' => $messagesPercentageChange >= 0 ? '+' . number_format($messagesPercentageChange, 2) . '%' : number_format($messagesPercentageChange, 2) . '%',
            'title' => 'Today Messages',
            'iconColor' => 'rgb(0, 194, 146)',
            'iconBg' => 'rgb(235, 250, 242)',
            'pcColor' => $messagesPercentageChange >= 0 ? 'green-600' : 'red-600',
        ]
    ];

    return response()->json($data, 200);
}



}
