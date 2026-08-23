<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GenerateInvoicePdfAction;
use App\Models\Order;
use App\Models\OrderItem;
use Barryvdh\DomPDF\PDF as PdfInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateInvoicePdfActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateInvoicePdfAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateInvoicePdfAction::class);
    }

    private function makeOrder(): Order
    {
        $order = Order::create([
            'order_number' => 'FKX-1001',
            'customer_name' => 'Іван',
            'customer_email' => 'ivan@example.com',
            'customer_phone' => '+380501112233',
            'shipping_address' => 'вул. Хрещатик, 1',
            'delivery_method' => 'nova_poshta',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'pending',
            'total_price' => 200,
            'discount_amount' => 0,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'Товар',
            'sku' => 'sku-1',
            'price' => 100,
            'quantity' => 2,
        ]);

        return $order;
    }

    public function test_execute_returns_a_pdf_instance_rendering_the_orders_invoice(): void
    {
        $order = $this->makeOrder();

        $pdf = $this->action->execute($order);

        $this->assertInstanceOf(PdfInstance::class, $pdf);
        $output = $pdf->output();
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output);
    }
}
