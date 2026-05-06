<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        $headers = [
            'name' => [
                'id' => "ID",
                'scan_id' => "Scan ID",
                'transaction' => "Transaction Details",
                'created_at' => "Created At",
                'updated_at' => "Updated At",
            ],
            'type' => [
                'id' =>  'numeric',
                'amount_left' => "numeric",
                'created_at' => "datetime",
                'updated_at' =>  "datetime"
            ],
            'dropdown' => []
        ];
        $data = Transaction::all();

        return Inertia::render('Transactions', [
            'data' => $data,
            'headers' => $headers,
            'dropdownOptions' => []
        ]);
    }
}
