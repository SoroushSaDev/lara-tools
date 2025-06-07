<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Morilog\Jalali\Jalalian;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $jDate = $request->has('date') && !is_null($request['date']) ? Jalalian::fromDateTime($request->date) : jdate();
        $month = $jDate->getMonth();
        $year = $jDate->getYear();
        $day = $jDate->getDay();
        $events = [];

        try {
            $data = Http::get("https://pnldev.com/api/calender?year=$year&month=$month&day=$day&holiday=false")->json();
            if ($data['status'])
                $events = $data['result']['event'];
        } catch (ConnectionException $e) {
//            dd($e);
        }

        $date = $jDate->toCarbon();
        return view('calendar.index', compact('events', 'date'));
    }

    public function create(Request $request)
    {
        $date = Carbon::parse($request->date);
        return view('calendar.create', compact('date'));
    }

    public function store(Request $request)
    {
        dd($request->all());
    }
}
