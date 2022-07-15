<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\App\UpdateAppSetting;
use App\Models\Currency;
use App\Models\Setting;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class AppSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.appSettings';
        $this->activeSettingMenu = 'app_settings';

        $this->middleware(function ($request, $next) {
            abort_403(user()->permission('manage_app_setting') !== 'all');
            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->dateFormat = [
            'd-m-Y', 'm-d-Y', 'Y-m-d', 'd.m.Y', 'm.d.Y', 'Y.m.d', 'd/m/Y', 'm/d/Y', 'Y/m/d',
            'd-M-Y', 'd/M/Y', 'd.M.Y', 'd-M-Y', 'd M Y', 'd F, Y', 'd D M Y', 'D d M Y', 'dS M Y'
        ];
        $this->timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $this->currencies = Currency::all();
        $this->dateObject = Carbon::now();
        $this->cachedFile = File::exists(base_path('bootstrap/cache/config.php'));
        return view('app-settings.index', $this->data);
    }

    /**
     * @param UpdateAppSetting $request
     * @param mixed $id
     * @return array
     * @throws BindingResolutionException
     * @throws CommandNotFoundException
     */
    // phpcs:ignore
    public function update(UpdateAppSetting $request, $id)
    {
        config(['filesystems.default' => 'local']);

        $setting = Setting::first();
        $setting->currency_id = $request->currency_id;
        $setting->timezone = $request->timezone;
        $setting->locale = $request->locale;
        $setting->date_format = $request->date_format;
        $setting->time_format = $request->time_format;
        $setting->app_debug = $request->has('app_debug') && $request->app_debug == 'on' ? 1 : 0;
        $setting->system_update = $request->has('system_update') && $request->system_update == 'on' ? 1 : 0;
        $setting->longitude = $request->longitude;
        $setting->latitude = $request->latitude;
        $setting->dashboard_clock = $request->has('dashboard_clock') && $request->dashboard_clock == 'on' ? 1 : 0;

        $setting->moment_format = $this->momentFormat($setting->date_format);

        $setting->save();

        session()->forget('global_setting');

        $this->createCache($request);

        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param string $dateFormat
     * @return string
     */
    public function momentFormat($dateFormat)
    {
        $availableDateFormats = Setting::DATE_FORMATS;
        return (isset($availableDateFormats[$dateFormat])) ? $availableDateFormats[$dateFormat] : 'DD-MM-YYYY';
    }

    private function createCache($request)
    {
        if ($request->cache) {
            try {
                Artisan::call('optimize');
                Artisan::call('route:clear');
            }catch (\LogicException $e){
                logger($e->getMessage());
            }

        }
        else {
            Artisan::call('optimize:clear');
            Artisan::call('cache:clear');
        }
    }

}
