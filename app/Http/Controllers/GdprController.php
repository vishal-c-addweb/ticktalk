<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\GdprSetting;
use App\Models\User;
use Illuminate\Http\Request;

class GdprController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.gdpr';
        $this->activeSettingMenu = 'gdpr';
        $this->gdprSetting = GdprSetting::first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->view = 'gdpr.ajax.right-to-informed';

        $tab = request('tab');

        switch ($tab) {
        case 'right-to-erasure':
            $this->view = 'gdpr.ajax.right-to-erasure';
                break;
        case 'right-to-data-portability':
            $this->view = 'gdpr.ajax.right-to-data-portability';
                break;
        case 'right-to-access':
            $this->view = 'gdpr.ajax.right-to-access';
                break;
        case 'consent':
            $this->view = 'gdpr.ajax.consent';
                break;
        default:
            $this->view = 'gdpr.ajax.right-to-informed';
                break;
        }

        ($tab == '') ? $this->activeTab = 'right-to-informed' : $this->activeTab = $tab;

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('gdpr.index', $this->data);
    }
    
    public function downloadJson(Request $request)
    {
        $table = User::with('clientDetails', 'attendance', 'employee', 'employeeDetail', 'projects', 'member', 'group')->find(user()->id);
        $filename = 'user-uploads/user.json';
        $handle = fopen($filename, 'w+');
        fputs($handle, $table->toJson(JSON_PRETTY_PRINT));
        fclose($handle);
        $headers = array('Content-type' => 'application/json');

        return response()->download($filename, 'user.json', $headers);
    }

}
