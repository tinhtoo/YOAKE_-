<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\MT99Msg;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        View::share('error_messages', $this->getMessage());
        Schema::defaultStringLength(191);
    }

    private function getMessage()
    {
        $messages = MT99Msg::all()->pluck('MSG_CONT', 'MSG_NO');
        return $messages;
    }
}
