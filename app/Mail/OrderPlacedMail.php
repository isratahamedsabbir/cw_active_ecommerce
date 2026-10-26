<?php

namespace App\Mail;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Order;
use Config;
use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use PDF;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $currency_code = Currency::findOrFail(get_setting('system_default_currency'))->code;
        $language_code = Config::get('app.locale');

        $lang = Language::where('code', $language_code)->first();
        if ($lang && $lang->rtl == 1) {
            $direction = 'rtl';
            $text_align = 'right';
            $not_text_align = 'left';
        } else {
            $direction = 'ltr';
            $text_align = 'left';
            $not_text_align = 'right';
        }

        if ($currency_code == 'BDT' || $language_code == 'bd') {
            $font_family = "'Hind Siliguri','freeserif'";
        } elseif ($currency_code == 'KHR' || $language_code == 'kh') {
            $font_family = "'Hanuman','sans-serif'";
        } elseif ($currency_code == 'AMD') {
            $font_family = "'arnamu','sans-serif'";
        } elseif (
            $currency_code == 'AED' || $currency_code == 'EGP' || $language_code == 'sa' ||
            $currency_code == 'IQD' || $language_code == 'ir' || $language_code == 'om' ||
            $language_code == 'jo' || $currency_code == 'ROM' || $currency_code == 'SDG' ||
            $currency_code == 'ILS'
        ) {
            $font_family = "xbriyaz";
        } elseif ($currency_code == 'THB') {
            $font_family = "'Kanit','sans-serif'";
        } elseif ($currency_code == 'CNY' || $language_code == 'zh') {
            $font_family = "'sun-exta','gb'";
        } elseif ($currency_code == 'MMK' || $language_code == 'mm') {
            $font_family = 'tharlon';
        } elseif ($currency_code == 'THB' || $language_code == 'th') {
            $font_family = "'zawgyi-one','sans-serif'";
        } else {
            $font_family = "'Roboto','sans-serif'";
        }

        $pdf = PDF::loadView('backend.invoices.invoice', [
            'order' => $this->order,
            'font_family' => $font_family,
            'direction' => $direction,
            'text_align' => $text_align,
            'not_text_align' => $not_text_align
        ]);

        return $this->subject('Order Confirmation')
            ->view('emails.order-placed')
            ->attachData($pdf->output(), 'order-' . $this->order->code . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
