# 独角数卡接入VouCash
独角数卡 VouCash支付插件 支持USDT，关于 [VouCash](https://github.com/voucash/voucash)

### 设置
```
1. 添加代码
    下载app/Http/Controllers/Pay/VouCashController.php，并上传到相应目录
    修改routes/common/pay.php，添加以下代码

    Route::get('voucash/{payway}/{orderSN}', 'VouCashController@gateway');
    Route::post('voucash/notify_url', 'VouCashController@notifyUrl');
    Route::get('voucash/return_url', 'VouCashController@returnUrl')->name('voucash-return');
    
2. 添加 VouCash 支付方式，如图示
```
![独角数卡支付设置](https://raw.githubusercontent.com/voucash/learncoins/master/images/voucash.png)


### 兑现
1. 用户支付后，销售数据 > 订单列表 > 详情

![独角数卡支付成功](https://raw.githubusercontent.com/voucash/learncoins/master/images/voucash_2.png)

2. 复制回调单号到 [VouCash提现](https://voucash.com/cn/redeem)

## 有问题和合作可以小飞机联系我们
 - telegram：[@voucash](https://t.me/voucash)