<?php

use App\Models\User\Notification;
use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Repositories\Admin\Interfaces\SliderInterface;
use App\Repositories\Core\Interfaces\ApplicationSettingInterface;
use App\Repositories\Core\Interfaces\LanguageInterface;
use App\Repositories\Core\Interfaces\RoleInterface;
use App\Repositories\Core\Interfaces\SystemNoticeInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Services\Core\NavService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

if (!function_exists('company_name')) {
    function company_name()
    {
        $companyName = settings('company_name');
        return empty($companyName) ? config('app.name') : $companyName;
    }
}

if (!function_exists('company_logo')) {
    function company_logo()
    {
        $logoPath = 'storage/' . config('commonconfig.path_image');
        $companyLogo = settings('company_logo');
        $avatar = valid_image($logoPath, $companyLogo) ? $logoPath . $companyLogo : $logoPath . 'logo.png';
        return asset($avatar);
    }
}

if (!function_exists('company_logo_dashboard')) {
    function company_logo_dashboard()
    {
        $logoPath = 'storage/' . config('commonconfig.path_image');
        $companyLogo = settings('company_logo_dashboard');
        $avatar = valid_image($logoPath, $companyLogo) ? $logoPath . $companyLogo : $logoPath . 'logo.png';
        return asset($avatar);
    }
}

if (!function_exists('payment_method_logo')) {
    function payment_method_logo($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.payment_method_logo');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'logo.png';

        return asset($avatar);
    }
}

if (!function_exists('auction_image')) {
    function auction_image($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.auction_image');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'logo.png';

        return asset($avatar);
    }
}

if (!function_exists('slider_images')) {
    function slider_images($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.slider_images');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'preview.png';

        return asset($avatar);
    }
}

if (!function_exists('dispute_image')) {
    function dispute_image($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.dispute_image');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'logo.png';

        return asset($avatar);
    }
}

if (!function_exists('currency_logo')) {
    function currency_logo($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.currency_logo');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'Clogo.png';

        return asset($avatar);
    }
}

if (!function_exists('know_your_customer_images')) {
    function know_your_customer_images($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.know_your_customer_images');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'preview.png';

        return asset($avatar);
    }
}

if (!function_exists('get_seller_profile_image')) {
    function get_seller_profile_image($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.seller_profile_images');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'preview.png';

        return asset($avatar);
    }
}

if (!function_exists('random_string')) {

    function random_string($length = 10, $characters = null)
    {
        if ($characters == null) {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        }
        $output = '';
        for ($i = 0; $i < $length; $i++) {
            $y = rand(0, strlen($characters) - 1);
            $output .= substr($characters, $y, 1);
        }
        return $output;
    }
}

if (!function_exists('settings')) {

    function settings($ApplicationSettingField = null, $database = false)
    {
        if ($database) {
            if (is_null($ApplicationSettingField)) {
                $adminSettings = app(ApplicationSettingInterface::class)->getAll();
                $adminSettings = $adminSettings->pluck('value', 'slug')->toArray();
                foreach ($adminSettings as $key => $val) {
                    if (is_json($val)) {
                        $arrayConfig[$key] = json_decode($val, true);
                    } else {
                        $arrayConfig[$key] = $val;
                    }
                }
            } else {
                if (is_array($ApplicationSettingField) && count($ApplicationSettingField) > 0) {
                    $arrayConfig = app(ApplicationSettingInterface::class)->getBySlugs($ApplicationSettingField);
                } else {
                    $arrayConfig = app(ApplicationSettingInterface::class)->getBySlug($ApplicationSettingField);
                }

            }
            return $arrayConfig;
        }

        $arrayConfig = Cache::get('application_settings');

        if (is_array($arrayConfig)) {
            if ($ApplicationSettingField != null) {
                if (is_array($ApplicationSettingField) && count($ApplicationSettingField) > 0) {
                    return array_intersect_key($arrayConfig, array_flip($ApplicationSettingField));
                } elseif (!is_array($ApplicationSettingField) && isset($arrayConfig[$ApplicationSettingField])) {
                    return $arrayConfig[$ApplicationSettingField];
                } else {
                    return null;
                }
            } else {
                return $arrayConfig;
            }
        }
        return false;
    }
}

