<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Dispatch;
use App\Models\Goods;
use App\Models\ImportBatch;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\ClientPolicy;
use App\Policies\DispatchPolicy;
use App\Policies\GoodsPolicy;
use App\Policies\ImportPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\UserPolicy;
use App\Policies\VehiclePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Dispatch::class, DispatchPolicy::class);
        Gate::policy(Receipt::class, ReceiptPolicy::class);
        Gate::policy(Vehicle::class, VehiclePolicy::class);
        Gate::policy(Goods::class, GoodsPolicy::class);
        Gate::policy(Client::class, ClientPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(ImportBatch::class, ImportPolicy::class);
    }
}
