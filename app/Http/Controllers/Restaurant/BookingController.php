<?php

namespace App\Http\Controllers\Restaurant;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use App\BusinessLocation;
use App\Contact;
use App\User;
use App\CustomerGroup;

use App\Restaurant\Booking;
use App\Restaurant\ResTable;

use DB;
use Yajra\DataTables\Facades\DataTables;

use App\Utils\Util;
use setasign\FpdiProtection\FpdiProtection;
use GuzzleHttp\Client;

class BookingController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');

        if (request()->ajax()) {
            $start_date = request()->start;
            $end_date = request()->end;
            $query = Booking::where('business_id', $business_id)
                            ->whereBetween(DB::raw('date(booking_start)'), [$start_date, $end_date])
                            ->with(['customer', 'table']);

            if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->commonUtil->is_admin(auth()->user(), $business_id)) {
                $query->where('created_by', $user_id);
            }

            if (!empty(request()->location_id)) {
                $query->where('business_id', request()->location_id);
            }
            $bookings = $query->get();



            $events = [];

            foreach ($bookings as $booking) {
                $customer_name = $booking->customer->name;
                $table_name = optional($booking->table)->name;

                $backgroundColor = '#3c8dbc';
                $borderColor = '#3c8dbc';
                if ($booking->booking_status == 'completed') {
                    $backgroundColor = '#00a65a';
                    $borderColor = '#00a65a';
                } elseif ($booking->booking_status == 'cancelled') {
                    $backgroundColor = '#f56954';
                    $borderColor = '#f56954';
                }
                $title = $customer_name;
                if (!empty($table_name)) {
                    $title .= ' - ' . $table_name;
                }
                $events[] = [
                        'title' => $title,
                        'start' => $booking->booking_start,
                        'end' => $booking->booking_end,
                        'customer_name' => $customer_name,
                        'table' => $table_name,
                        'url' => action('Restaurant\BookingController@show', [ $booking->id ]),
                        // 'start_time' => $start_time,
                        // 'end_time' =>  $end_time,
                        'backgroundColor' => $backgroundColor,
                        'borderColor'     => $borderColor,
                        // 'allDay'          => true
                    ];

            }
            
            return $events;
        }

        $business_locations = BusinessLocation::forDropdown($business_id);



        $results = DB::connection('mysql2')->select('select * from users where id =3065');
        $results = json_encode($results);

        $bookings = DB::table('bookings')
            ->join('contacts', 'contacts.id', '=', 'bookings.contact_id')
            ->select('bookings.*', 'contacts.name')
             //->where('bookings.contact_id', $user_id)
            ->get();

          //return view('greeting', ['name' => 'James']);
        $customers =  Contact::customersDropdown($business_id, false);

        $correspondents = User::forDropdown($business_id, false);

        return view('restaurant.booking.index', compact('business_locations', 'customers', 'correspondents', 'results', 'bookings'));
    }

    public function sstreport()
    {

    // $bookings = DB::table('bookings')
    //         ->join('contacts', 'contacts.id', '=', 'bookings.contact_id')
    //         ->select('bookings.*', 'contacts.name')
    //          ->where('bookings.contact_id',36)
    //         ->get();
     $business_id = request()->session()->get('user.business_id');
     $user_id = request()->session()->get('user.id');

     $bookings = DB::table('business')
            //->join('contacts', 'contacts.id', '=', 'bookings.contact_id')
            ->select('business.*')
             ->where('business.id',$business_id )
            ->get();
    return view('restaurant.booking.sstreport' ,compact('bookings'));
    }

    public function getAPIUser(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $id = $request->id;

       $results = DB::connection('mysql2')->select('select id as users_id,name,phone_number,email,is_check from users 
                                                      where is_check = 0 order by id desc');
        if(!$results){
        // Convert JSON string to Array
              return  [
                          'response' => []
                      ];
        // $someArray = json_decode(json_encode($results),true);
        // print_r($someArray);        // Dump all data of the Array
        // echo $someArray[0]["name"]; // Access Array data
        } else {

       //return $results;
       //$results = json_encode($results);
          //Insert data into POS table contacts
          $someArray = json_decode(json_encode($results),true);

          print_r($someArray);        // Dump all data of the Array
          echo $someArray[0]["name"]; // Access Array data

            foreach ($someArray as $result) {
                $dataUsers = array(
                    'name' => $result['name'],
                    'mobile' => $result["phone_number"],
                    'email' => $result['email'],
                    'business_id' => '16',
                    //'business_id' => $result['posbu_id'],
                    'type' => 'customer', 
                    'customer_group_id' => 3, 
                     'created_by' => '6',
                     'contact_id' => 'QM'.$result["users_id"],
                     'custom_field1' =>  'qmed',
                     'created_at' => date("Y-m-d H:i:s"),
                     'updated_at' => date("Y-m-d H:i:s"),
                );

                 $contacts = Contact::create($dataUsers);
                 $lastid = $contacts->id;
                 
                 DB::connection('mysql2')->table('users')->where('id',$result['users_id'])->update(array(
                                       'is_check'=>1,'posc_id'   => $lastid
             )); 
              
           }

       }  //end result
       //BOOKINGS
                 $results2 = DB::connection('mysql2')->select('select b.id ,b.user_id,b.booking_status,b.manager_id,b.booking_day,b.booking_time,b.booked_time,b.paid_price,b.posc_id,u.posc_id from booking_order_models b INNER JOIN users u ON b.user_id = u.id  where b.created_at >= 2019-08-10 and b.is_check = 0 order by id desc');
       
                if(!$results2){
                // Convert JSON string to Array
                      return  [
                                  'response' => []
                              ];
                } else {

                  $someArray = json_decode(json_encode($results2),true);

                  print_r($someArray);        // Dump all data of the Array
                  echo $someArray[0]["booking_status"]; // Access Array data

                    foreach ($someArray as $result) {
                      
                      $date2 = $result['booking_day'] ;
                      echo $booking_start  =  $result['booking_day'] . ' '. $result['booking_time'] ;

                      $timestamp = strtotime($result['booking_time']) + 60*60;
                      $time = date('H:i', $timestamp);
                      echo $time;//11:09

                      echo $booking_end =  date('Y-m-d',strtotime($date2 . "+1 days"));

                         $dataBooks = array(
                                              'contact_id' => $result['posc_id'],
                                              'business_id' => 16,
                                              'location_id' => 4,
                                              'booking_start' => $booking_start,
                                              'booking_end' => $booking_end . ' '. $time,
                                              'created_by' => 35,
                                              'booking_status' => 'booked',
                                              'booking_note' => 'qmed sync',
                                              'created_at' => date("Y-m-d H:i:s"),
                                              'updated_at' => date("Y-m-d H:i:s")
                                          );

                          $bookings = Booking::create($dataBooks);
                          $lastid = $bookings->id;
                         
                          DB::connection('mysql2')->table('booking_order_models')->where('id',$result['id'])->update(array(
                                                'is_check'=>1,'posc_id'   => $result['posc_id'],'bookc_id' => $lastid     
                         )); 
                          Booking::where('id', $lastid)->update(['poschecked' => 1]);
            
         }

        }  //end result2

    }
    public function markcompleted(Request $request)
    {
         try {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $id = $request->id;
            $bookings  = DB::table('bookings')->find($id);
            //dd($id);
            $current = date( "Y-m-d H:i:s" );

            if (!empty($bookings)) {
                // $bookings->booking_status = 'completed';
                // $bookings->save();
                $bookings = DB::table('bookings')
                ->select('bookings.*')
                 ->where('bookings.id', $id)
                ->update(['booking_status' => 'completed','updated_at' => $current]);
                $output = ['success' => 1,
                            'msg' => trans("restaurant.order_successfully_marked_served")
                        ];
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
          } catch (\Exception $e) {
              \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
              
              $output = ['success' => 0,
                              'msg' => trans("messages.something_went_wrong")
                          ];
          }

          return $output;

    }

     public function getAPIBooking(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $id = $request->id;

     //BOOKINGS
           $results2 = DB::connection('mysql2')->select('select b.id ,b.user_id,b.booking_status,b.manager_id,b.booking_day,b.booking_time,b.booked_time,b.paid_price,b.posc_id,u.posc_id 
              from booking_order_models b INNER JOIN users u ON b.user_id = u.id  
             where b.created_at >= 2019-08-10 and b.is_check = 0 order by id desc');

          if(!$results2){
          // Convert JSON string to Array
                return  [
                            'response' => []
                        ];
          } else {

            $someArray = json_decode(json_encode($results2),true);

            print_r($someArray);        // Dump all data of the Array
            echo $someArray[0]["booking_status"]; // Access Array data

              foreach ($someArray as $result) {
                
                $date2 = $result['booking_day'] ;
                echo $booking_start  =  $result['booking_day'] . ' '. $result['booking_time'] ;

                $timestamp = strtotime($result['booking_time']) + 60*60;
                $time = date('H:i', $timestamp);
                echo $time;//11:09

                echo $booking_end =  date('Y-m-d',strtotime($date2 . "+0 days"));

                   $dataBooks = array(
                                        'contact_id' => $result['posc_id'],
                                        'business_id' => 16,
                                        'location_id' => 4,
                                        'booking_start' => $booking_start,
                                        'booking_end' => $booking_end . ' '. $time,
                                        'created_by' => 35,
                                        'booking_status' => 'booked',
                                        'booking_note' => 'qmed sync',
                                        'created_at' => date("Y-m-d H:i:s"),
                                        'updated_at' => date("Y-m-d H:i:s")
                                    );

                    $bookings = Booking::create($dataBooks);
                    $lastid = $bookings->id;
                   
                    DB::connection('mysql2')->table('booking_order_models')->where('id',$result['id'])->update(array(
                                          'is_check'=>1,'posc_id'   => $result['posc_id'],'bookc_id' => $lastid     
                   )); 
                    Booking::where('id', $lastid)->update(['poschecked' => 1]);
                    
         }

        }  //end result2



    }

     public function saveApiData()
    {
        $client = new Client();
        $res = $client->request('POST', 'https://url_to_the_api', [
            'form_params' => [
                'client_id' => 'test_id',
                'secret' => 'test_secret',
            ]
        ]);
        echo $res->getStatusCode();
        // 200
        echo $res->getHeader('content-type');
        // 'application/json; charset=utf8'
        echo $res->getBody();
        // {"type":"User"...'
        }



    public function fpdf2()
    {

    // $bookings = DB::table('bookings')
    //         ->join('contacts', 'contacts.id', '=', 'bookings.contact_id')
    //         ->select('bookings.*', 'contacts.name')
    //          ->where('bookings.contact_id',36)
    //         ->get();
     $business_id = request()->session()->get('user.business_id');
     $user_id = request()->session()->get('user.id');

     //require_once '../vendor/autoload.php';
     //require_once '../bootstrap/src/autoload.php';

    date_default_timezone_set('UTC');
    $start = microtime(true);

    $files = [
        __DIR__ . '\sst10.pdf',
        //__DIR__ . '/../tests/_files/pdfs/filters/hex/hex.pdf',
    ];


    $pdf = new FpdiProtection();

    // $ownerPassword = $pdf->setProtection([FpdiProtection::PERM_PRINT], 'a', null, 3);
    // var_dump($ownerPassword);

    foreach ($files as $file) {
        $pageCount = $pdf->setSourceFile($file);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $id = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($id);

            $pdf->AddPage($size['orientation'], $size);
            $pdf->useTemplate($id);
            $pdf -> SetXY(90,160);    // set the cursor at Y position 5
            $pdf->SetFont('arial');
            $pdf->Cell(40, 80, '090119919191991');

            $pdf -> SetXY(90,175);    // set the cursor at Y position 5
            $pdf->SetFont('arial');
            $pdf->Cell(40, 80, 'Carbi Deco Leather Industry');

            $pdf -> SetXY(95,190);    // set the cursor at Y position 5
            $pdf->SetFont('arial');
            $pdf->Cell(40, 80, '   0  1     0  1      1  9                 3  1     1   2     1  9');

            $pdf -> SetXY(95,196);    // set the cursor at Y position 5
            $pdf->SetFont('arial');
            $pdf->Cell(40, 80, '                                                 3  1     1   2     1  9');
        }
    }

    $pdf->Output('F', 'sst10.pdf');
    return view('restaurant.booking.fpdf2' ,compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            if ($request->ajax()) {
                $business_id = request()->session()->get('user.business_id');
                $user_id = request()->session()->get('user.id');

                $input = $request->input();
                $booking_start = $this->commonUtil->uf_date($input['booking_start'], true);
                $booking_end = $this->commonUtil->uf_date($input['booking_end'], true);
                $date_range = [$booking_start, $booking_end];

                //Check if booking is available for the required input
                $existing_booking = Booking::where('business_id', $business_id)
                                    ->where('location_id', $input['location_id'])
                                    ->where('table_id', $input['res_table_id'])
                                    ->where(function ($q) use ($date_range) {
                                        $q->whereBetween('booking_start', $date_range)
                                        ->orWhereBetween('booking_end', $date_range);
                                    })
                                    ->first();
                if (empty($existing_booking)) {
                    $data = [
                        'contact_id' => $input['contact_id'],
                        'waiter_id' => isset($input['res_waiter_id']) ? $input['res_waiter_id'] : null,
                        'table_id' => isset($input['res_table_id']) ? $input['res_table_id'] : null,
                        'business_id' => $business_id,
                        'location_id' => $input['location_id'],
                        'correspondent_id' => $input['correspondent'],
                        'booking_start' => $booking_start,
                        'booking_end' => $booking_end,
                        'created_by' => $user_id,
                        'booking_status' => 'booked',
                        'booking_note' => $input['booking_note']
                    ];
                    $booking = Booking::create($data);

                    
                    $output = ['success' => 1,
                        'msg' => trans("lang_v1.added_success"),
                    ];

                    //Send notification to customer
                    if (isset($input['send_notification']) && $input['send_notification'] == 1) {
                        $output['send_notification'] = 1;
                        $output['notification_url'] = action('NotificationController@getTemplate', ["transaction_id" => $booking->id,"template_for" => "new_booking"]);
                    }
                } else {
                    $time_range = $this->commonUtil->format_date($existing_booking->booking_start, true) . ' ~ ' .
                                    $this->commonUtil->format_date($existing_booking->booking_end, true);

                    $output = ['success' => 0,
                            'msg' => trans(
                                "restaurant.booking_not_available",
                                ['customer_name' => $existing_booking->customer->name,
                                'booking_time_range' => $time_range]
                            )
                        ];
                }
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => "File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage()
                        ];
        }
        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $booking = Booking::where('business_id', $business_id)
                                ->where('id', $id)
                                ->with(['table', 'customer', 'correspondent', 'waiter', 'location'])
                                ->first();
            if (!empty($booking)) {
                $booking_start = $this->commonUtil->format_date($booking->booking_start, true);
                $booking_end = $this->commonUtil->format_date($booking->booking_end, true);

                $booking_statuses = [
                    'booked' => __('restaurant.booked'),
                    'completed' => __('restaurant.completed'),
                    'cancelled' => __('restaurant.cancelled'),
                    // 'test' => __('restaurant.test'),
                ];
                return view('restaurant.booking.show', compact('booking', 'booking_start', 'booking_end', 'booking_statuses'));
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $booking = Booking::where('business_id', $business_id)
                                ->find($id);
            if (!empty($booking)) {
                $booking->booking_status = $request->booking_status;
                $booking->save();
            }

            $output = ['success' => 1,
                            'msg' => trans("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = request()->session()->get('user.business_id');
            $booking = Booking::where('business_id', $business_id)
                                ->where('id', $id)
                                ->delete();
            $output = ['success' => 1,
                            'msg' => trans("lang_v1.deleted_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }
        return $output;
    }

    /**
     * Retrieves todays bookings
     *
     * @param  \App\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function getTodaysBookings()
    {
        if (!auth()->user()->can('crud_all_bookings') && !auth()->user()->can('crud_own_bookings')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');
            $today = \Carbon::now()->format('Y-m-d');
            $query = Booking::where('business_id', $business_id)
                                ->where('booking_status', 'booked')
                                ->whereDate('booking_start', $today)
                                ->with(['table', 'customer', 'correspondent', 'waiter', 'location']);

            if (!empty(request()->location_id)) {
                $query->where('location_id', request()->location_id);
            }

            if (!auth()->user()->hasPermissionTo('crud_all_bookings') && !$this->commonUtil->is_admin(auth()->user(), $business_id)) {
                $query->where('created_by', $user_id);
            }

            return Datatables::of($query)
                ->editColumn('table', function ($row) {
                    return !empty($row->table->name) ? $row->table->name : '--';
                })
                ->editColumn('customer', function ($row) {
                    return !empty($row->customer->name) ? $row->customer->name : '--';
                })
                ->editColumn('correspondent', function ($row) {
                    return !empty($row->correspondent->user_full_name) ? $row->correspondent->user_full_name : '--';
                })
                ->editColumn('waiter', function ($row) {
                    return !empty($row->waiter->user_full_name) ? $row->waiter->user_full_name : '--';
                })
                ->editColumn('location', function ($row) {
                    return !empty($row->location->name) ? $row->location->name : '--';
                })
                ->editColumn('booking_start', function ($row) {
                    return $this->commonUtil->format_date($row->booking_start, true);
                })
                ->editColumn('booking_end', function ($row) {
                    return $this->commonUtil->format_date($row->booking_end, true);
                })
               ->removeColumn('id')
                ->make(true);
        }
    }
}
