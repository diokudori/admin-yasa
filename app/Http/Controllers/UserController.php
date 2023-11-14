<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use PDF;
use DB;
use Illuminate\Support\Facades\Auth;
use Response;
use Redirect;
class UserController extends Controller
{
	public $table;
	public $bulans = [];
	public $db;
	function __construct(){
        
        $this->middleware('auth');
        // $user = $this->getUser();
        $this->db = DB::connection(session('db'));
        $this->bulans = array("01"=>"Januari", "02"=>"Februari", "03"=>"Maret", "04"=>"April", "05"=>"Mei", "06"=>"Juni", "07"=>"Juli", "08"=>"Agustus", "09"=>"September", "10"=>"Oktober", "11"=>"November", "12"=>"Desember");
        
     }

     function getUser(){
     	$user = DB::connection('mysql')->table('users')->where('id', Auth::user()->id)->first();
        return $user;
    }

    public function home(){
    	if(Auth::user()->role==0){
            $provinsi = DB::table('data_provinsi')->get();

            foreach ($provinsi as $key => $p) {
                $user = DB::table('users')->select('name')->where('role','!=','0')->where('db',$p->db)->groupBy('name')->get();

                $totalAll = 0;
                $totalReal = 0;
                $totalNotReal = 0;
                $totalPercent = 0;


                foreach($user as $key => $value){
                    try{
                        $tmpAll = DB::connection($p->db)->table($value->name)->select(DB::RAW("count(*)as total"))->first();
                        $tmpReal = DB::connection($p->db)->table($value->name)->select(DB::RAW("count(*)as total"))->where('tgl_serah','!=','')->first();
                        $totalAll += $tmpAll->total;
                        $totalReal += $tmpReal->total;
                    }catch(\Illuminate\Database\QueryException $ex){ 
                      // dd($ex->getMessage()); 
                      // Note any method of class PDOException can be called on $ex.
                        continue;
                    }

                    
                }

                
                $totalNotReal = $totalAll-$totalReal;
                    $totalPercent = ($totalReal/$totalAll)*100;
                    
                    $totalAll = number_format($totalAll,0,",",".");
                    $totalReal = number_format($totalReal,0,",",".");
                    $totalNotReal = number_format($totalNotReal,0,",",".");
                    $totalPercent = number_format($totalPercent,2,",",".");

                $response[] = ["provinsi"=>$p->nama, "totalAll"=>$totalAll, "totalReal"=> $totalReal, "totalNotReal"=>$totalNotReal, "totalPercent"=> $totalPercent];
            }

            
    		return view('user/dashboard')->with('data',$response);
    	}else if(Auth::user()->role==5){
    		return redirect('new/bast');
    	}else if(Auth::user()->role==2){
            return redirect('bulog/entry');
        }else if(Auth::user()->role==1){
            return redirect('entry/distribution');
        }
        
    }

    public function entryDistribution(){

        $data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
        $data['tahap'] = DB::table('tahap')->select('*')->get();
        $data['wil'] = Auth::user()->name;
        $data['bulans'] = $this->bulans[date('m')];
        return view('user/distribution-form')->with($data);
    }

    public function entryDistributionTable(Request $request){
        
        $data['tahap'] = $request->tahap;
        $data['tahaptext'] = DB::table('tahap')->select('*')->where('value',$request->tahap)->first();
        $data['wilayah'] = Auth::user()->name;
        $data['kabupaten'] = $request->kabupaten;
        $data['kecamatan'] = $request->kecamatan;
        $data['kelurahan'] = $request->kelurahan;
        return view('user/distribution-table')->with($data);

    }

    public function entryDistributionList(Request $request){
        
        $data = $request->all();
        $draw=$data['draw'];

        $length=$data['length'];
        $start=$data['start'];
        $search=$data['search']["value"];

        $output=array();
        $output['draw']=$draw;

        $output['data']=array();
        DB::enableQueryLog();
        $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->get()->pluck('prefik');
        $db = DB::connection(Auth::user()->db)->table(Auth::user()->name." as t")
        ->select("t.nama", "t.alamat", "t.prefik", "t.no_urut");
        if($request->real=='false'){
            // echo $request->real;
            $db = $db->whereNotIn('t.prefik', $tahap);
        }else{
            // die();
            $db = $db->whereIn('t.prefik', $tahap);
        }
        
        $db = $db->where('t.kabupaten',$request->kabupaten)
        ->where('t.kecamatan',$request->kecamatan)
        ->where('t.kelurahan',$request->kelurahan);
        

        // print_r($db->get());
        $totalData = $db->count();
        if($search!=""){
            $db = $db->where("prefik","like", "%".$search."%");
        }
        $orderby = '';
        if($data['order'][0]['column']==0){
            $orderby = 'id';
        }else if($data['order'][0]['column']==1){
            $orderby = 'stock_code';
        }else{
            $orderby = $data['columns'][$data['order'][0]['column']]['data'];
            if($orderby==''){
                $orderby = 'no_urut';
            }
        }
        $db = $db->skip($start)->take($length);
        $db = $db->orderBy($orderby,$data['order'][0]['dir']);
        $query=$db->get();
        
        $output['data'] = $query->toArray();
        // $total=$db->count();
        $output['recordsTotal']=$output['recordsFiltered']=$totalData;
        $log = DB::getQueryLog();
        $output['logquery'] = $log;
        return Response::JSON($output);

    }