if (!function_exists('check_language')) {
    function check_language($language)
    {
        $languages = language();
        if (array_key_exists($language, $languages) == true) {
            return $language;
        }
        return null;
    }
}

if (!function_exists('set_language')) {
    function set_language($language, $default = null)
    {
        $languages = language();
        if (!array_key_exists($language, $languages)) {
            if (isset($_COOKIE['lang']) && check_language($_COOKIE['lang']) != null && array_key_exists($_COOKIE['lang'], $languages)) {
                $language = $_COOKIE['lang'];
            } else {
                if ($default != null && array_key_exists($default, $languages)) {
                    $language = $default;
                } else {
                    $language = settings('lang');
                }
                if ($language != false && array_key_exists($language, $languages)) {
                    setcookie("lang", $language, time() + (86400 * 30), '/');
                } else {
                    $language = LANGUAGE_DEFAULT;
                }
            }
        }
        App()->setlocale($language);
    }
}

if (!function_exists('language')) {
    function language($language = null)
    {
        $languages = Cache::get('languages');

        if (empty($languages)) {
            try{
                $conditions = ['is_active' => ACTIVE_STATUS_ACTIVE];
                $langs = app(LanguageInterface::class)->getByConditions($conditions);
                foreach ($langs as $lang) {
                    $languages[$lang->short_code] = [
                        'name' => $lang->name,
                        'icon' => $lang->icon
                    ];
                }
            }
            catch (Exception $e){
                $languages=[];
            }
            Cache::set('languages', $languages);
        }

        return is_null($language) ? $languages : $languages[$language];
    }
}

if (!function_exists('language_short_code_list')) {
    function language_short_code_list($language = null)
    {
        $languages = array_keys(language());

        return is_null($language) ? array_combine($languages, $languages) : $languages[$language];
    }
}


if (!function_exists('fake_field')) {
    function fake_field($fieldname, $reverse = false)
    {
        if ($reverse === true) {
            return array_flip(config('fakefields.table_keys'))[$fieldname];
        }
        return Config::get('fakefields.table_keys.' . $fieldname, $fieldname);
    }
}

if (!function_exists('base_key')) {
    function base_key()
    {
        return encode_decode(config('fakefields.base_key'));
    }
}

if (!function_exists('encode_decode')) {
    function encode_decode($data, $decryption = false)
    {
        $code = ['x', 'f', 'z', 's', 'b', 'h', 'n', 'a', 'c', 'm'];
        if ($decryption == true) {
            $code = array_flip($code);
        }
        $output = '';
        $length = strlen($data);
        try {
            for ($i = 0; $i < $length; $i++) {
                $y = substr($data, $i, 1);
                $output .= $code[$y];
            }
        } catch (Exception $e) {
            $output = null;
        }
        return $output;
    }
}

if (!function_exists('generate_id')) {

    function generate_id($random=false)
    {
        $items[]=random_string(5);
        $items[]=uniqid();
        $items[]=random_string(5);
        if($random){
            shuffle($items);
        }
        $output = implode('-',$items);
        return $output;
    }
}

if (!function_exists('validate_date')) {
    function validate_date($date, $seperator = '-')
    {
        $datepart = explode($seperator, $date);
        return strlen($date) == 10 && count($datepart) == 3 && strlen($datepart[0]) == 4 && strlen($datepart[1]) == 2 && strlen($datepart[2]) == 2 && ctype_digit($datepart[0]) && ctype_digit($datepart[1]) && ctype_digit($datepart[2]) && checkdate($datepart[1], $datepart[2], $datepart[0]);
    }
}

