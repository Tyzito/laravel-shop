<?php

namespace App\Providers;

use App\Http\ViewComposers\CategoryTreeComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Elasticsearch\ClientBuilder as ESClientBuilder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay',function (){
            $config = config('pay.alipay');
            //$config['notify_url'] = route('payment.alipay.notify');
            $config['notify_url'] = ngrok_url('payment.alipay.notify');
            //$config['notify_url'] = 'https://requestbin.leo108.com/1mj9wsa1';
            $config['return_url'] = route('payment.alipay.return');
            // 判断当前项目运行环境是否为线上环境
            if(app()->environment() != 'production'){
                $config['mode'] = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }
            // 调用 Yansongda\Pay 来创建一个支付宝对象
            return Pay::alipay($config);
        });

        // 往服务容器注入一个名为 wechat_pay 的单例对象
        $this->app->singleton('wechat_pay',function (){
            $config = config('pay.wechat');
            $config['notify_url'] = ngrok_url('payment.wechat.notify');
            //$config['notify_url'] = route('payment.wechat.notify');
            //$config['notify_url'] = 'https://requestbin.leo108.com/1mj9wsa1';  // requestbin 来捕获
            if(app()->environment() != 'production'){
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::wechat($config);
        });

        // 注册一个名为 es 的单例
        $this->app->singleton('es', function (){
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));

            // 如果是开发环境
            if(app()->environment() === 'local'){
                $builder->setLogger(app('log')->driver());
            }

            return $builder->build();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer(['products.index', 'products.show'], CategoryTreeComposer::class);
    }
}