    public function entryDistributionUpload(Request $request){
        $filenameori = request()->file->getClientOriginalName();


        $exp = explode("_", $filenameori);

        if(Auth::user()->db == 'mysql'){
            $folder = 'jatim_op_db';
        }else{
            $folder = Auth::user()->db;
        }

        $request->kabupaten = trim($request->kabupaten);
        $request->kecamatan = trim($request->kecamatan);
        $request->kelurahan = trim($request->kelurahan);
       
        $resp = DB::connection(Auth::user()->db)->table(Auth::user()->name)
        ->where('kabupaten', $request->kabupaten)
        ->where('kecamatan', $request->kecamatan)
        ->where('kelurahan', $request->kelurahan)
        ->where('no_urut', trim($exp[0]))->get();

        if($resp->count()==0){
            $resp2 = DB::connection(Auth::user()->db)->table(Auth::user()->name)
            ->where('kabupaten', $request->kabupaten)
            ->where('kecamatan', $request->kecamatan)
            ->where('kelurahan', $request->kelurahan)
            ->where('no_urut', trim($exp[1]))->get();

            if($resp2->count()==0){
                DB::connection(Auth::user()->db)->enableQueryLog();
                $resp3 = DB::connection(Auth::user()->db)->table(Auth::user()->name)
                ->where('kabupaten', $request->kabupaten)
                ->where('kecamatan', $request->kecamatan)
                ->where('kelurahan', $request->kelurahan)
                ->where('prefik', trim($exp[1]))
                ->get();

                $query = DB::connection(Auth::user()->db)->getQueryLog();
                
                //360905080061
                //360905080061
                // print_r($exp);
                // print_r($resp3->count());
                // dd($query);
                // die();
                // $queries = DB::getQueryLog();
                // $last_query = end($queries);
                // dd($last_query);

                $resp = $resp3;

            }else{
                $resp = $resp2;
            }


             
        }
        // print_r($resp);
        if($resp->count()==0){
            return Response::JSON(['status'=> false, 'msg'=>'data tidak ditemukan']);
        }

        $resp = $resp[0];

        $serah = DB::connection(Auth::user()->db)->table($request->tahap)->where('prefik', $resp->prefik)->get()->count();

        if($serah>0){
            return Response::JSON(['status'=>false, "msg"=>"Data penyaluran sudah ada"]);
        }

        $endpoint = "https://ptyaons-apps.com/drive/driveSyncTahap.php";
        $request->tgl_serah = str_replace("_", "", $request->tgl_serah);
        $filename = 'pbp_'.$resp->prefik.'.jpg';
        $path = public_path().'/uploads/tmp/';
        $filepath = public_path().'/uploads/tmp/'.$filename;
        request()->file->move($path,$filename);
        // $ifp = fopen( $filepath, 'wb' ); 
        // $write = fwrite( $ifp, request()->file() );
        // clean up the file resource
        // $client = new \GuzzleHttp\Client();
        // $endpoint."?filepath=".$filepath."&filename=".$filename."&db=".$folder."&table=".$user->name."&user_id=".$res['user_id']."&status_penerima=".$data['status_penerima']."&id=".$data['id']."&kabupaten=".$data['kabupaten']."&kecamatan=".$data['kecamatan']."&kelurahan=".$data['kelurahan']."&tgl_serah=".$data['tgl_serah']
        // $res = $client->request('GET', $endpoint, [ 'query' =>[
        //     'filepath' => $filepath,
        //     'filename' => $filename,
        //     'db' => Auth::user()->db,
        //     'table' => $request->tahap,
        //     'user_id' => Auth::user()->id,
        //     'status_penerima' => '1',
        //     'id' => $resp->id,
        //     'tgl_serah' => $request->tgl_serah,
        //     'kabupaten' => $request->kabupaten,
        //     'kecamatan' => $request->kecamatan,
        //     'kelurahan' => $request->kelurahan,
        // ]
            
        // ]);
        // echo $res->getStatusCode();
        // $r = $res->getBody()->getContent();
        
        // $resbody = json_decode($res->getBody());
         // fclose( $ifp ); 
         return Redirect::to($endpoint."?filepath=".$filepath."&filename=".$filename."&db=".$folder."&table=".Auth::user()->name."&tahap=".$request->tahap."&user_id=".Auth::user()->id."&status_penerima=".$request->status_penerima."&id=".$resp->id."&kabupaten=".$request->kabupaten."&kecamatan=".$request->kecamatan."&kelurahan=".$request->kelurahan."&tgl_serah=".$request->tgl_serah."&prefik=".$resp->prefik);
        
    }

    public function bulogList(){
        $data['tahap'] = DB::table('tahap')->get();
        if(Auth::user()->db=='mysql'){
            $data['kabupaten'] = DB::table('data_map')->select('kabupaten')->where('kode_map', Auth::user()->name)->groupBy('kabupaten')->get();

        }else{
            $data['kabupaten'] = DB::connection(Auth::user()->db)->table(Auth::user()->name)->select('kabupaten')->groupBy('kabupaten')->get();
        }
        
        return view('user.bulog-list')->with($data);
    }

    public function bulogListData(Request $request){
        $data = $request->all();
        $draw=$data['draw'];

        $length=$data['length'];
        $start=$data['start'];
        $search=$data['search']["value"];

        $output=array();
        $output['draw']=$draw;

        $output['data']=array();
        DB::enableQueryLog();
        $db = DB::connection(Auth::user()->db)->table($data['tahap'].'_data_gudang')
        ->where('kab',$data['kabupaten'])
        ->where('kec',$data['kecamatan']);
        
        // print_r($db->get());
        $totalData = $db->count();
        // if($search!=""){
        //     $db = $db->where("prefik","like", "%".$search."%");
        // }
        $orderby = '';
        if($data['order'][0]['column']==0){
            $orderby = 'id';
        }else if($data['order'][0]['column']==1){
            $orderby = 'transporter_doc';
        }else{
            $orderby = $data['columns'][$data['order'][0]['column']]['data'];
            if($orderby==''){
                $orderby = 'transporter_doc';
            }
        }
        $db = $db->skip($start)->take($length);
        $db = $db->orderBy($orderby,$data['order'][0]['dir']);
        $query=$db->get();
        
        $output['data'] = $query->toArray();
        // $total=$db->count();
        $output['recordsTotal']=$output['recordsFiltered']=$totalData;
        $log = DB::getQueryLog();
        $output['logquery'] = $log;
        return Response::JSON($output);
    }

    public function bulogEntry(){
        $data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
        $data['tahap'] = DB::table('tahap')->select('*')->get();
        $data['provinsi'] = DB::table('data_provinsi')->select('nama')->where('db',Auth::user()->db)->first()->nama;
        $data['wil'] = Auth::user()->name;
        $data['bulans'] = $this->bulans[date('m')];
        $data['db'] = Auth::user()->db;

        //live
        $data['transporter_key'] = 'YAT_zvqXIcAOhy';

        // $data['transporter_key'] = 'YAT_KEY_gshuy';

        $data['url'] = 'https://bpb.bulog.co.id';
        // $data['url'] = 'https://bpb-sandbox.bulog.co.id';
        $data['url_bulog'] = $data['url'].'/api/transporter/insert/';
        return view('user.bulog-form')->with($data);
    }

    public function bulogDataKec(Request $request){
        $kec_id = DB::table('kec_bulog')->where('kecamatan', $request->kec)->first()->kode_bulog;

        return Response::JSON(['kec_id'=> $kec_id]);
    }

    public function bulogDataRiwayat(Request $request){
        $data = DB::connection($request->db)->table($request->tahap.'_data_gudang')
        ->where('kecamatan_id', $request->kecamatan_id)
        ->where('kel', $request->kelurahan)
        ->first();

        return Response::JSON($data);
    }

