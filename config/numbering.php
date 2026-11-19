<?php

/*
|--------------------------------------------------------------------------
| Document numbering defaults
|--------------------------------------------------------------------------
|
| Used when a company has no number_sequences row yet for a document type.
| Companies can override prefix, format and reset period in Settings.
| Tokens: {PREFIX} {YYYY} {YY} {MM} {DD} {SEQ} {SEQ:n}
|
*/

$yearly = static fn (string $prefix): array => [
    'prefix' => $prefix,
    'format' => '{PREFIX}-{YYYY}-{SEQ:4}',
    'padding' => 4,
    'reset_period' => 'yearly',
];

return [
    'defaults' => [
        // Core
        'approval_request' => $yearly('APR'),

        // CRM
        'lead' => $yearly('LEAD'),
        'deal' => $yearly('DEAL'),
        'ticket' => $yearly('TKT'),

        // Sales
        'customer' => ['prefix' => 'CUS', 'format' => '{PREFIX}-{SEQ:5}', 'padding' => 5, 'reset_period' => 'never'],
        'quotation' => $yearly('QT'),
        'sales_order' => $yearly('SO'),
        'delivery_note' => $yearly('DN'),
        'invoice' => $yearly('INV'),
        'payment' => $yearly('PAY'),
        'credit_note' => $yearly('CN'),
        'sales_return' => $yearly('SR'),

        // Purchase
        'vendor' => ['prefix' => 'VEN', 'format' => '{PREFIX}-{SEQ:5}', 'padding' => 5, 'reset_period' => 'never'],
        'purchase_requisition' => $yearly('PR'),
        'rfq' => $yearly('RFQ'),
        'purchase_order' => $yearly('PO'),
        'goods_receipt' => $yearly('GRN'),
        'vendor_bill' => $yearly('BILL'),
        'vendor_payment' => $yearly('VPAY'),
        'debit_note' => $yearly('DBN'),

        // Inventory
        'stock_transfer' => $yearly('TRF'),
        'stock_adjustment' => $yearly('ADJ'),
        'stock_count' => $yearly('CNT'),

        // Accounting
        'journal_entry' => $yearly('JE'),
        'expense' => $yearly('EXP'),

        // HR
        'employee' => ['prefix' => 'EMP', 'format' => '{PREFIX}-{SEQ:4}', 'padding' => 4, 'reset_period' => 'never'],
        'payroll_run' => $yearly('PRUN'),
        'payslip' => $yearly('PSL'),

        // Projects
        'project' => ['prefix' => 'PRJ', 'format' => '{PREFIX}-{SEQ:4}', 'padding' => 4, 'reset_period' => 'never'],

        // Manufacturing / POS
        'work_order' => $yearly('WO'),
        'production_plan' => $yearly('PP'),
        'pos_order' => ['prefix' => 'POS', 'format' => '{PREFIX}-{YYYY}{MM}{DD}-{SEQ:4}', 'padding' => 4, 'reset_period' => 'monthly'],
    ],
];
