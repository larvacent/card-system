<?php
namespace App; use App\Jobs\OrderSms; use App\Library\LogHelper; use App\Mail\OrderShipped; use Illuminate\Database\Eloquent\Model; use Illuminate\Support\Facades\Mail; use Illuminate\Support\Facades\Log as LogWriter; class Order extends Model { protected $guarded = array(); const STATUS_UNPAY = 0; const STATUS_PAID = 1; const STATUS_SUCCESS = 2; const STATUS_FROZEN = 3; const STATUS_REFUND = 4; const STATUS = array(0 => '未支付', 1 => '未发货', 2 => '已发货', 3 => '已冻结', 4 => '已退款'); const SEND_STATUS_UN = 0; const SEND_STATUS_EMAIL_SUCCESS = 1; const SEND_STATUS_EMAIL_FAILED = 2; const SEND_STATUS_MOBILE_SUCCESS = 3; const SEND_STATUS_MOBILE_FAILED = 4; const SEND_STATUS_CARD_UN = 100; const SEND_STATUS_CARD_PROCESSING = 101; const SEND_STATUS_CARD_SUCCESS = 102; const SEND_STATUS_CARD_FAILED = 103; protected $casts = array('api_info' => 'array'); public static function unique_no() { $spd10b1a = date('YmdHis') . str_random(5); while (\App\Order::where('order_no', $spd10b1a)->exists()) { $spd10b1a = date('YmdHis') . str_random(5); } return $spd10b1a; } function user() { return $this->belongsTo(User::class); } function product() { return $this->belongsTo(Product::class); } function pay() { return $this->belongsTo(Pay::class); } function cards() { $sp937c8f = $this->belongsToMany(Card::class); return $sp937c8f->withTrashed(); } function card_orders() { return $this->hasMany(CardOrder::class); } function fundRecord() { return $this->hasMany(FundRecord::class); } function getCardsArray() { $spf7b822 = array(); $this->cards->each(function ($sp3fbb89) use(&$spf7b822) { $spf7b822[] = $sp3fbb89->card; }); return $spf7b822; } function getSendMessage() { if (count($this->cards)) { if (count($this->cards) == $this->count) { $sp32fb2d = '订单#' . $this->order_no . '&nbsp;已支付，您购买的内容如下：'; } else { if ($this->cards[0]->type === \App\Card::TYPE_REPEAT || @$this->product->delivery === \App\Product::DELIVERY_MANUAL) { $sp32fb2d = '订单#' . $this->order_no . '&nbsp;已支付，您购买的内容如下：'; } else { $sp32fb2d = '订单#' . $this->order_no . '&nbsp;已支付，目前库存不足，您还有' . ($this->count - count($this->cards)) . '件未发货，请联系商家客服发货<br>已发货商品见下方：<br>'; $sp32fb2d .= '<a href="http://wpa.qq.com/msgrd?v=3&uin=' . $this->user->qq . '&site=qq&menu=yes" target="_blank">商家客服QQ:' . $this->user->qq . '</a><br>'; } } } else { $sp32fb2d = '订单#' . $this->order_no . '&nbsp;已支付，目前库存不足，您购买的' . ($this->count - count($this->cards)) . '件未发货，请联系商家客服发货<br>'; $sp32fb2d .= '<a href="http://wpa.qq.com/msgrd?v=3&uin=' . $this->user->qq . '&site=qq&menu=yes" target="_blank">商家客服QQ:' . $this->user->qq . '</a><br>'; } return $sp32fb2d; } function sendEmail($sp030dc3 = false) { if ($sp030dc3 === false) { $sp030dc3 = @json_decode($this->contact_ext)['_mail']; } if (!$sp030dc3 || !@filter_var($sp030dc3, FILTER_VALIDATE_EMAIL)) { return; } $spf7b822 = $this->getCardsArray(); try { Mail::to($sp030dc3)->Queue(new OrderShipped($this, $this->getSendMessage(), join('<br>', $spf7b822))); $this->send_status = \App\Order::SEND_STATUS_EMAIL_SUCCESS; $this->saveOrFail(); } catch (\Throwable $sp96dd17) { $this->send_status = \App\Order::SEND_STATUS_EMAIL_FAILED; $this->saveOrFail(); LogHelper::setLogFile('mail'); LogWriter::error('Order.sendEmail error', array('order_no' => $this->order_no, 'email' => $sp030dc3, 'cards' => $spf7b822, 'exception' => $sp96dd17->getMessage())); LogHelper::setLogFile('card'); } } function sendSms($spe34083 = false) { if ($spe34083 === false) { $spe34083 = @json_decode($this->contact_ext)['_mobile']; } if (!$spe34083 || strlen($spe34083) !== 11) { return; } OrderSms::dispatch($spe34083, $this); } }