    public function bulogFormSimpan(Request $request){
                                    // YAT_zvqXIcA0hy
        // $data['transporter_key'] = 'YAT_zvqXIcAOhy';
        $data['url'] = 'https://bpb.bulog.co.id';

        //sandbox
        // $data['transporter_key'] = 'YAT_KEY_gshuy';
        // $data['url'] = 'https://bpb-sandbox.bulog.co.id';
        $check = DB::connection($request->db)->table($request->tahap.'_data_gudang')
        ->where('transporter_bast',$request->transporter_bast)
        ->where('kel', '!=',$request->kelurahan)
        ->get();
        if($check->count()>0){
            return Redirect::back()->withErrors(['msg' => 'No Surat Jalan sudah digunakan dikelurahan lain']);
        }
        $hit_bulog = DB::table('settings')->where('name','hit_bulog_enabled')->first()->value;

        $request->jumlah_pbp = $request->jumlah_pbp - $request->jumlah_sptjm;
        $status_hit = 0;
        $id_bulog = 0;
        if($hit_bulog=='1'){
            $data['url_bulog'] = $data['url'].'/api/transporter/insert/';
            $curlPost = http_build_query($request->all()); 
            $ch = curl_init();         
            curl_setopt($ch, CURLOPT_URL, $data['url_bulog']);         
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
            curl_setopt($ch, CURLOPT_POST, 1);         
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
            $data = json_decode(curl_exec($ch), true); 
            $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
            $id_bulog = $data['data']['id'];
            $status_hit = 1;
            if ($http_code != 200) { 
                $error_msg = 'Failed to receieve access token'; 
                if (curl_errno($ch)) { 
                    $error_msg = curl_error($ch); 
                    $status_hit = 0;
                } 

                $status_hit = 0;
                
            }


        }

        $resp = DB::connection($request->db)
        ->table($request->tahap.'_data_gudang')
        ->updateOrInsert([
            'surat_jalan'=>$request->surat_jalan,
            'kel'=>$request->kelurahan,
            'kecamatan_id'=>$request->kecamatan_id,
        ],[
            'tahap'=>$request->tahap,
            'kprk'=>$request->wilayah,
            'kab'=>$request->kabupaten,
            'kec'=>$request->kecamatan,
            'no_out'=>$request->no_out,
            'tanggal_alokasi'=>$request->tanggal_alokasi,
            'titik_penyerahan'=>$request->titik_penyerahan,
            'tanggal'=>$request->tanggal,
            'kuantum'=>$request->kuantum,
            'jumlah_pbp'=>$request->jumlah_pbp,
            'jumlah_sptjm'=>$request->jumlah_sptjm,
            'provinsi'=>$request->provinsi,
            'status_hit'=>$status_hit,
            'id_bulog'=>$id_bulog,
            'created_by'=>Auth::user()->id,
        ]);
        $lastInsertedId = DB::getPdo()->lastInsertId();
        $kode_bast = 'BAST' . $request->wilayah . $request->kecamatan_id . $lastInsertedId;
        $kode_doc = 'SJLN' . $request->wilayah . $request->kecamatan_id . $lastInsertedId;
        DB::connection($request->db)
        ->table($request->tahap.'_data_gudang')
        ->where('id', $lastInsertedId)
        ->update(['transporter_bast' => $kode_bast,'transporter_doc' => $kode_doc ]);
        return redirect()->back()->with('success', 'Berhasil menambahkan data gudang');  
    }

    public function bulogFormHapus(Request $request){
        $data['transporter_key'] = 'YAT_zvqXIcAOhy';
        $data['url'] = 'https://bpb.bulog.co.id';
        $data['url_bulog'] = $data['url'].'/api/transporter/delete/'.$request->id;
        // $curlPost = http_build_query($request->all());
        $curlPost = "transporter_key=".$data['transporter_key']; 
        $ch = curl_init();         
        curl_setopt($ch, CURLOPT_URL, $data['url_bulog']);         
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        curl_setopt($ch, CURLOPT_POST, 1);         
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        $data = json_decode(curl_exec($ch), true); 
        $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
         
        if ($http_code != 200) { 
            $error_msg = 'Failed to receieve access token'; 
            if (curl_errno($ch)) { 
                $error_msg = curl_error($ch); 
            } 
            return Response::JSON(['status'=> false, 'message'=>$error_msg]);
        }
    }

    public function hitBulog($db, $tahap, $id){
            $data['url_bulog'] = 'https://bpb.bulog.co.id/api/transporter/insert/';

            $param = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('id',$id)->first();
            $param->transporter_key = 'YAT_zvqXIcAOhy';
            $param->kabupaten = $param->kab;
            $param->kecamatan = $param->kec;
            
            $curlPost = http_build_query($param); 
            $ch = curl_init();         
            curl_setopt($ch, CURLOPT_URL, $data['url_bulog']);         
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
            curl_setopt($ch, CURLOPT_POST, 1);         
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
            $data = json_decode(curl_exec($ch), true); 
            $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
            $id_bulog = $data['data']['id'];
            $status_hit = 1;
            if ($http_code != 200) { 
                $error_msg = 'Failed to receieve access token'; 
                if (curl_errno($ch)) { 
                    $error_msg = curl_error($ch); 
                    $status_hit = 0;
                } 

                $status_hit = 0;
                
            }

            $update = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('id',$id)->update(['id_bulog' => $id_bulog, 'status_hit' => $status_hit]);
            return Response::JSON(['status'=>$update]);
    }

     public function homeRealisasi(){
        if(Auth::user()->role==0){
            // $data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
            // $data['wil'] = Auth::user()->name;
            $provinsi = DB::table('data_provinsi')->get();

            return view('user/dashboard-realisasi')->with('provinsi', $provinsi);
        }else{
            return redirect('new/bast');
        }
        
    }

    public function homeRealisasiTahap(){
        if(Auth::user()->role==0){
            // $data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
            // $data['wil'] = Auth::user()->name;
            $data['tahap'] = DB::table('tahap')->get();
            $data['provinsi'] = DB::table('data_provinsi')->get();

            return view('user/dashboard-realisasi-tahap')->with($data);
        }else{
            return redirect('new/bast');
        }
        
    }

    public function realOptKabupaten(Request $request){
        $tables = DB::table('users')->select('name','email')->where('role','!=','0')->where('db',$request->provinsi)->groupBy('name')->get();
        return Response::JSON($tables);

    }

    public function realOptKecamatan(Request $request){

        $tables = DB::connection($request->db)->table($request->table)->select('kecamatan');
        if(isset($request->kab)){
            $tables = $tables->where('kabupaten', $request->kab);
        }
        $tables = $tables->groupBy('kecamatan')->get();
        return Response::JSON($tables);

    }

    public function realOptKelurahan(Request $request){
        $tables = DB::connection($request->db)->table($request->table)->select('kelurahan')->where('kecamatan', $request->kecamatan)->groupBy('kelurahan')->get();
        return Response::JSON($tables);

    }

