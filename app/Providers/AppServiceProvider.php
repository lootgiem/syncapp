<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerMacro();
    }


    protected function registerMacro()
    {
        Str::macro('urlEncode', function ($string) {
            return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
        });

        Str::macro('urlDecode', function ($string) {
            return base64_decode(str_pad(strtr($string, '-_', '+/'), strlen($string) % 4, '=', STR_PAD_RIGHT));
        });

        Collection::macro('diffOnKeys', function (Collection $collection, array $keys = []) {
            $diff = new static;

            foreach ($this->items as $item) {
                $filteredCollection = $collection;

                foreach ($keys as $key) {
                    $filteredCollection = $filteredCollection->where($key, $item[$key]);
                }

                if ($filteredCollection->count() == 0) {
                    $diff->add($item);
                }
            }
            return $diff;
        });

        Collection::macro('withoutEvents', function (Collection $collection, $comparedProperties = null) {
            $comparedProperties = $comparedProperties ?? config('synchronization.compared_properties');
            return $this->diffOnKeys($collection, $comparedProperties);
        });

        Collection::macro('sharedWith', function (Collection $collection, $comparedProperties = null) {
            $mask = $this->diffOnKeys($collection, $comparedProperties);
            return $this->diff($mask);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