if (!function_exists('has_permission')) {
    function has_permission($routeName, $userId = null, $is_link = true, $is_api = false)
    {
        $configPath = $is_api ? 'routeapipermission' : 'routepermission';

        $isAccessible = $is_link ? false : ROUTE_REDIRECT_TO_UNAUTHORIZED;

        if (is_null($userId)) {
            $user = auth()->user();
        } else {
            $user = app(UserInterface::class)->getFirstById($userId);
        }

        if (empty($user)) {
            Config::set($configPath . '.all_accessible_routes', []);
            return $isAccessible;
        }

        $allAccessibleRoutes = config($configPath . '.all_accessible_routes');
        $routeConfig = config($configPath);

        if (isset($routeConfig[ROUTE_CONSTANT_PERMISSION][$user->role_id])) {
            $currentUserRole = $routeConfig[ROUTE_CONSTANT_PERMISSION][$user->role_id];
        } else {
            $currentUserRole = [
                ROUTE_MUST_ACCESSIBLE => [],
                ROUTE_NOT_ACCESSIBLE => [],
            ];
        }

        if (is_null($allAccessibleRoutes)) {
            $allAccessibleRoutes = [];
            $permissionGroups = Cache::get("roles{$user->role_id}");

            if (is_null($permissionGroups)) {
                $permissionGroups = app(RoleInterface::class)->getFirstById($user->role_id)->permissions;
                Cache::forever("roles{$user->role_id}", $permissionGroups);
            }

            foreach ($currentUserRole[ROUTE_MUST_ACCESSIBLE] as $mainGroup) {
                foreach ($routeConfig["configurable_routes"][$mainGroup] as $groupName => $groupAccessName) {
                    foreach ($groupAccessName as $accessKey => $accessName) {
                        try {
                            $routes = $routeConfig["configurable_routes"][$mainGroup][$groupName][$accessKey];
                            $allAccessibleRoutes = array_merge($allAccessibleRoutes, $routes);

                        } catch (Exception $e) {
                        }
                    }
                }
            }
            if (empty($permissionGroups)) {
                Config::set($configPath . '.all_accessible_routes', array_merge($allAccessibleRoutes, config($configPath . '.private_routes')));
            } else {

                foreach ($permissionGroups as $permissionGroupName => $permissionGroup) {
                    foreach ($permissionGroup as $groupName => $groupAccessName) {
                        foreach ($groupAccessName as $accessName) {
                            try {
                                $routes = $routeConfig["configurable_routes"][$permissionGroupName][$groupName][$accessName];
                                /*if(in_array($routeName, $routes) ) {
                                    $isAccessible = true;
                                }*/
                                if (!in_array($permissionGroupName, $currentUserRole[ROUTE_MUST_ACCESSIBLE]) && !in_array($permissionGroupName, $currentUserRole[ROUTE_NOT_ACCESSIBLE])) {
                                    $allAccessibleRoutes = array_merge($allAccessibleRoutes, $routes);
                                }

                            } catch (Exception $e) {
                            }
                        }
                    }
                }
                $allAccessibleRoutes = array_merge($allAccessibleRoutes, $routeConfig['private_routes']);
                Config::set($configPath . '.all_accessible_routes', $allAccessibleRoutes);
            }
        }
        if (settings('maintenance_mode') == UNDER_MAINTENANCE_MODE_ACTIVE && $user->is_accessible_under_maintenance != UNDER_MAINTENANCE_ACCESS_ACTIVE) {
            if (
                !empty($allAccessibleRoutes) && in_array($routeName, $allAccessibleRoutes) &&
                in_array($routeName, $routeConfig['avoidable_maintenance_routes'])
            ) {
                $isAccessible = true;
            } else {
                $isAccessible = $is_link ? false : ROUTE_REDIRECT_TO_UNDER_MAINTENANCE;
            }
        } elseif (in_array($routeName, $routeConfig['private_routes'])) {
            $isAccessible = true;
        } else if (!empty($allAccessibleRoutes) && in_array($routeName, $allAccessibleRoutes)) {
            if (in_array($routeName, $routeConfig['avoidable_unverified_routes'])) {
                $isAccessible = true;
            } elseif (in_array($routeName, $routeConfig['avoidable_suspended_routes'])) {
                $isAccessible = true;
            } elseif (in_array($routeName, $routeConfig['financial_routes'])) {
                if ($user->is_financial_active == FINANCIAL_STATUS_ACTIVE) {
                    $isAccessible = true;
                } else {
                    $isAccessible = $is_link ? false : ROUTE_REDIRECT_TO_FINANCIAL_ACCOUNT_SUSPENDED;
                }
            } elseif (
                (
                    $user->is_email_verified == EMAIL_VERIFICATION_STATUS_ACTIVE ||
                    settings('require_email_verification') == ACTIVE_STATUS_INACTIVE
                ) && $user->is_active == USER_STATUS_ACTIVE
            ) {
                $isAccessible = true;
            } else {
                if ($user->is_email_verified != EMAIL_VERIFICATION_STATUS_ACTIVE &&
                    settings('require_email_verification') == ACTIVE_STATUS_ACTIVE) {
                    $isAccessible = $is_link ? false : ROUTE_REDIRECT_TO_EMAIL_UNVERIFIED;
                } elseif ($user->is_active != USER_STATUS_ACTIVE) {
                    $isAccessible = $is_link ? false : ROUTE_REDIRECT_TO_ACCOUNT_SUSPENDED;
                }
            }
        }
        return $isAccessible;
    }
}

