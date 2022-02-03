<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Insurance;
use App\Models\User;
use App;
use DB;
use Barryvdh\DomPDF\Facade as PDF; 
use Illuminate\Support\Facades\Mail;

class InsureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    //
    public function index()
    {
        //
		$info = DB::table('clients_info')->where('user_id', auth()->user()->id)->first();
        return view('pages.insurance', [
			'info' => $info
			]
		);
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
        //dd("insure");
        $request->validate([
            'fname' => 'required',
            'mname' => 'required',
            'lname' => 'required',
            'dob' => 'required',
			'height' => 'required',
			'weight' => 'required',
            'relationship' => 'required',
            'gender' => 'required',
            'healthdata' => 'required',
            'health_terms_and_conditions' => 'required',
            'terms_and_conditions' => 'required'
        ]);

        $expiryDate = Carbon::now()->addYear();
        
        $status = "";
        if($request->amount == '0.00'){
            $status = "disqualified";
        }else {
            $status = "pending";
        }
        
        
        $json_data = $request->healthdata;
        
        $check = Insurance::where('user_id', auth()->user()->id)
                ->where('fname', $request->fname)
                ->where('lname', $request->lname)
                ->where('relationship', $request->relationship)
                ->whereYear('created_at', '=', Carbon::now()->year)
                ->get();
                
        if($check->count() > 0) {
            
            return back()->with('check', 'This person you\'re ensuring exists in our servers. Proceed to Insure Summary Page.' );
            
        }else{
        
            if(Insurance::create(['fname' => $request->fname,'mname' => $request->mname,'lname' => $request->lname,'dob' => $request->dob,'weight' => $request->weight,'weight_unit' => $request->weight_unit,'height' => $request->height,'height_unit' => $request->height_unit,'relationship' => $request->relationship,'gender' => $request->gender,'status' => $status,'user_id' => auth()->user()->id,'expired_date' => $expiryDate,'amount' => $request->amount,'category' => $request->category,'healthdata' => $request->healthdata]))
            {
                // 
                $insurance = new Insurance;
                
                $date_parse = Carbon::parse($insurance->created_at);
                $expired = $date_parse->addYear();
                
                DB::table('insurances')
                    ->where('id', $insurance->id)
                    ->update(['expired_date' => $expired]);
                    
                    $date = Carbon::parse($insurance->created_at);
                    $timeInMilliseconds = $date->valueOf();
                    
                    $year = Carbon::now()->year;
                    
                
                $html = view('doc.template', 
                    [ 
                        'policy_holder' => auth()->user()->fname." ".auth()->user()->mname." ".auth()->user()->lname,
                        'insured_name' => $request->fname." ".$request->mname." ".$request->lname, 
                        'gender' => $request->gender, 
						'relationship' => $request->relationship, 
						'dob' => $request->dob, 
						'weight' => $request->weight, 
						'weight_unit' => $request->weight_unit, 
						'height' => $request->height, 
						'height_unit' => $request->height_unit, 
                        'date' => Carbon::parse($insurance->created_at)->format('M d, Y'),
                        'healthform' => json_decode($json_data, true)
                        
                    ])->render();
                
                
                $pdf = App::make('dompdf.wrapper');
                $invPDF = $pdf->loadHTML($html);
                
                PDF::loadHTML($html)->setPaper('a4', 'portrait')->setWarnings(false)->save(base_path().'/healthform/'.auth()->user()->fname.'_'.auth()->user()->lname.'_'.$request->fname.'_'.$year.'.pdf');
            
                    $att_name = auth()->user()->fname.'_'.auth()->user()->lname.'_'.$request->fname.'_'.$year;
                    $getEx = base_path('/healthform/'.$att_name.'.pdf');
                    
                    $extension = pathinfo($getEx, PATHINFO_EXTENSION);
                    
					$fullpath = env('APP_URL').'healthform/'.$att_name.'.'.$extension;
				
					$attachPath = 'https://winpamoja.com/healthform/'.$att_name.'.pdf';
                    
                    
                // DB::table('insurances')
                //     ->where('id', $insurance->id)
                //     ->update(['health_doc' => 'hello']);
                
                // 
                Insurance::where('user_id', auth()->user()->id)->where('dob', $request->dob)->update(['health_doc' => $fullpath]);
				
				$data = array(
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->fname." ".auth()->user()->lname,
					'amount' => $request->amount,
					'insured_name' => $request->fname, 
                    'f_mail' => 'pamoja@winpamoja.com',
                    'f_name' => 'WIN Pamoja',
                    'attachment' => $attachPath
                );
        
                Mail::send('emails.healthform', $data, function($message) use ($data) {
                     $message->attach($data['attachment']);
                    $message->to($data['email'], $data['name'])
					->cc('winpamoja2021@gmail.com', $data['f_name'])
                    ->subject('Health Declaration Form');
                    $message->from($data['f_mail'],$data['f_name']);
                });  

                return back()->with('status', 'Application was successful');
            }
        
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