    public function realTableTotal(Request $request){
        // $db = DB::connection($request->db);
        if($request->db=='mysql'){
            $users = DB::table('data_map')->select('kabupaten','kode_map')->groupBy('kode_map')->get();
        }else{
            $users = DB::table('users')->selectRaw('name as kode_map, email as kabupaten')->where('db', $request->db)->where('role','!=','0')->groupBy('name')->get();
        }
        
         $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        if($request->kab==''){
              
                
            foreach ($users as $key => $value) {
                if($request->db == 'mysql'){
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$value->kabupaten)->groupBy('kecamatan')->get();
                    foreach ($kode_map as $key2 => $value2) {
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan', $value2->kecamatan)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan', $value2->kecamatan)->where('tgl_serah','!=','')->first()->total;
                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                    }
                }else{
                    
                    $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->first()->total;
                    $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->first()->total;
                    $kuantum += $rencana_salur;
                    $pbp += $pbp_total;
                
                }
                
                
            }
            $sisa = $kuantum-$pbp;
            $persen_pbp = ($pbp/$kuantum)*100;
            $persen_sisa = ($sisa/$kuantum)*100;

        }else{
            if($request->db=='mysql'){
                $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();
            }else{
                $kab = DB::connection($request->db)->table('users')->selectRaw('name as kode_map')->where('email',$request->kab)->groupBy('kode_map')->get();
            }
            if($request->kec==''){
               
               

                foreach ($kab as $key => $value) {
                   $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->first()->total;
                    $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->first()->total;
                    $kuantum += $rencana_salur;
                    $pbp += $pbp_total;
                }

                $sisa = $kuantum-$pbp;
                $persen_pbp = ($pbp/$kuantum)*100;
                $persen_sisa = ($sisa/$kuantum)*100;

            }else{
                if($request->kel==''){

                    foreach ($kab as $key => $value) {
                        $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->where('kecamatan',$request->kec)->first()->total;
                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                       
                    }
                    $sisa = $kuantum-$pbp;
                    $persen_pbp = ($pbp/$kuantum)*100;
                    $persen_sisa = ($sisa/$kuantum)*100;

                }else{
                    foreach ($kab as $key => $value) {
                        if(isset($request->tahap) && $request->tahap !='2023_NOV'){
                            $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                            $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->where('path_ktp','B')->first()->total;

                            $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                            $pbp_total_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->where('path_ktp','B')->first()->total;
                            // print_r($rencana_salur);

                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                            $pbp_total = $pbp_total - $pbp_total_b;
                        }else{
                            $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                            $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('tgl_serah','!=','')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                        }
                        
                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                    }
                    $sisa = $kuantum-$pbp;
                    $persen_pbp = ($pbp/$kuantum)*100;
                    $persen_sisa = ($sisa/$kuantum)*100;
                }
            }
        }


        return Response::JSON(["kuantum"=>number_format((int)$kuantum,0,",","."), "transporter"=>number_format((int)$transporter,0,",","."), "persen_transporter"=>$persen_transporter, "pbp"=>number_format((int)$pbp,0,",","."), "persen_pbp"=>number_format($persen_pbp,2), "sisa"=>number_format((int)$sisa,0,",","."), "persen_sisa"=>number_format($persen_sisa,2)]);
    }

    public function realTableAll(Request $request){
        // $db = DB::connection($request->db);
        if($request->db == 'mysql'){
            $users = DB::table('data_map')->select('kabupaten','kode_kab')->groupBy('kabupaten')->get();
            
        }else{
            $users = DB::table('users')->selectRaw('name as kode_map, email as kabupaten, id as kode_kab')->where('db', $request->db)->where('role','!=','0')->groupBy('name')->get();
        }
        
         $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        $data = [];

        if($request->kab==''){
              
              if($request->db == 'mysql'){
                foreach ($users as $key => $value) {
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$value->kabupaten)->groupBy('kecamatan')->get();

                    foreach ($kode_map as $key2 => $value2) {
                        
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->where('tgl_serah','!=','')->first()->total;
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            // echo $value->kabupaten;
                            continue;
                        }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                        $persen_sisa = ($sisa/$kuantum)*100;

                        if (array_key_exists($value->kode_kab,$data)){
                            $data[$value->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$value->kode_kab]['pbp_r'] += $pbp;
                            $data[$value->kode_kab]['sisa_r'] += $sisa;

                            $pbp = $data[$value->kode_kab]['pbp_r'];
                            $sisa = $data[$value->kode_kab]['sisa_r'];
                            $kuantum = $data[$value->kode_kab]['kuantum_r'];
                            $persen_pbp = ($pbp/$kuantum)*100;
                            $persen_sisa = ($sisa/$kuantum)*100;
                            $data[$value->kode_kab]['kuantum'] = number_format((int)$data[$value->kode_kab]['kuantum_r'],0,",",".");
                            $data[$value->kode_kab]['pbp'] = number_format((int)$data[$value->kode_kab]['pbp_r'],0,",",".");
                            $data[$value->kode_kab]['persen_pbp'] = number_format((int)$persen_pbp,0,",",".");
                            $data[$value->kode_kab]['sisa'] = number_format((int)$data[$value->kode_kab]['sisa_r'],0,",",".");
                            $data[$value->kode_kab]['persen_sisa'] = number_format((int)$persen_sisa,0,",",".");
                        }else{
                            $data[$value->kode_kab] = [
                                "nama"=> $value->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                       
                    }

                }

              }else{
                    foreach ($users as $key => $value) {
                        
                        $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$value->kabupaten)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$value->kabupaten)->where('tgl_serah','!=','')->first()->total;
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            // echo $value->kabupaten;
                            continue;
                        }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                        $persen_sisa = ($sisa/$kuantum)*100;

                        if (array_key_exists($value->kode_kab,$data)){
                            $data[$value->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$value->kode_kab]['pbp_r'] += $pbp;
                            $data[$value->kode_kab]['sisa_r'] += $sisa;

                            $data[$value->kode_kab]['kuantum'] = number_format((int)$data[$value->kode_kab]['kuantum_r'],0,",",".");
                            $data[$value->kode_kab]['pbp'] = number_format((int)$data[$value->kode_kab]['pbp_r'],0,",",".");
                            $data[$value->kode_kab]['sisa'] = number_format((int)$data[$value->kode_kab]['sisa_r'],0,",",".");
                        }else{
                            $data[$value->kode_kab] = [
                                "nama"=> $value->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                       
                    }
              }
                
            

            


        }else{
            // $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();
            if($request->db=='mysql'){
                $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();
            }else{
                $kab = DB::connection($request->db)->table('users')->selectRaw('name as kode_map')->where('email',$request->kab)->groupBy('kode_map')->get();
            }

            if($request->kec==''){
                foreach ($kab as $k => $v) {
                    $kecamatan = DB::connection($request->db)->table($v->kode_map)->select('kecamatan')->groupBy('kecamatan')->get();
                    if($kecamatan->count()==0){
                        continue;
                     }
                    foreach ($kecamatan as $key => $value) {
                        $rencana_salur = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$value->kecamatan)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$value->kecamatan)->where('tgl_serah','!=','')->first()->total;
                        $kuantum = $rencana_salur;
                        $pbp = $pbp_total;
                        if($kuantum==0){
                    // echo $value->kabupaten;
                    continue;
                }
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                $persen_sisa = ($sisa/$kuantum)*100;
                        // $arr_name = trim($value->kecamatan," ");
                       
                            $data[] = [
                                "nama"=> $value->kecamatan,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        
                    }
                }
                
            }else{
                if($request->kel==''){
                    foreach ($kab as $k => $v) {
                     $kelurahan = DB::connection($request->db)->table($v->kode_map)->select('kelurahan')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->groupBy('kelurahan')->get();
                     if($kelurahan->count()==0){
                        continue;
                     }
                     foreach ($kelurahan as $key => $value) {
                        $rencana_salur = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('kelurahan',$value->kelurahan)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('kelurahan',$value->kelurahan)->where('tgl_serah','!=','')->first()->total;
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                    // echo $value->kabupaten;
                    continue;
                }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                $persen_sisa = ($sisa/$kuantum)*100;
                        $data[] = ["nama"=>$value->kelurahan,"kuantum"=>number_format((float)$kuantum,0,",","."), "transporter"=>number_format((float)$transporter,0,",","."), "persen_transporter"=>$persen_transporter, "pbp"=>number_format((float)$pbp,0,",","."), "persen_pbp"=>number_format($persen_pbp,2), "sisa"=>number_format((int)$sisa,0,",","."), "persen_sisa"=>number_format($persen_sisa,2)];
                    }
                    }

                }else{
                    foreach ($kab as $k => $v) {
                     $person = DB::connection($request->db)->table($v->kode_map)->select('*')
                     ->where('kabupaten',$request->kab)
                     ->where('kecamatan',$request->kec)
                     ->where('kelurahan',$request->kel);

                     if($request->tgl_serah == 'true'){
                        $person = $person->where('tgl_serah','!=','');
                     }else if($request->tgl_serah == 'false'){
                        $person = $person->where('tgl_serah','');
                     }
                     $person = $person->get();
                     if($person->count()==0){
                        continue;
                     }
                     $db = ($request->db=='mysql')?'jatim_op_db':$request->db;
                     $status_penerima = [1=>'Penerima Sendiri','Pengganti','Perwakilan','Kolektif'];
                     $status_penerima_class = [1=>'bg-success','bg-primary','bg-warning','bg-dark'];
                     foreach ($person as $key => $value) {
                        $haystack = $value->path_pbp;
                        $needle = 'drive';
                            if (strpos($haystack, $needle) !== false) {
                                $person[$key]->path_pbp = $value->path_pbp;
                            }else{
                                $person[$key]->path_pbp = asset('uploads/'.$db.'/pbp/pbp_'.$value->prefik.'.jpg');
                            }
                       
                       $person[$key]->status_penerima_class = $status_penerima_class[$person[$key]->status_penerima];
                       $person[$key]->status_penerima = $status_penerima[$person[$key]->status_penerima];
                       
                       $data[] = $person[$key];
                    }
                    }

                }
            }
        }


        return Response::JSON($data);
    }



    public function realTahapTableTotal(Request $request){
        // $db = DB::connection($request->db);
        if($request->db=='mysql'){
            $users = DB::table('data_map')->select('kabupaten','kode_map')->groupBy('kode_map')->get();
        }else{
            $users = DB::table('users')->selectRaw('name as kode_map, email as kabupaten')->where('db', $request->db)->where('role','!=','0')->groupBy('name')->get();
        }
        
         $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        if($request->kab==''){
              
                
            foreach ($users as $key => $value) {
                if($request->db == 'mysql'){
                    
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$value->kabupaten)->groupBy('kecamatan')->get();
                    foreach ($kode_map as $key2 => $value2) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value2->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan', $value2->kecamatan)->first()->total;

                        $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->where('path_ktp','B')->first()->total;

                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        
                        $kuantum += $rencana_salur;
                    }

                     $pbp = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, path_ktp')
                     ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik');

                     if($request->pbp == 'utama'){
                            $pbp = $pbp->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp = $pbp->where('path_ktp','B');
                        }
                     $pbp = $pbp->first()->total;
                }else{
                    // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                    $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->first()->total;
                    // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->first()->total;
                    $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, path_ktp')
                        ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik');
                    if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                    $pbp_total = $pbp_total->where('t.kprk', $value->kode_map)->first()->total;

                    $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('path_ktp','B')->first()->total;
                    if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                    $kuantum += $rencana_salur;
                    $pbp += $pbp_total;
                
                }
                
            }
            $sisa = $kuantum-$pbp;
            $persen_pbp = ($pbp/$kuantum)*100;
            $persen_sisa = ($sisa/$kuantum)*100;

        }else{
            if($request->db=='mysql'){
                $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();
            }else{
                $kab = DB::connection($request->db)->table('users')->selectRaw('name as kode_map')->where('email',$request->kab)->groupBy('kode_map')->get();
            }
            if($request->kec==''){
               
               

                foreach ($kab as $key => $value) {
                    // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                   $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->first()->total;
                    // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik',$tahap)->first()->total;
                   $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, path_ktp')
                        ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik');

                    if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->where('t.kprk', $value->kode_map)->first()->total;
                    $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('path_ktp','B')->first()->total;
                    if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                    $kuantum += $rencana_salur;
                    $pbp += $pbp_total;
                }

                $sisa = $kuantum-$pbp;
                $persen_pbp = ($pbp/$kuantum)*100;
                $persen_sisa = ($sisa/$kuantum)*100;

            }else{
                if($request->kel==''){

                    foreach ($kab as $key => $value) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kecamatan',$request->kec)->first()->total;
                        $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $value->kode_map)
                        ->where('kecamatan', $request->kec);


                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        $pbp_total = $pbp_total->first()->total;
                        $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                       
                    }
                    $sisa = $kuantum-$pbp;
                    $persen_pbp = ($pbp/$kuantum)*100;
                    $persen_sisa = ($sisa/$kuantum)*100;

                }else{
                    foreach ($kab as $key => $value) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kecamatan',$request->kec)->where('kelurahan',$request->kel)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.kelurahan, k.path_ktp')
                        ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $value->kode_map)
                        ->where('kecamatan', $request->kec)
                        ->where('kelurahan', $request->kel);

                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->first()->total;

                        $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$request->kec)->where('kelurahan', $request->kel)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                    }
                    $sisa = $kuantum-$pbp;
                    $persen_pbp = ($pbp/$kuantum)*100;
                    $persen_sisa = ($sisa/$kuantum)*100;
                }
            }
        }


        return Response::JSON(["kuantum"=>number_format((int)$kuantum,0,",","."), "transporter"=>number_format((int)$transporter,0,",","."), "persen_transporter"=>$persen_transporter, "pbp"=>number_format((int)$pbp,0,",","."), "persen_pbp"=>number_format($persen_pbp,2), "sisa"=>number_format((int)$sisa,0,",","."), "persen_sisa"=>number_format($persen_sisa,2)]);
    }

    public function realTahapTableAll(Request $request){
        // $db = DB::connection($request->db);
        if($request->db == 'mysql'){
            $users = DB::table('data_map')->select('kabupaten','kode_kab')->groupBy('kabupaten')->get();
            
        }else{
            $users = DB::table('users')->selectRaw('name as kode_map, email as kabupaten, id as kode_kab')->where('db', $request->db)->where('role','!=','0')->groupBy('name')->get();
        }
        
         $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        $data = [];

        if($request->kab==''){
           
              if($request->db == 'mysql'){
                foreach ($users as $key => $value) {
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$value->kabupaten)->groupBy('kecamatan')->get();

                    foreach ($kode_map as $key2 => $value2) {
                         // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value2->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kecamatan',$value2->kecamatan)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan')
                        ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $value2->kode_map)
                        ->where('kecamatan', $value2->kecamatan)
                        ->first()->total;

                        $rencana_salur_b = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            // echo $value->kabupaten;
                            continue;
                        }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                        $persen_sisa = ($sisa/$kuantum)*100;

                        if (array_key_exists($value->kode_kab,$data)){
                            $data[$value->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$value->kode_kab]['pbp_r'] += $pbp;
                            $data[$value->kode_kab]['sisa_r'] += $sisa;

                            $data[$value->kode_kab]['kuantum'] = number_format((int)$data[$value->kode_kab]['kuantum_r'],0,",",".");
                            $data[$value->kode_kab]['pbp'] = number_format((int)$data[$value->kode_kab]['pbp_r'],0,",",".");
                            $data[$value->kode_kab]['sisa'] = number_format((int)$data[$value->kode_kab]['sisa_r'],0,",",".");
                        }else{
                            $data[$value->kode_kab] = [
                                "nama"=> $value->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                       break;
                    }
                    break;
                }

              }else{
                    foreach ($users as $key => $value) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                        
                        $rencana_salur = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$value->kabupaten)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kabupaten',$value->kabupaten)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kabupaten, k.path_ktp')
                        ->leftJoin($value->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $value->kode_map)
                        ->where('kabupaten', $value->kabupaten);
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->first()->total;

                         $rencana_salur_b = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$value->kecamatan)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            // echo $value->kabupaten;
                            continue;
                        }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                        $persen_sisa = ($sisa/$kuantum)*100;

                        if (array_key_exists($value->kode_kab,$data)){
                            $data[$value->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$value->kode_kab]['pbp_r'] += $pbp;
                            $data[$value->kode_kab]['sisa_r'] += $sisa;

                            $data[$value->kode_kab]['kuantum'] = number_format((int)$data[$value->kode_kab]['kuantum_r'],0,",",".");
                            $data[$value->kode_kab]['pbp'] = number_format((int)$data[$value->kode_kab]['pbp_r'],0,",",".");
                            $data[$value->kode_kab]['sisa'] = number_format((int)$data[$value->kode_kab]['sisa_r'],0,",",".");
                        }else{
                            $data[$value->kode_kab] = [
                                "nama"=> $value->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                       
                    }
              }
                
            

            


        }else{
            // $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();
            if($request->db=='mysql'){

                $kab = DB::connection($request->db)->table('data_map')->select('kode_map')->where('kabupaten',$request->kab)->groupBy('kode_map')->get();

            }else{
                $kab = DB::connection($request->db)->table('users')->selectRaw('name as kode_map')->where('email',$request->kab)->groupBy('kode_map')->get();
            }

            if($request->kec==''){
                foreach ($kab as $k => $v) {
                    // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$v->kode_map)->get()->pluck('prefik');
                    $kecamatan = DB::connection($request->db)->table($v->kode_map)->select('kecamatan')->groupBy('kecamatan')->get();
                    if($kecamatan->count()==0){
                        continue;
                     }
                    foreach ($kecamatan as $key => $value) {
                        $rencana_salur = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$value->kecamatan)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kabupaten',$request->kab)->where('kecamatan',$value->kecamatan)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.kabupaten, k.path_ktp')
                        ->leftJoin($v->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $v->kode_map)
                        ->where('kecamatan', $value->kecamatan)
                        ->where('kabupaten', $request->kab);
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->first()->total;

                        $rencana_salur_b = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten', $request->kab)->where('kecamatan',$value->kecamatan)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        $pbp = $pbp_total;
                        if($kuantum==0){
                    // echo $value->kabupaten;
                    continue;
                }
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                $persen_sisa = ($sisa/$kuantum)*100;
                        // $arr_name = trim($value->kecamatan," ");
                       
                            $data[] = [
                                "nama"=> $value->kecamatan,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        
                    }
                }
                
            }else{
                if($request->kel==''){
                    foreach ($kab as $k => $v) {
                     $kelurahan = DB::connection($request->db)->table($v->kode_map)->select('kelurahan')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->groupBy('kelurahan')->get();
                     if($kelurahan->count()==0){
                        continue;
                     }
                     foreach ($kelurahan as $key => $value) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$v->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('kelurahan',$value->kelurahan)->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('kelurahan',$value->kelurahan)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        ->leftJoin($v->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $v->kode_map)
                        ->where('kabupaten', $request->kab)
                        ->where('kecamatan', $request->kec)
                        ->where('kelurahan', $value->kelurahan);
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->first()->total;

                        $rencana_salur_b = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('path_ktp','B')->first()->total;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                    // echo $value->kabupaten;
                    continue;
                }
                        $pbp = $pbp_total;
                        $persen_pbp = ($pbp/$kuantum)*100;
                        $sisa = $kuantum-$pbp;
                $persen_sisa = ($sisa/$kuantum)*100;
                        $data[] = ["nama"=>$value->kelurahan,"kuantum"=>number_format((float)$kuantum,0,",","."), "transporter"=>number_format((float)$transporter,0,",","."), "persen_transporter"=>$persen_transporter, "pbp"=>number_format((float)$pbp,0,",","."), "persen_pbp"=>number_format($persen_pbp,2), "sisa"=>number_format((int)$sisa,0,",","."), "persen_sisa"=>number_format($persen_sisa,2)];
                    }
                    }

                }else{
                    // print_r($kab[0]->kode_map);
                    // die();
                    foreach ($kab as $k => $v) {
                    $tahap = DB::connection($request->db)->table($request->tahap." as t")
                    ->select('t.prefik','k.kabupaten','k.kecamatan','k.kelurahan','k.path_ktp','k.nama','k.alamat','k.prefik','t.path_pbp','t.status_penerima')
                    ->leftJoin($v->kode_map.' as k','t.prefik','=','k.prefik')
                    ->where('t.kprk',$v->kode_map)
                    ->where('kabupaten',$request->kab)
                     ->where('kecamatan',$request->kec)
                     ->where('kelurahan',$request->kel);
                        if($request->pbp == 'utama'){
                            $tahap = $tahap->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $tahap = $tahap->where('path_ktp','B');
                        }
                    $tahap = $tahap->get();

                        // print_r($tahap);die();
                     $person = DB::connection($request->db)->table($v->kode_map)->select('*')
                     ->where('kabupaten',$request->kab)
                     ->where('kecamatan',$request->kec)
                     ->where('kelurahan',$request->kel);

                      if($request->pbp == 'utama'){
                            $person = $person->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $person = $person->where('path_ktp','B');
                        }

                    if(sizeof($tahap)>0){
                        if($request->tgl_serah == 'true'){
                        $person = $tahap;
                     }else if($request->tgl_serah == 'false'){

                        $person = $person->whereNotIn('prefik',$tahap->pluck('prefik'));
                        $person = $person->get();
                     }
                    }
                     // die();
                     
                     if($person->count()==0){
                        // echo $v->kode_map;
                        continue;
                     }

                     $db = ($request->db=='mysql')?'jatim_op_db':$request->db;
                     $status_penerima = [1=>'Penerima Sendiri','Pengganti','Perwakilan','Kolektif'];
                     $status_penerima_class = [1=>'bg-success','bg-primary','bg-warning','bg-dark'];
                     foreach ($person as $key => $value) {
                        if($request->tgl_serah == 'true'){
                            // $t = DB::connection(Auth::user()->db)->table($request->tahap)->where('kprk',$v->kode_map)->where('prefik',$value->prefik)->first();
                            // if(isset($t->path_pbp)){
                                $haystack = $value->path_pbp;
                            $needle = 'drive';
                                if (strpos($haystack, $needle) !== false) {
                                    $person[$key]->path_pbp = $value->path_pbp;
                                }else{
                                    $person[$key]->path_pbp = asset('uploads/'.$db.'/pbp/pbp_'.$value->prefik.'.jpg');
                                }
                           
                           $person[$key]->status_penerima_class = $status_penerima_class[$value->status_penerima];
                           $person[$key]->status_penerima = $status_penerima[$value->status_penerima];
                            // }
                            
                        }
                        
                        
                       
                       $data[] = $person[$key];
                    }
                    }

                }
            }
        }


        return Response::JSON($data);
    }

    public function realTahapTableKabList(Request $request){
        if($request->db == 'mysql'){
            $users = DB::table('data_map as dm')->select('dm.kabupaten','dm.kode_kab','dm.kode_map','s.ip')
            ->leftJoin('server_mapping as sm','dm.kode_map','=','sm.kprk')
            ->leftJoin('servers as s','sm.server','=','s.name')
            ->groupBy('kabupaten')->get();
            
        }else{
            $users = DB::table('users')->selectRaw('name as kode_map, email as kabupaten, id as kode_kab')->where('db', $request->db)->where('role','!=','0')->groupBy('name')->get();
        }

        return Response::JSON($users);

    }

    public function realTahapTableTotalKab(Request $request){
        $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        
              
                if($request->db == 'mysql'){
                    
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$request->kabupaten)->groupBy('kecamatan')->get();
                    foreach ($kode_map as $key2 => $value2) {
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value2->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan', $value2->kecamatan)->first()->total;
                        $rencana_salur_b = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan', $value2->kecamatan)->where('path_ktp','B')->first()->total;


                        $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik')
                        ->where('t.kprk', $value2->kode_map);
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        $pbp_total = $pbp_total->where('kecamatan', $value2->kecamatan)->first()->total;

                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }

                        $kuantum += $rencana_salur;
                        $pbp += $pbp_total;
                    }
                }else{
                    // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                    $rencana_salur = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->first()->total;
                    $rencana_salur_b = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->where('path_ktp','B')->first()->total;
                    // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->first()->total;
                    $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.path_ktp')
                        ->leftJoin($request->kode_map." as k",'t.prefik','=','k.prefik');
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->where('t.kprk', $request->kode_map)->first()->total;

                    if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }

                    $kuantum += $rencana_salur;
                    $pbp += $pbp_total;
                
                }
                
            
            $sisa = $kuantum-$pbp;
            $persen_pbp = ($pbp/$kuantum)*100;
            $persen_sisa = ($sisa/$kuantum)*100;

             return Response::JSON(["kuantum"=>number_format((int)$kuantum,0,",","."), "transporter"=>number_format((int)$transporter,0,",","."), "persen_transporter"=>$persen_transporter, "pbp"=>number_format((int)$pbp,0,",","."), "persen_pbp"=>number_format($persen_pbp,2), "sisa"=>number_format((int)$sisa,0,",","."), "persen_sisa"=>number_format($persen_sisa,2)]);
        
    }

    public function realTahapTableAllKab(Request $request){
        // $url = 'http://'.$request->ip.'/pbp-app/public/index.php/api/realisasi/tahap/table/all/kab';
        // $curlPost = http_build_query($request->all()); 
        //     $ch = curl_init();         
        //     curl_setopt($ch, CURLOPT_URL, $url);         
        //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
        //     curl_setopt($ch, CURLOPT_POST, 1);         
        //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
        //     curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);     
        //     $data = curl_exec($ch); 
        //     // $data = json_decode(curl_exec($ch), true); 
        //     $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
        //     if ($http_code != 200) { 
        //         $error_msg = 'Failed to receieve access token'; 
        //         if (curl_errno($ch)) { 
        //             $error_msg = curl_error($ch); 
        //             return Response::JSON(['status'=>false,'msg'=>$error_msg]);
        //         } 
        //     }

        //     return Response::JSON($data);

        
         $kuantum = 0;
                $transporter = 0;
                $persen_transporter = 0;
                $pbp = 0;
        $data = [];
        if($request->db == 'mysql'){
                
                    $kode_map = DB::connection($request->db)->table('data_map')->select('kecamatan','kode_map')->where('kabupaten',$request->kabupaten)->groupBy('kecamatan')->get();



                    foreach ($kode_map as $key2 => $value2) {
                         // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value2->kode_map)->get()->pluck('prefik');
                        $rencana_salur = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->first()->total;
                        $rencana_salur_b = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total')->where('kecamatan',$value2->kecamatan)->where('path_ktp','B')->first()->total;
                        $kecamatan = DB::connection($request->db)->table($value2->kode_map)->select('prefik')->where('kecamatan',$value2->kecamatan)->get()->pluck('prefik');
                        // $pbp_total = DB::connection($request->db)->table($value2->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kecamatan',$value2->kecamatan)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik');
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        $pbp_total = $pbp_total->where('t.kprk', $value2->kode_map)
                        ->where('kecamatan', $value2->kecamatan)
                        ->first()->total;

                        // $pbp_total = 0;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            continue;
                        }else{
                             $pbp = $pbp_total;
                             $persen_pbp = ($pbp/$kuantum)*100;
                             $sisa = $kuantum-$pbp;
                             $persen_sisa = ($sisa/$kuantum)*100;
                        }
                       

                        if (array_key_exists($request->kode_kab,$data)){
                            $data[$request->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$request->kode_kab]['pbp_r'] += $pbp;
                            $data[$request->kode_kab]['sisa_r'] += $sisa;

                            $data[$request->kode_kab]['persen_pbp'] = number_format(($data[$request->kode_kab]['pbp_r']/$data[$request->kode_kab]['kuantum_r'])*100,2);
                            $data[$request->kode_kab]['persen_sisa'] = number_format(($data[$request->kode_kab]['sisa_r']/$data[$request->kode_kab]['kuantum_r'])*100,2);

                            $data[$request->kode_kab]['kuantum'] = number_format((int)$data[$request->kode_kab]['kuantum_r'],0,",",".");
                            $data[$request->kode_kab]['pbp'] = number_format((int)$data[$request->kode_kab]['pbp_r'],0,",",".");
                            $data[$request->kode_kab]['sisa'] = number_format((int)$data[$request->kode_kab]['sisa_r'],0,",",".");
                        }else{
                            $data[$request->kode_kab] = [
                                "nama"=> $request->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                    }
                   
                

              }else{
                   
                        // $tahap = DB::connection(Auth::user()->db)->table($request->tahap)->select('prefik')->where('kprk',$value->kode_map)->get()->pluck('prefik');
                        
                        $rencana_salur = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kabupaten)->first()->total;
                        $rencana_salur_b = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kabupaten)->where('path_ktp','B')->first()->total;
                        // $pbp_total = DB::connection($request->db)->table($value->kode_map)->selectRaw('count(*)as total, prefik')->whereIn('prefik', $tahap)->where('kabupaten',$value->kabupaten)->first()->total;
                         $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kabupaten, k.path_ktp')
                        ->leftJoin($request->kode_map." as k",'t.prefik','=','k.prefik');
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }
                        $pbp_total = $pbp_total->where('t.kprk', $request->kode_map)
                        ->where('kabupaten', $request->kabupaten)
                        ->first()->total;

                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            $pbp = $pbp_total;
                            $persen_pbp = 0;
                            $sisa = $kuantum-$pbp;
                            $persen_sisa = 0;
                        }else{
                            $pbp = $pbp_total;
                            $persen_pbp = ($pbp/$kuantum)*100;
                            $sisa = $kuantum-$pbp;
                            $persen_sisa = ($sisa/$kuantum)*100;
                        }
                       

                        if (array_key_exists($request->kode_kab,$data)){
                            $data[$request->kode_kab]['kuantum_r'] += $kuantum;
                            $data[$request->kode_kab]['pbp_r'] += $pbp;
                            $data[$request->kode_kab]['sisa_r'] += $sisa;

                            $data[$request->kode_kab]['kuantum'] = number_format((int)$data[$request->kode_kab]['kuantum_r'],0,",",".");
                            $data[$request->kode_kab]['pbp'] = number_format((int)$data[$request->kode_kab]['pbp_r'],0,",",".");
                            $data[$request->kode_kab]['sisa'] = number_format((int)$data[$request->kode_kab]['sisa_r'],0,",",".");
                        }else{
                            $data[$request->kode_kab] = [
                                "nama"=> $request->kabupaten,
                                "kuantum"=>number_format((int)$kuantum,0,",","."), 
                                "transporter"=>number_format((int)$transporter,0,",","."), 
                                "persen_transporter"=>$persen_transporter, 
                                "pbp"=>number_format((int)$pbp,0,",","."), 
                                "persen_pbp"=>number_format($persen_pbp,2), 
                                "sisa"=>number_format((int)$sisa,0,",","."), 
                                "persen_sisa"=>number_format($persen_sisa,2),
                                "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                            ];
                        }


                       
                    
              }

              return Response::JSON($data);
    }

    public function bastForm(){
    	$data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
    	$data['wil'] = Auth::user()->name;
    	$data['bulans'] = $this->bulans[date('m')];
        $data['bulan'] = array (
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    	return view('user/bast-form')->with($data);
    }

    public function generateBast(Request $request){

    	$enabled = DB::table('settings')->where('name','enable_generate_bast')->first();
    	if($enabled->value=='0'){
    		return back()->with('error','Fungsi cetak sedang nonaktif');
    	}

    	if(Auth::user()->role==5){
			$user = DB::table('users')->where('id', Auth::user()->id)->first();
		}else if(Auth::user()->role==0){
			$user = DB::table('users')->where('name', $request->wilayah)->first();
		}else{
			return back()->with('error','Anda tidak memiliki hak akses cetak');
		}
    		
    		$list = DB::connection($user->db)->table($user->name)
    		->where("kabupaten", $request->kabupaten)
    		->where("kecamatan", $request->kecamatan)
    		->where("kelurahan", $request->kelurahan);

            if($request->jenis_penerima=='tambahan'){
                $list = $list->where('path_ktp','B');
            }else if($request->jenis_penerima=='utama'){
                $list = $list->where('path_ktp', '');
                
            }

    		$list = $list->orderBy("no_urut","asc")
    		// ->limit("35")
    		->get();
         $provinsi = DB::connection($user->db)->table($user->name)->first()->provinsi;

         $kode_kel = DB::connection($user->db)->table('data_kelurahan')
            ->where("kabupaten", $request->kabupaten)
            ->where("kecamatan", $request->kecamatan)
            ->where("kelurahan", $request->kelurahan)->first()->kode_kel;

    		$chunk = array_chunk($list->toArray(), 15);


$bulan = array (
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

            $bulan_num = array_search($request->bulan, $bulan);

    		$data = [
    			"tahun"=> $request->tahun,
    			"bulans"=> $request->bulan,
    			"provinsi"=> $provinsi,
            "kabupaten"=> $request->kabupaten,
    			"kecamatan"=> $request->kecamatan,
    			"kelurahan"=> $request->kelurahan,
    			"list" => $chunk,
    			"kprk" => $list[0]->kprk,
    			"prefik" => $list[0]->prefik,
                "bulan_num" => $bulan_num,
                "kode_kel" => $kode_kel
    		];

    	$pdf = PDF::chunkLoadView('<html-separator/>','layout.bast-new', $data, [], [
		    'title' => 'Another Title',
		    'margin_top' => 0
		]);



        return $pdf->stream(time().'.pdf');
    }

    public function undanganForm(){
    	$data['wilayah'] = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
    	$data['wil'] = Auth::user()->name;
    	$data['bulans'] = $this->bulans[date('m')];
    	return view('user/undangan-form')->with($data);
    }

    public function generateUndangan(Request $request){
    	$enabled = DB::table('settings')->where('name','enable_generate_undangan')->first();
    	if($enabled->value=='0'){
    		return back()->with('error','Fungsi cetak sedang nonaktif');
    	}
    	if(Auth::user()->role==5){
			$user = DB::table('users')->where('id', Auth::user()->id)->first();
		}else if(Auth::user()->role==0){
			$user = DB::table('users')->where('name', $request->wilayah)->first();
		}else{
			return back()->with('error','Anda tidak memiliki hak akses cetak');
		}
    		$list = DB::connection($user->db)->table($user->name)
         ->where("kabupaten", $request->kabupaten)
    		->where("kecamatan", $request->kecamatan)
    		->where("kelurahan", $request->kelurahan);

            if($request->jenis_penerima=='tambahan'){
                $list = $list->where('path_ktp','B');
            }else if($request->jenis_penerima=='utama'){
                $list = $list->whereIn('path_ktp', ['',NULL]);
            }

    		$list = $list->orderBy("no_urut","asc")
    		// ->limit("6")
    		->get();

         $provinsi = DB::connection($user->db)->table($user->name)->first()->provinsi;

    		// print_r($list->count());
    		// die();
    		$chunk = array_chunk($list->toArray(), 3);
    		$data = [
    			"tahun"=> $request->tahun,
    			"bulans"=> $request->bulan,
    			"provinsi"=> $provinsi,
            "kabupaten"=> $request->kabupaten,
    			"kecamatan"=> $request->kecamatan,
    			"kelurahan"=> $request->kelurahan,
    			"list" => $chunk,
    			"kprk" => $list[0]->kprk,
    			"prefik" => $list[0]->prefik
    		];
    		// return view("layout.undangan")->with($data);
    	$pdf = PDF::chunkLoadView('<html-separator/>','layout.undangan', $data, [], [
		    'title' => 'Another Title',
		    'margin_top' => 0
		]);

		$pdf->showImageErrors = true;


        return $pdf->stream(time().'.pdf');
    }
}
