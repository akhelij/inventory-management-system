<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoicePdfLayoutTest extends TestCase
{
    #[Test]
    public function invoice_pdf_keeps_its_page_margins(): void
    {
        $this->assertPageMarginsApplied('orders.pdf-invoice', ['order' => $this->makeOrder()]);
    }

    #[Test]
    public function bulk_invoice_pdf_keeps_its_page_margins(): void
    {
        $this->assertPageMarginsApplied('orders.pdf-bulk-invoice', ['orders' => collect([$this->makeOrder()])]);
    }

    private function assertPageMarginsApplied(string $view, array $data): void
    {
        $dompdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait')->getDomPDF();
        $dompdf->render();

        // dompdf derives the page box from the root element's style, so a CSS reset
        // that matches <html> silently zeroes the @page margins and content runs to
        // the paper edge (and, on the invoice, underneath the fixed footer).
        $page = $dompdf->getTree()->get_root()->get_style();

        $this->assertGreaterThan(0, $page->margin_top);
        $this->assertGreaterThan(0, $page->margin_bottom);
    }

    private function makeOrder(): Order
    {
        $product = (new Product)->forceFill(['code' => 'REF-001', 'name' => 'Réfrigérateur LG']);
        $detail = (new OrderDetails)->forceFill(['quantity' => 2, 'unitcost' => 100, 'total' => 200]);

        return (new Order)
            ->forceFill([
                'invoice_no' => 'INV-000001',
                'order_date' => now(),
                'total' => 200,
                'pay' => 0,
                'due' => 200,
            ])
            ->setRelation('details', collect([$detail->setRelation('product', $product)]))
            ->setRelation('customer', (new Customer)->forceFill(['name' => 'Client']))
            ->setRelation('user', (new User)->forceFill(['name' => 'Vendeur']))
            ->setRelation('payments', collect());
    }
}
