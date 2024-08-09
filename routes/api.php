<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminUserController;
use App\Http\Middleware\ApiAuthenticator;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\Websites;
use App\Http\Controllers\Finance;
use App\Http\Controllers\ClientApiController;
use App\Http\Controllers\APPS;
use App\Http\Controllers\DashBoard;
use App\Http\Controllers\AuditTrialController;
use App\Http\Controllers\GlobalPaymentController;
use App\Http\Controllers\PartnerDashBoard;
use App\Http\Controllers\PartnerAuthentication;
use App\Http\Controllers\PartnerController;

// Route for setting up the admin, accessible without authentication
Route::post('SetUpCreateAdmin', [AdminUserController::class, 'SetUpCreateAdmin']);
Route::post('LogIn', [AuthenticationController::class, 'LogIn']);
Route::post('VerifyToken', [AuthenticationController::class, 'VerifyToken']);

Route::post('ForgetPasswordStep1', [AuthenticationController::class, 'ForgetPasswordStep1']);
Route::post('ForgetPasswordStep2', [AuthenticationController::class, 'ForgetPasswordStep2']);
Route::post('Visitors', [AuditTrialController::class, 'Visitors']);
Route::post('UnLocker', [AdminUserController::class, 'UnLocker']);







Route::post('ViewHero', [Websites::class, 'ViewHero']);
Route::post('ViewWhatWeDo', [Websites::class, 'ViewWhatWeDo']);
Route::post('ViewOurDifferences', [Websites::class, 'ViewOurDifferences']);
Route::post('ViewOurProcess', [Websites::class, 'ViewOurProcess']);
Route::post('ViewOurPortfolioHeader', [Websites::class, 'ViewOurPortfolioHeader']);
Route::post('ViewOurPortfolioProjects', [Websites::class, 'ViewOurPortfolioProjects']);
Route::post('ViewOurClientsHeader', [Websites::class, 'ViewOurClientsHeader']);
Route::post('ViewOurClientsProjects', [Websites::class, 'ViewOurClientsProjects']);
Route::post('ViewTestimonials', [Websites::class, 'ViewTestimonials']);
Route::post('SendChat', [APPS::class, 'SendChat']);

//Payment Integration
Route::post('AddPayment', [GlobalPaymentController::class, 'AddPayment']);
Route::post('GetPayment', [GlobalPaymentController::class, 'GetPayment']);
Route::get('TestPayment', [GlobalPaymentController::class, 'TestPayment']);
Route::get('ConfirmPayment/{RefId}', [GlobalPaymentController::class, 'ConfirmPayment']);
Route::post('SchedulePayment', [GlobalPaymentController::class, 'SchedulePayment']);
Route::get('MakePayment/{TransactionId}', [GlobalPaymentController::class, 'MakePayment']);
Route::post('GetSpecificUnApprovedPayment', [GlobalPaymentController::class, 'GetSpecificUnApprovedPayment']);
Route::post('GenerateReceiptCode', [GlobalPaymentController::class, 'GenerateReceiptCode']);
Route::post('GenerateTransferCode', [GlobalPaymentController::class, 'GenerateTransferCode']);
Route::post('FinalPayment', [GlobalPaymentController::class, 'FinalPayment']);
Route::post('GetAllPaymentToClient', [GlobalPaymentController::class, 'GetAllPaymentToClient']);

//Partner Section
Route::post('PartnerLogIn', [PartnerAuthentication::class, 'PartnerLogIn']);
Route::post('PartnerVerifyToken', [PartnerAuthentication::class, 'PartnerVerifyToken']);
Route::post('CreatePartner', [PartnerController::class, 'CreatePartner']);
Route::post('PartnerForgetPasswordStep1', [PartnerAuthentication::class, 'PartnerForgetPasswordStep1']);
Route::post('PartnerForgetPasswordStep2', [PartnerAuthentication::class, 'PartnerForgetPasswordStep2']);
Route::post('PartnerUnLocker', [PartnerAuthentication::class, 'PartnerUnLocker']);









