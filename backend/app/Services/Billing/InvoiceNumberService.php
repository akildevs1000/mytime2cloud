<?php

namespace App\Services\Billing;

use App\Models\InvoiceSequence;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function next(): string
    {
        return DB::transaction(function () {
            $row = InvoiceSequence::where('key', 'invoice')->lockForUpdate()->first();

            if (! $row) {
                $row = InvoiceSequence::create(['key' => 'invoice', 'last_number' => 0]);
                $row = InvoiceSequence::where('id', $row->id)->lockForUpdate()->first();
            }

            $row->last_number = $row->last_number + 1;
            $row->save();

            return sprintf('INV-%06d', $row->last_number);
        });
    }
}
