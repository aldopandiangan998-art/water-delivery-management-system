<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'this_month');

        $queryStart = match ($period) {
            'today' => Carbon::today(),
            'this_week' => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_year' => Carbon::now()->startOfYear(),
            default => Carbon::now()->startOfMonth(),
        };

        // Revenue from completed paid orders
        $totalRevenue = Order::where('payment_status', 'paid')
            ->where('created_at', '>=', $queryStart)
            ->sum('total_amount');

        // Unpaid receivables
        $unpaidAmount = Order::where('payment_status', 'unpaid')
            ->where('created_at', '>=', $queryStart)
            ->sum('total_amount');

        // Total Expenses
        $totalExpenses = Expense::where('date', '>=', $queryStart->toDateString())->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;

        $expenses = Expense::where('date', '>=', $queryStart->toDateString())
            ->with('recorder')
            ->latest('date')
            ->paginate(15);

        $payments = Payment::with(['order.user', 'receiver'])
            ->where('created_at', '>=', $queryStart)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.finance.index', compact('totalRevenue', 'unpaidAmount', 'totalExpenses', 'netProfit', 'expenses', 'payments', 'period'));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'in:stock,fuel,vehicle_maintenance,salary,utilities,other'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'receipt' => ['nullable', 'image', 'max:2048'],
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expenseNumber = 'EXP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

        Expense::create([
            'expense_number' => $expenseNumber,
            'category' => $validated['category'],
            'title' => $validated['title'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'recorded_by' => Auth::id(),
        ]);

        return back()->with('success', 'Catatan pengeluaran operasional berhasil disimpan.');
    }

    public function destroyExpense(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Catatan pengeluaran berhasil dihapus.');
    }
}
