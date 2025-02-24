<?php

namespace App\Http\Controllers\Pay;


use App\Exceptions\RuleValidationException;
use App\Http\Controllers\PayController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class VouCashController extends PayController
{
    public function gateway(string $payway, string $orderSN)
    {
        $this->loadGateWay($orderSN, $payway);
        $amount = $this->order->actual_price;
        $order_id = $this->order->order_sn;
        $notify_url = url($this->payGateway->pay_handleroute . '/notify_url');
        $url = "https://voucash.com/api/payment?amount=$amount&order_id=$order_id&currency=CNY&notify_url=$notify_url";
        return redirect()->away($url);
    }


    public function notifyUrl(Request $request)
    {
        $data = $request->all();
        $order = $this->orderService->detailOrderSN($data['order_id']);
        if (!$order) {
            return 'fail1';
        }
        $payGateway = $this->payService->detail($order->pay_id);
        if (!$payGateway) {
            return 'fail2';
        }
        if($payGateway->pay_handleroute != 'pay/voucash'){
            return 'fail3';
        }

        $raw_post_data = file_get_contents('php://input');
        @file_put_contents('/tmp/voucash.log', $raw_post_data);
        $ch = curl_init("https://voucash.com/api/payment/verify");
    
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $raw_post_data);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        // curl_setopt($ch, CURLOPT_CAINFO, $tmpfile);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        $http_code = $info['http_code'];
    
    
        if ( ! ($res)) {
            $errno = curl_errno($ch);
            $errstr = curl_error($ch);
            curl_close($ch);
            echo "connect error";
        }
    
        
        if ($http_code != 200) {
            curl_close($ch);
            echo "server response error";
        }
    
        curl_close($ch);
    
        if ($res == "verified") {
            $this->orderProcessService->completedOrder($data['order_id'], $data['amount'], $data['voucher']);
            return 'ok';
        }

        return "fail";
    }

    public function returnUrl(Request $request)
    {
        $oid = $request->get('order_id');
        // 异步通知还没到就跳转了，所以这里休眠2秒
        sleep(2);
        return redirect(url('detail-order-sn', ['orderSN' => $oid]));
    }

}
