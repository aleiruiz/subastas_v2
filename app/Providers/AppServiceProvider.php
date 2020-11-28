<?php

namespace App\Providers;

use App\Repositories\Core\Interfaces\ApplicationSettingInterface;
use App\Services\LanguageService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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
        if (env("APP_PROTOCOL", 'http') == 'https') {
            URL::forceScheme('https');
        }

        Validator::extend('hash_check', function ($attribute, $value, $parameters) {
            return $value == null ? true : Hash::check($value, $parameters[0]);
        });

        Validator::extend('digits_only', function ($attribute, $value, $parameters) {
            return $value == null ? true : ctype_digit($value);
        });

        Validator::extend('date_gt', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);

            if ($value == null) {
                return true;
            }

            $validator->addReplacer('date_gt', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] > $data[1];
        });

        Validator::extend('date_gte', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);
            if ($value == null) {
                return true;
            }

            $validator->addReplacer('date_gte', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] >= $data[1];
        });

        Validator::extend('date_lt', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);
            if ($value == null) {
                return true;
            }

            $validator->addReplacer('date_lt', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] < $data[1];
        });

        Validator::extend('date_lte', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);
            if ($value == null || !$data) {
                return true;
            }

            $validator->addReplacer('date_lte', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] <= $data[1];
        });

        Validator::extend('date_neq', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);
            if ($value == null) {
                return true;
            }

            $validator->addReplacer('date_neq', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] != $data[1];
        });

        Validator::extend('date_eq', function ($attribute, $value, $parameters, $validator) {
            $data = $this->dateComparison($attribute, $value, $parameters);
            if ($value == null) {
                return true;
            }

            $validator->addReplacer('date_eq', function ($message, $attribute, $rule, $parameters) use($validator) {
                $replaceString = isset($validator->customAttributes[$parameters[0]]) ? $validator->customAttributes[$parameters[0]] : str_replace('_', ' ', $parameters[0]);
                return str_replace([':other'], $replaceString, $message);
            });
            return $data[0] == $data[1];
        });

        Validator::extend('alpha_space', function ($attribute, $value) {
            if ($value == null) {
                return true;
            }
            return is_string($value) && preg_match('/^[\pL\s]+$/u', $value);
        });

        Validator::extend('slug', function ($attribute, $value) {
            if ($value == null) {
                return true;
            }
            return is_string($value) && preg_match('/^[\pL\pM\pN-]+$/u', $value);
        });

        Blade::directive('basekey', function () {
            return '<?php echo \'<input type="hidden" value="\'.base_key().\'" name="base_key">\'  ?>';
        });

        $this->app->singleton(LanguageService::class, function () {
            return new LanguageService(
                new Filesystem,
                $this->app['path.lang'],
                [$this->app['path.resources'], $this->app['path']]
            );
        });

        if (function_exists('bcscale')) {
            bcscale(2);
        }
        Schema::defaultStringLength(191);

        //Cache admin settings
        $this->loadApplicationSettings();
    }

    private function loadApplicationSettings()
    {
        $applicationSettings = settings();
        if (empty($applicationSettings)) {
            try{
                $applicationSettings = $this->app->make(ApplicationSettingInterface::class)->getAll();
                $applicationSettings = $applicationSettings->pluck('value', 'slug')->toArray();
                foreach ($applicationSettings as $key => $val) {
                    if (is_json($val)) {
                        $applicationSettings[$key] = json_decode($val, true);
                    }
                }
                Cache::forever('application_settings', $applicationSettings);
            }
            catch (\Exception $exception){
                return false;
            }
        }
    }

    private function dateComparison($attribute, $value, $parameters){
        $otherFieldValue = \Request::get($parameters[0]);
        $currentInputNameParts = explode('.',$attribute);
        if(count($currentInputNameParts)>1){
            $otherInputPartName = explode('.', $parameters[0]);
            if(count($otherInputPartName)>1){
                $otherFieldValue = \Request::get($otherInputPartName[0]);
                foreach($otherInputPartName as $key=>$inputValue){
                    if($key!=0){
                        if($inputValue=='*'){
                            $otherFieldValue = $otherFieldValue[$currentInputNameParts[$key]];
                        }
                        else{
                            $otherFieldValue = $otherFieldValue[$inputValue];
                        }
                    }
                }
            }
        }

        $thisFormat = isset($parameters[1]) ? $parameters[1] : 'Y-m-d H:i:s';
        $otherFormat = isset($parameters[2]) ? $parameters[2] : $thisFormat;
        try{
            $thisValue= Carbon::createFromFormat($thisFormat, $value)->getTimestamp();
            $otherValue= Carbon::createFromFormat($otherFormat, $otherFieldValue)->getTimestamp();
            return [$thisValue,$otherValue];
        }
        catch(\Exception $e){
            return false;
        }
    }
}
