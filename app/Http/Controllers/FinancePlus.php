<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BudgetConfiguration;
use App\Models\TransactionMonitor;

class FinancePlus extends Controller
{


    public function BudgetConfiguration(Request $req){

        $s = new BudgetConfiguration();
        $s->Element = $req->Element;
        $s->Amount = $req->Amount;

        $saver = $s->save();
        if($saver){
            return response()->json(["message"=>"Budget Added Successfully"],200);
        }
        else{
            return response()->json(["message"=>"Failed to Add Budget"],400);
        }

    }

    public function ViewBudgets(Request $req){
        return BudgetConfiguration::get();
    }

    public function DeleteBudgets(Request $req){
        $s = BudgetConfiguration::where("id",$req->Id)->first();
        if(!$s){
            return response()->json(["message"=>"Budget does not exist "],400);
        }
        $saver = $s->delete();

        if($saver){
            return response()->json(["message"=>"Budget deleted Successfully"],200);
        }
        else{
            return response()->json(["message"=>"Failed to delete Budget"],400);
        }

    }

    public function CreateTransactionMonitor(Request $req){
        $s = new TransactionMonitor();
        $allCredit = TransactionMonitor::where("TransactionType", "Asset")->sum("Credit");
        $allDebit = TransactionMonitor::where("TransactionType", "Liability")->sum("Debit");
        $currentBalance = $allCredit - $allDebit;

        if($req->filled("TransactionType")){
            $s->TransactionType = $req->TransactionType;
        }

        if($req->filled("Narration")){
            $s->Narration = $req->Narration;
        }

        if($req->TransactionType == "Asset"){
            $s->Balance = $currentBalance + $req->Credit;
            $s->Credit = $req->Credit;
            $s->Debit = 0;
        }

        if($req->TransactionType == "Liability"){
            $s->Balance = $currentBalance - $req->Debit;
            $s->Debit = $req->Debit;
            $s->Credit = 0;
        }

        $saver = $s->save();
        if($saver){
            return response()->json(["message"=>"Transaction Added Successfully"],200);
        }
        else{
            return response()->json(["message"=>"Failed to Add Transaction"],400);
        }


    }

    public function ViewTransactionMonitor(Request $req){
        return TransactionMonitor::get();
    }

    public function generateComprehensiveFinancialReport(Request $req)
    {
        // Step 1: Profit and Loss Statement
        $totalRevenue = TransactionMonitor::where("TransactionType", "Asset")->sum("Credit");
        $totalExpenses = TransactionMonitor::where("TransactionType", "Liability")->sum("Debit");
        $netProfit = $totalRevenue - $totalExpenses;

        $profitAndLossStatement = [
            "Total Revenue" => [
                "value" => $totalRevenue,
                "interpretation" => "The total amount earned from all income-generating activities (assets) in the business. Aim to increase this by exploring new markets or enhancing product offerings."
            ],
            "Total Expenses" => [
                "value" => $totalExpenses,
                "interpretation" => "The total amount spent on expenses or costs (liabilities) to operate the business. Regularly review these costs to identify areas for reduction."
            ],
            "Net Profit" => [
                "value" => $netProfit,
                "interpretation" => "The difference between total revenue and total expenses. A positive net profit indicates a profitable business. Aim for a net profit margin of at least 20% as per industry standards."
            ]
        ];

        // Step 2: Financial Position
        $totalAssets = TransactionMonitor::where("TransactionType", "Asset")->sum("Credit");
        $totalLiabilities = TransactionMonitor::where("TransactionType", "Liability")->sum("Debit");
        $netWorth = $totalAssets - $totalLiabilities;

        $financialPosition = [
            "Total Assets" => [
                "value" => $totalAssets,
                "interpretation" => "The total amount of resources owned by the business. Compare this with total liabilities to assess overall financial stability."
            ],
            "Total Liabilities" => [
                "value" => $totalLiabilities,
                "interpretation" => "The total amount owed to creditors. If liabilities exceed assets, it indicates potential financial distress. Aim for liabilities to be below 50% of total assets."
            ],
            "Net Worth" => [
                "value" => $netWorth,
                "interpretation" => "The value of the business after all liabilities are deducted from assets. A growing net worth reflects business growth. Aim to increase this yearly."
            ]
        ];

        // Step 3: Cash Flow Statement
        $cashInflows = $totalAssets;
        $cashOutflows = $totalLiabilities;
        $netCashFlow = $cashInflows - $cashOutflows;

        $cashFlowStatement = [
            "Cash Inflows" => [
                "value" => $cashInflows,
                "interpretation" => "The total cash generated by the business's asset-related activities. Positive cash inflows should be a priority; consider strategies to boost sales."
            ],
            "Cash Outflows" => [
                "value" => $cashOutflows,
                "interpretation" => "The total cash spent by the business on liability-related activities. Monitor these outflows closely and identify non-essential expenses for reduction."
            ],
            "Net Cash Flow" => [
                "value" => $netCashFlow,
                "interpretation" => "The net result of cash inflows and outflows. A positive net cash flow indicates good liquidity. Aim for consistent positive cash flow to ensure operational stability."
            ]
        ];

        // Step 4: Financial Ratios
        $currentRatio = $totalAssets / max($totalLiabilities, 1);
        $debtRatio = $totalLiabilities / max($totalAssets, 1);
        $returnOnCapital = ($totalRevenue - $totalExpenses) / max($netWorth, 1);
        $profitMargin = $netProfit / max($totalRevenue, 1);
        $assetTurnover = $totalRevenue / max($totalAssets, 1);
        $workingCapitalEfficiency = ($totalAssets - $totalLiabilities) / max($totalAssets, 1);
        $liquidityRatio = ($totalAssets - $totalLiabilities) / max($totalLiabilities, 1);

        $financialRatios = [
            "Current Ratio" => [
                "value" => $currentRatio,
                "interpretation" => "A ratio above 1 indicates sufficient current assets to cover current liabilities. Industry standard is 1.5; consider improving this ratio for better financial health."
            ],
            "Debt Ratio" => [
                "value" => $debtRatio,
                "interpretation" => "A lower debt ratio is preferable; aim for below 0.5 to ensure assets are not overly financed by debt. This indicates lower financial risk."
            ],
            "Return on Capital" => [
                "value" => $returnOnCapital,
                "interpretation" => "Aim for a return on capital of at least 15%, indicating efficient use of capital. This shows how well the business is generating profits from invested capital."
            ],
            "Profit Margin" => [
                "value" => $profitMargin,
                "interpretation" => "Higher profit margins (20% or more) indicate effective cost control and pricing strategy. Consider reviewing pricing strategies to enhance profitability."
            ],
            "Asset Turnover Ratio" => [
                "value" => $assetTurnover,
                "interpretation" => "A ratio above 1 indicates efficient use of assets in generating sales. Aim to improve this to maximize revenue from existing assets."
            ],
            "Working Capital Efficiency Ratio" => [
                "value" => $workingCapitalEfficiency,
                "interpretation" => "A higher ratio indicates efficient use of working capital. Aim for efficiency ratios above 0.2 for optimal operations."
            ],
            "Liquidity Ratio" => [
                "value" => $liquidityRatio,
                "interpretation" => "Aim for a liquidity ratio above 1. This indicates the ability to pay short-term liabilities and maintain operational efficiency."
            ]
        ];

        // Step 5: Combine All Data into One JSON Response
        return response()->json([
            "Profit and Loss Statement" => $profitAndLossStatement,
            "Financial Position" => $financialPosition,
            "Cash Flow Statement" => $cashFlowStatement,
            "Financial Ratios" => $financialRatios
        ], 200);
    }





}