if (!function_exists('is_json')) {
    function is_json($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }
}

if (!function_exists('is_current_route')) {
    function is_current_route($route_name, $active_class_name = 'active', $must_have_route_parameters = null, $optional_route_parameters = null)
    {
        if (!is_array($route_name)) {
            $is_selected = \Route::currentRouteName() == $route_name;
        } else {
            $is_selected = in_array(\Route::currentRouteName(), $route_name);
        }
        if ($is_selected) {
            if ($optional_route_parameters) {
                if (is_array($must_have_route_parameters)) {
                    $is_selected = available_in_parameters($must_have_route_parameters);
                }
                if (is_array($optional_route_parameters)) {
                    $is_selected = available_in_parameters($optional_route_parameters, true);
                }
            } elseif (is_array($must_have_route_parameters)) {
                $is_selected = available_in_parameters($must_have_route_parameters);
            }
        }
        return $is_selected == true ? $active_class_name : '';
    }

    function available_in_parameters($route_parameter, $optional = false)
    {
        $is_selected = true;
        foreach ($route_parameter as $key => $val) {
            if (is_array($val)) {
                $current_route_parameter = \Request::route()->parameter($val[0]);
                if ($val[1] == '<') {
                    $is_selected = $current_route_parameter < $val[2];
                } elseif ($val[1] == '<=') {
                    $is_selected = $current_route_parameter <= $val[2];
                } elseif ($val[1] == '>') {
                    $is_selected = $current_route_parameter > $val[2];
                } elseif ($val[1] == '>=') {
                    $is_selected = $current_route_parameter >= $val[2];
                } elseif ($val[1] == '!=') {
                    $is_selected = $current_route_parameter != $val[2];
                } else {
                    $param = isset($val[2]) ? $val[2] : $val[1];
                    $is_selected = $current_route_parameter == $param;
                }
            } else {
                $current_route_parameter = \Request::route()->parameter($key);
                if ($optional && $current_route_parameter !== 0 && empty($current_route_parameter)) {
                    continue;
                }
                $is_selected = $current_route_parameter == $val;
            }
            if ($is_selected == false) {
                break;
            }
        }
        return $is_selected;
    }
}

if (!function_exists('return_get')) {
    function return_get($key, $val = '')
    {
        $output = '';

//        dd($_GET[$key]);
        if (isset($_GET[$key]) && $val !== '') {
            if(!is_array($_GET[$key]) && $_GET[$key] === (string)$val){
                $output = ' selected';
            }
            else{
                $output = '';
            }
        } elseif (isset($_GET[$key]) && $val == '') {
            if(!is_array($_GET[$key])){
                $output = $_GET[$key];
            }
            else{
                $output = '';
            }
        }
        return $output;
    }
}

