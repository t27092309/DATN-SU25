<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cancel-unpaid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xoá đơn hàng chưa thanh toán (VNPAY) quá 10 phút và trả lại stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $this->info('Đang kiểm tra đơn hàng...');

        DB::transaction(function () {
            $orders = Order::where('status', 'pending_payment')
                ->where('created_at', '<=', Carbon::now()->subMinutes(10))
                ->whereHas('payment', function ($q) {
                    $q->where('payment_method', 'vnpay');
                })
                ->get();

            // $this->info("Tìm thấy " . $orders->count() . " đơn hàng cần xoá.");

            foreach ($orders as $order) {
                $order->delete();
            }
        });

        return Command::SUCCESS;
    }
}
