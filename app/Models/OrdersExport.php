<?php

namespace App\Models;

use App\Traits\PreventDemoModeChanges;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class OrdersExport implements FromCollection, WithMapping, WithHeadings, WithCustomValueBinder
{
    use PreventDemoModeChanges;

    protected $order_ids;

    public function __construct($order_ids)
    {
        $this->order_ids = $order_ids;
    }

    public function collection()
    {
        return Order::with('orderDetails.product')->findMany($this->order_ids);
    }

    public function headings(): array
    {
        return [
            'Order Code',
            'Num. of Products',
            'Products',
            'customer_name',
            'customer_email',
            'customer_phone',
            'Seller',
            'Delivery Charge',
            'Discount',
            'Total Amount',
            'Delivery Status',
            'Payment method',
            'Payment Status',
            'Create Date',
        ];
    }

    /**
    * @var Order  $order
    */
    public function map($order): array
    {
        return [
            (string) $order->code,
            count($order->orderDetails),
            $this->productsSummary($order),
            $order->user != null ? $order->user->name : '',
            $order->user != null ? $order->user->email : '',
            $order->orderAddress ? $order->orderAddress->phone : ($order->user ? $order->user->phone : ''),
            $order->shop != null ? $order->shop->name : translate('Inhouse Order'),
            single_price($order->shipping_cost ?? 0),
            single_price(($order->coupon_discount ?? 0) + ($order->admin_discount ?? 0)),
            single_price($order->grand_total),
            translate(ucfirst(str_replace('_', ' ', $order->delivery_status))),
            translate(ucfirst(str_replace('_', ' ', $order->payment_type))),
            translate(ucfirst($order->payment_status)),
            $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
        ];
    }

    protected function productsSummary($order): string
    {
        return $order->orderDetails->map(function ($detail) {
            $name = $detail->product ? $detail->product->getTranslation('name') : 'Deleted Product';
            if ($detail->variation) {
                $name .= ' (' . $detail->variation . ')';
            }
            return $name . ' x' . $detail->quantity . ' - ' . single_price($detail->price);
        })->implode(', ');
    }

    public function bindValue(Cell $cell, $value)
    {
        $cell->setValueExplicit($value, DataType::TYPE_STRING);

        return true;
    }
}