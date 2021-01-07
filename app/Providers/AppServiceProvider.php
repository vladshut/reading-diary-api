<?php

namespace App\Providers;

use App\Rules\BaseRule;
use App\Rules\ContainsSubstring;
use App\Rules\CountryCode;
use App\Rules\DateUtc;
use App\Rules\Equals;
use App\Rules\Ip;
use App\Rules\LocaleIcu;
use App\Rules\Missing;
use App\Rules\Password;
use App\Rules\PhoneE164;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private $validationRules = [
        ContainsSubstring::class,
        CountryCode::class,
        PhoneE164::class,
        LocaleIcu::class,
        Password::class,
        Missing::class,
        DateUtc::class,
        Equals::class,
        Ip::class,
    ];

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
        Resource::withoutWrapping();
        ResourceCollection::withoutWrapping();
        $this->registerValidationRules();
    }

    private function registerValidationRules(): void
    {
        foreach ($this->validationRules as $class) {
            $alias = $class::getAlias();
            /** @var BaseRule $ruleObj */
            $ruleObj = $this->app->get($class);
            Validator::extend($alias, function ($attribute, $value, $parameters = [], $validator = null) use ($ruleObj) {
                return $ruleObj->passes($attribute, $value, $parameters, $validator);
            });
            Validator::replacer($alias, function ($message, $attribute, $rule, $parameters, $validator) use ($ruleObj) {
                return $ruleObj->replacer($message, $attribute, $rule, $parameters, $validator);
            });
        }
    }
}