// Routes that require authentication
Route::middleware([ApiAuthenticator::class])->group(function () {













    Route::post('CreateAdmin', [AdminUserController::class, 'CreateAdmin']);
    Route::post('UpdateAdmin', [AdminUserController::class, 'UpdateAdmin']);
    Route::post('EditAdmin', [AdminUserController::class, 'EditAdmin']);
    Route::post('ViewSingleAdmin', [AdminUserController::class, 'ViewSingleAdmin']);
    Route::post('ViewAllAdmin', [AdminUserController::class, 'ViewAllAdmin']);
    Route::post('DeleteAdmin', [AdminUserController::class, 'DeleteAdmin']);
    Route::post('UnBlockAdmin', [AdminUserController::class, 'UnBlockAdmin']);
    Route::post('BlockAdmin', [AdminUserController::class, 'BlockAdmin']);


    Route::post('CreateEmployee', [EmployeeController::class, 'CreateEmployee']);
    Route::post('UpdateEmployee', [EmployeeController::class, 'UpdateEmployee']);
    Route::post('ViewSingleEmployee', [EmployeeController::class, 'ViewSingleEmployee']);
    Route::post('ViewAllEmployee', [EmployeeController::class, 'ViewAllEmployee']);
    Route::post('DeleteEmployee', [EmployeeController::class, 'DeleteEmployee']);

    Route::post('CreateCustomers', [CustomersController::class, 'CreateCustomers']);
    Route::post('UpdateCustomers', [CustomersController::class, 'UpdateCustomers']);
    Route::post('ViewSingleCustomers', [CustomersController::class, 'ViewSingleCustomers']);
    Route::post('ViewAllCustomers', [CustomersController::class, 'ViewAllCustomers']);
    Route::post('DeleteCustomers', [CustomersController::class, 'DeleteCustomers']);
    Route::post('ViewPartnerCustomers', [CustomersController::class, 'ViewPartnerCustomers']);




    Route::post('CreateHero', [Websites::class, 'CreateHero']);
    Route::post('CreateWhatWeDo', [Websites::class, 'CreateWhatWeDo']);
    Route::post('CreateOurDifferences', [Websites::class, 'CreateOurDifferences']);
    Route::post('CreateOurProcess', [Websites::class, 'CreateOurProcess']);
    Route::post('CreateOurPortfolioHeader', [Websites::class, 'CreateOurPortfolioHeader']);
    Route::post('CreateOurPortfolioProjects', [Websites::class, 'CreateOurPortfolioProjects']);
    Route::post('UpdateOurPortfolioProjects', [Websites::class, 'UpdateOurPortfolioProjects']);
    Route::post('DeleteOurPortfolioProjects', [Websites::class, 'DeleteOurPortfolioProjects']);
    Route::post('CreateOurClientsHeader', [Websites::class, 'CreateOurClientsHeader']);
    Route::post('CreateOurClientsProjects', [Websites::class, 'CreateOurClientsProjects']);
    Route::post('UpdateOurClientsProjects', [Websites::class, 'UpdateOurClientsProjects']);
    Route::post('DeleteOurClientsProjects', [Websites::class, 'DeleteOurClientsProjects']);
    Route::post('CreateTestimonials', [Websites::class, 'CreateTestimonials']);
    Route::post('UpdateTestimonials', [Websites::class, 'UpdateTestimonials']);
    Route::post('DeleteTestimonials', [Websites::class, 'DeleteTestimonials']);

    Route::post('SchedulePayment', [GlobalPaymentController::class, 'SchedulePayment']);

    Route::post('CreateSales', [Finance::class, 'CreateSales']);
    Route::post('AuthorizeSales', [Finance::class, 'AuthorizeSales']);
    Route::post('ViewSales', [Finance::class, 'ViewSales']);
    Route::post('ViewSalesDetails', [Finance::class, 'ViewSalesDetails']);

    Route::post('ViewOneSale', [Finance::class, 'ViewOneSale']);
    Route::post('RegenerateTransactionId', [Finance::class, 'RegenerateTransactionId']);

    Route::post('CreateExpenses', [Finance::class, 'CreateExpenses']);
    Route::post('ViewExpenses', [Finance::class, 'ViewExpenses']);



    Route::post('ConfigurePrice', [Finance::class, 'ConfigurePrice']);
     Route::post('GetAllPrice', [Finance::class, 'GetAllPrice']);
    Route::post('DeletePrice', [Finance::class, 'DeletePrice']);


    Route::post("CreateClientApiServerURL",[ClientApiController::class,'CreateClientApiServerURL']);
Route::post("UpdateClientApiServerURL",[ClientApiController::class,'UpdateClientApiServerURL']);
Route::post("ViewClientApiServerURL",[ClientApiController::class,'ViewClientApiServerURL']);
Route::post("DeleteClientApiServerURL",[ClientApiController::class,'DeleteClientApiServerURL']);
Route::post("ViewAllClientApiServerURL",[ClientApiController::class,'ViewAllClientApiServerURL']);


Route::post('CreateSchedular', [APPS::class, 'CreateSchedular']);
Route::post('UpdateSchedular', [APPS::class, 'UpdateSchedular']);
Route::post('DeleteSchedule', [APPS::class, 'DeleteSchedule']);
Route::post('GetSchedule', [APPS::class, 'GetSchedule']);
Route::post('ReplyTheChat', [APPS::class, 'ReplyTheChat']);
Route::post('GetChat', [APPS::class, 'GetChat']);
Route::post('GetOneEmail', [APPS::class, 'GetOneEmail']);
Route::post('GetOneReply', [APPS::class, 'GetOneReply']);


Route::post('ViewTotalSales', [DashBoard::class, 'ViewTotalSales']);
Route::post('ViewTotalExpenses', [DashBoard::class, 'ViewTotalExpenses']);
Route::post('ViewTotalYearlySales', [DashBoard::class, 'ViewTotalYearlySales']);
Route::post('ViewMonthlySalesAndExpenses', [DashBoard::class, 'ViewMonthlySalesAndExpenses']);
Route::post('ViewTotalSalesForCurrentMonth', [DashBoard::class, 'ViewTotalSalesForCurrentMonth']);
Route::post('ThisYearSales', [DashBoard::class, 'ThisYearSales']);
Route::post('TotalCustomers', [DashBoard::class, 'TotalCustomers']);
Route::post('EarningData', [DashBoard::class, 'EarningData']);
Route::post('RecentTransaction', [DashBoard::class, 'RecentTransaction']);
Route::post('YearlyContinent', [DashBoard::class, 'YearlyContinent']);
Route::post('WeeklyStats', [DashBoard::class, 'WeeklyStats']);
Route::post('TopCustomers', [DashBoard::class, 'TopCustomers']);
Route::post('TopTrendingPortfolio', [DashBoard::class, 'TopTrendingPortfolio']);
Route::post('Auditing', [DashBoard::class, 'Auditing']);
Route::post('GetVisitors', [DashBoard::class, 'GetVisitors']);
Route::post('CountVisitors', [DashBoard::class, 'CountVisitors']);
Route::post('CountCountryVisitors', [DashBoard::class, 'CountCountryVisitors']);


Route::post('ViewAdminHero', [Websites::class, 'ViewAdminHero']);
Route::post('ViewAdminWhatWeDo', [Websites::class, 'ViewAdminWhatWeDo']);
Route::post('ViewAdminOurDifferences', [Websites::class, 'ViewAdminOurDifferences']);
Route::post('ViewAdminOurProcess', [Websites::class, 'ViewAdminOurProcess']);
Route::post('ViewAdminOurPortfolioHeader', [Websites::class, 'ViewAdminOurPortfolioHeader']);




/*Partner Dashboard */

Route::post('ViewTotalSalesPD', [PartnerDashBoard::class, 'ViewTotalPendingSales']);
Route::post('ViewPendingSalesPD', [PartnerDashBoard::class, 'ViewPendingSales']);
Route::post('ViewTotalApprovedSales', [PartnerDashBoard::class, 'ViewTotalApprovedSales']);




Route::post('ViewTotalExpensesPD', [PartnerDashBoard::class, 'ViewTotalExpenses']);
Route::post('ViewTotalYearlySalesPD', [PartnerDashBoard::class, 'ViewTotalYearlySales']);
Route::post('ViewMonthlySalesAndExpensesPD', [PartnerDashBoard::class, 'ViewMonthlySalesAndExpenses']);
Route::post('ViewTotalSalesForCurrentMonthPD', [PartnerDashBoard::class, 'ViewTotalSalesForCurrentMonth']);
Route::post('ThisYearSalesPD', [PartnerDashBoard::class, 'ThisYearSales']);
Route::post('TotalCustomersPD', [PartnerDashBoard::class, 'TotalCustomers']);
Route::post('EarningDataPD', [PartnerDashBoard::class, 'EarningData']);
Route::post('RecentTransactionPD', [PartnerDashBoard::class, 'RecentTransaction']);
Route::post('YearlyContinentPD', [PartnerDashBoard::class, 'YearlyContinent']);
Route::post('WeeklyStatsPD', [PartnerDashBoard::class, 'WeeklyStats']);
Route::post('TopCustomersPD', [PartnerDashBoard::class, 'TopCustomers']);
Route::post('TopTrendingPortfolioPD', [PartnerDashBoard::class, 'TopTrendingPortfolio']);
Route::post('AuditingPD', [PartnerDashBoard::class, 'Auditing']);
Route::post('GetVisitorsPD', [PartnerDashBoard::class, 'GetVisitors']);
Route::post('CountVisitorsPD', [PartnerDashBoard::class, 'CountVisitors']);
Route::post('CountCountryVisitorsPD', [PartnerDashBoard::class, 'CountCountryVisitors']);



Route::post('UpdatePartner', [PartnerController::class, 'UpdatePartner']);
Route::post('EditPartner', [PartnerController::class, 'EditPartner']);
Route::post('ViewSinglePartner', [PartnerController::class, 'ViewSinglePartner']);
Route::post('ViewAllPartner', [PartnerController::class, 'ViewAllPartner']);
Route::post('DeletePartner', [PartnerController::class, 'DeletePartner']);
Route::post('UnBlockPartner', [PartnerController::class, 'UnBlockPartner']);
Route::post('BlockPartner', [PartnerController::class, 'BlockPartner']);









});