if (!function_exists('array_to_string')) {
    function array_to_string($array, $separator = ',', $key = true, $is_seperator_at_ends = false)
    {
        if ($key == true) {
            $output = implode($separator, array_keys($array));
        } else {
            $output = implode($separator, array_values($array));
        }
        return $is_seperator_at_ends ? $separator . $output . $separator : $output;
    }
}

if (!function_exists('valid_image')) {
    function valid_image($imagePath, $image)
    {
        $extension = explode('.', $image);
        $isExtensionAvailable = in_array(end($extension), config('commonconfig.image_extensions'));

        return $isExtensionAvailable && file_exists(public_path($imagePath . $image));
    }
}

if (!function_exists('get_avatar')) {
    function get_avatar($avatar)
    {
        $avatarPath = 'storage/' . config('commonconfig.path_profile_image');

        $avatar = valid_image($avatarPath, $avatar) ? $avatarPath . $avatar : $avatarPath . 'avatar.jpg';

        return asset($avatar);
    }
}

if (!function_exists('get_user_specific_notice')) {
    function get_user_specific_notice($userId = null)
    {
        if (is_null($userId)) {
            $userId = Auth::id();
        }

        $notificationRepository = app(NotificationInterface::class);
        return [
            'list' => $notificationRepository->getLastFive($userId),
            'count_unread' => $notificationRepository->countUnread($userId)
        ];
    }
}

if (!function_exists('get_nav')) {
    function get_nav($slug, $template = 'default_nav')
    {

        return new HtmlString(app(NavService::class)->navigationSingle($slug, $template));
    }
}

if (!function_exists('view_html')) {
    function view_html($text)
    {
        return new HtmlString($text);
    }
}

if (!function_exists('starts_with')) {
    function starts_with($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}

if (!function_exists('ends_with')) {
    function ends_with($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}

if (!function_exists('get_breadcrumbs')) {
    function get_breadcrumbs()
    {
        $routeList = Route::getRoutes()->getRoutesByMethod()['GET'];
        $baseUrl = url('/');
        $segments = Request::segments();
        $routeUries = explode('/', Route::current()->uri());
        $breadcrumbs = [];
        foreach ($segments as $key => $segment) {

            $displayUrl = true;
            $lastBreadcrumb = end($breadcrumbs);
            if (empty($lastBreadcrumb)) {
                $url = $baseUrl . '/' . $segment;
            } else {
                $url = $lastBreadcrumb['url'] . '/' . $segment;

            }

            $uris = array_slice($routeUries, 0, $key + 1);
            $resultUri = '';
            foreach ($uris as $key => $uri) {
                if (starts_with($uri, '{') && ends_with($uri, '}')) {
                    if (strpos($uri, Str::singular($uris[$key - 1])) !== false) {
                        $resultUri .= '/{id}';
                        continue;
                    }
                }
                $resultUri .= '/' . $uri;
            }

            if (!array_key_exists(ltrim($resultUri, '/'), $routeList)) {
                $displayUrl = false;
            }
            $breadcrumbs[] = [
                'name' => Str::title(str_replace('-', ' ', $segment)),
                'url' => $url,
                'display_url' => $displayUrl
            ];

        }
        return $breadcrumbs;
    }
}

if (!function_exists('get_system_notices')) {
    function get_system_notices()
    {
        $systemNoticeInterface = app(SystemNoticeInterface::class);
        $date = Carbon::now();
        $totalMinutes = $date->diffInMinutes($date->copy()->endOfDay());

        if (Cache::has('systemNotices')) {
            $systemNotices = Cache::get('systemNotices');
        } else {
            $systemNotices = $systemNoticeInterface->todaysNotifications();
            Cache::put('systemNotices', $systemNotices, $totalMinutes);
        }

        if ($systemNotices->isEmpty()) {
            return $systemNotices;
        }

        $systemNoticeIds = $systemNotices->pluck('updated_at', 'id')->toArray();
        if (session()->has('seenSystemNotices')) {
            $seenSystemNotices = session()->get('seenSystemNotices');
            $systemNotices = $systemNotices->filter(function ($systemNotice, $key) use ($seenSystemNotices) {
                $date = now();
                if (!($systemNotice->start_at <= $date && $systemNotice->end_at >= $date)) {
                    return false;
                }


                if (array_key_exists($systemNotice->id, $seenSystemNotices) && $systemNotice->updated_at->eq($seenSystemNotices[$systemNotice->id])) {
                    return false;
                }
                return $systemNotice;
            });
        }

        session()->put('seenSystemNotices', $systemNoticeIds);
        return $systemNotices;
    }
}

if (!function_exists('get_available_timezones')) {
    function get_available_timezones()
    {
        return [
            'UTC' => __('Default'),
            'BST' => __('Bangladesh Standard Time'),
        ];
    }
}

if (!function_exists('form_validation')) {
    function form_validation($errors, $name)
    {
        return $errors->has($name) ? 'form-control form-control-sm is-invalid' : 'form-control form-control-sm';
    }
}

if (!function_exists('get_user_roles')) {
    function get_user_roles()
    {
        return app(RoleInterface::class)->getUserRoles()->toArray();
    }
}

if (!function_exists('get_image')) {
    function get_image($image)
    {
        $imagePath = 'storage/' . config('commonconfig.path_image');
        if (valid_image($imagePath, $image)) {
            return asset($imagePath . $image);
        }

        return null;
    }
}

if (!function_exists('get_language_icon')) {
    function get_language_icon($icon)
    {
        $languagePath = 'storage/' . config('commonconfig.language_icon');

        if (valid_image($languagePath, $icon)) {
            return asset($languagePath . $icon);
        }

        return null;
    }
}

if (!function_exists('generate_language_url')) {
    function generate_language_url($language)
    {
        if (is_null(check_language($language))) {
            return 'javascript:;';
        }
        $oldLanguage = request()->segment(1);
        $oldLanguage = check_language($oldLanguage);
        $uri = request()->getRequestUri();

        if ($oldLanguage) {
            $uri = Str::replaceFirst($oldLanguage, $language, $uri);
        } else {
            $uri = $language . $uri;
        }

        return url('/') . '/' . ltrim($uri, '/');
    }
}

if (!function_exists('display_language')) {
    function display_language($lang, $params = null)
    {
        $item = settings('lang_switcher_item');
        $params = is_null($params) ? language($lang) : $params;
        if ($item == 'name') {
            return $params['name'];
        } elseif ($item == 'icon') {

            return new HtmlString('<img width="30" height="22" src="' . get_language_icon($params['icon']) . '">');
        } else {
            return strtoupper($lang);
        }
    }
}

if (!function_exists('get_notifications'))
{
    function get_notifications()
    {
        $userNotifications = app(NotificationInterface::class);

        return $userNotifications->getLastFive(auth()->id(), 5);
    }
}

if (!function_exists('total_notifications'))
{
    function total_notifications()
    {
        $userNotifications = app(NotificationInterface::class)->getByConditions(['user_id' => auth()->user()->id,'read_at' => null])->count();

        return $userNotifications;
    }
}

if (!function_exists('get_slider_images'))
{
    function get_slider_images()
    {
        $slider = app(SliderInterface::class);

        return $slider->getDefaultSlider()->images;
    }
}

if (!function_exists('get_popular_category'))
{
    function get_popular_category($limit)
    {
        $categories = app(CategoryInterface::class);

        return $categories->getPopularCategory($limit);
    }
}

if (!function_exists('get_popular_auction'))
{
    function get_popular_auction($limit)
    {
        $categories = app(AuctionInterface::class);

        return $categories->getPopularAuction($limit);
    }
}
