<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Response;
class HelperController extends Controller
{

	public function generatePrefixNew(Request $request){
        die();
		//insert new user from user_temp. use $table to set the temp table
		// $user = DB::table($request->table)->select('*')->get();
    	// foreach ($user as $key => $value) {
    	// 	$password = \Hash::make($value->password);
    	// 	DB::table('users')->updateOrinsert(
        //         [
        //             'name'=>$value->name,
        //             'email'=>$value->email,
        //             'officer_name'=>$value->officer_name,
        //             'username'=>$value->username,
        //         ],
    	// 		[    				
    	// 			'password'=>$password,
    	// 			'role'=>$value->role,
    	// 			'db'=>$value->db
    	// 		]);
    	// }

    	//generate no urut
    	 $table = DB::table($request->table)->get();
   


   foreach($table as $t => $tv){
        
        $kec = DB::connection($tv->db)->table($tv->name)->select("kecamatan")->groupBy("kecamatan")->get();
        foreach($kec as $k => $v){
            $kel = DB::connection($tv->db)->table($tv->name)->select("kelurahan")->where("kecamatan", $v->kecamatan)->groupBy("kelurahan")->get();
            foreach($kel as $k1 => $v1){
                $d = DB::connection($tv->db)->table($tv->name)->select("*")
                ->where("kecamatan", $v->kecamatan)
                ->where("kelurahan", $v1->kelurahan)
                ->orderBy("nama","asc")->get();
               $kode = DB::connection($tv->db)->table('data_kelurahan')->where('kabupaten', $d[0]->kabupaten)->where('kecamatan', $v->kecamatan)->where('kelurahan', $v1->kelurahan)->first();
               $counter = 1;
               foreach($d as $k2 => $v2){
	               	if($kode!=null){
	               		$prefik =  $kode->kode_kel.sprintf("%04s", $counter);
	               		$arr = ["no_urut"=>$counter, "prefik"=>$prefik];
	               	}else{
	               		$arr = ["no_urut"=>$counter];
	               	}
                    try{
                        DB::connection($tv->db)->table($tv->name)->where("id",$v2->id)->update($arr);
                        $counter++;
                    }catch(\Exception $err){
                        echo 'prefik : '.$tv->name.', kabupaten : '. $d[0]->kabupaten.', kecamatan : '. $v->kecamatan.', kelurahan : '.$v1->kelurahan."\n";
                    }
                    
               }
            }

           
        }
   }

    return Response::JSON(['status'=>true]);

	}


    public function generatePrefix(Request $request){
    	// die();
    	

    	// $kab = DB::table('kabupaten')->where('kabupaten','=',strtoupper($value->kabupaten))->first();
     //    $kel = DB::table('kelurahan')->where('kode_kel','like',$kab->kode_kab.'%')->where('kelurahan',strtoupper($value->kelurahan))->first();
     //    $kec_bulog = DB::table('kec_bulog')->where('kecamatan',strtoupper($value->kecamatan))->first();
        // $kab = DB::table('kabupaten')->select('*')->get()->toArray();
        // $kel = DB::table('kelurahan')->select('*')->get()->toArray();
        // $kec_bulog = DB::table('kec_bulog')->select('*')->get()->toArray();
        $counter = 0;
        $limit = 500;
        // $kode_kab = array_search('green', $array); 
        // $user = DB::table('users')->select('name')->where('name','!=','dashboard')->where('name','!=','64400')->where('name','!=','60000')->groupBy('name')->get();
        // foreach ($user as $k => $v) {
        $user = DB::table('users')->where('name', $request->prefik)->first();
        	do{
		    	$skip = $counter*$limit;

                    if(isset($request->kecamatan)){
                        $danom = DB::connection($user->db)->table($request->prefik)->select('*')->where('kecamatan', $request->kecamatan)->skip($skip)->limit($limit)->get();
                    }else{
                        $danom = DB::connection($user->db)->table($request->prefik)->select('*')->skip($skip)->limit($limit)->get();
                    }
			    	
                    
			   
			        foreach ($danom as $key => $value) {
			            try{
			            	$kode = DB::connection($user->db)->table('data_kelurahan')->where('kabupaten', $value->kabupaten)->where('kecamatan', $value->kecamatan)->where('kelurahan', $value->kelurahan)->first();
			            	if($kode!=null){
			            			$prefik =  $kode->kode_kel.sprintf("%04s", $value->no_urut);
			            			$update = DB::connection($user->db)->table($request->prefik)->where('id',$value->id)->update(['prefik'=>$prefik]);
			            // 			echo $v->name;
        							// echo "\n";
			            	} else{
			            		echo $value->kabupaten.", ".$value->kecamatan.", ".$value->kelurahan."\n";
			            		continue;
			            	}
			            	
			            }catch(Illuminate\Foundation\Bootstrap\HandleExceptions $ex){
			            	continue;
			            }
			        }
		        	$counter++;
		    	}while($danom->count()>0);
		    	

		    	// echo "Sukses mengupdate prefik ".$request->prefik;
		    	// die();
        // }

		    	return Response::JSON(["status"=>true, "prefik"=>$request->prefik]);
    	
    	
    }

    public function generatePrefixB(Request $request){
        $request->table = 'temp_tambahan';
        $table = DB::connection($request->db)->table($request->table)->get();

       foreach($table as $t => $tv){
            
            $kec = DB::connection($request->db)->table($request->table)->select("kecamatan")->groupBy("kecamatan")->get();
            foreach($kec as $k => $v){
                $kel = DB::connection($request->db)->table($request->table)->select("kelurahan")->where("kecamatan", $v->kecamatan)->groupBy("kelurahan")->get();
                foreach($kel as $k1 => $v1){
                    $d = DB::connection($request->db)->table($request->table)->select("*")
                    ->where("kecamatan", $v->kecamatan)
                    ->where("kelurahan", $v1->kelurahan)
                    ->where("no_urut", "0")
                    ->orderBy("nama","asc")->get();
                   // $kode = DB::connection($request->db)->table('data_kelurahan')->where('kabupaten', $d[0]->kabupaten)->where('kecamatan', $v->kecamatan)->where('kelurahan', $v1->kelurahan)->first();

                    $maxi = DB::connection($request->db)->table($request->table)->select(DB::RAW("MAX(no_urut)as maxi"))
                    ->where("kecamatan", $v->kecamatan)
                    ->where("kelurahan", $v1->kelurahan)->first()->maxi;
                   $counter = $maxi+1;

                   foreach($d as $k2 => $v2){
                        // if($kode!=null){
                            // print_r($v2->kode_kel);
                            // echo $v->kecamatan." & ".$v1->kelurahan;
                            $prefik =  "B".$v2->kode_kel.sprintf("%04s", $counter);
                            $arr = ["no_urut"=>$counter, "prefik"=>$prefik];
                        // }else{
                        //     $arr = ["no_urut"=>$counter];
                        // }
                        try{
                            DB::connection($request->db)->table($request->table)->where("id",$v2->id)->update($arr);
                            $counter++;
                        }catch(\Exception $err){
                            echo 'prefik : '.$tv->name.', kabupaten : '. $d[0]->kabupaten.', kecamatan : '. $v->kecamatan.', kelurahan : '.$v1->kelurahan."\n";
                        }
                        
                   }
                }

               
            }
       }
    }

    public function tambahanPbp(Request $request){
        // if (ob_get_level() == 0) ob_start();
        // die();
        $counter = 1;
        $kel = DB::connection($request->db)->table('temp_tambahan')->select('kprk')->groupBy('kprk')->get();
        foreach($kel as $k => $v){
            // $r = DB::connection($request->db)->table('temp_tambahan2')->where('kprk', $v->kprk)->get();
            echo '<a href="'.url('api/pbp/insert').'?db='.$request->db.'&kprk='.$v->kprk.'" target="_blank">'.$v->kprk.'</a><br>';

            // $client = new \GuzzleHttp\Client();
            //     $req = new \GuzzleHttp\Psr7\Request('GET', url('api/pbp/insert'), [
            //         'query'=>[
            //             'kprk'=>'65100',
            //             'db'=>$request->db,
            //         ]
                    
            //     ]);

                // $promise = $client->sendAsync($req)->then(function ($response) {
                //     // $j = json_decode($response->getBody());
                //     echo $response->getBody();
                //     // print_r($j);
                //     // echo 'kprk '.$j->kprk.' inserted <br>';
                // });

                // $promise->wait();
                // sleep(100);
                // echo "A".$v2->prefik;

                // $resp = DB::connection($request->db)->table($v2->kprk)->updateOrInsert(['prefik'=>$v2->prefik],
                //     [
                //         'nama'=>$v2->nama,
                //         'provinsi'=>$v2->provinsi,
                //         'kabupaten'=>$v2->kabupaten,
                //         'kecamatan'=>$v2->kecamatan,
                //         'kelurahan'=>$v2->kelurahan,
                //         'alamat'=>$v2->alamat,
                //         'rw'=>$v2->rw,
                //         'rt'=>$v2->rt,
                //         'umur'=>$v2->umur,
                //         'kprk'=>$v2->kprk,
                //         'path_ktp'=>$v2->path_ktp,
                //         'no_urut'=>$v2->no_urut,
                //     ]);

                // if($resp){
                //     echo $counter." prefik added: ".$v2->prefik."<br>";
                //     die();
                //     $counter++;
                // }
            
        }
        // ob_end_flush();
        // return Response::JSON(['status'=>true]);

    }

    public function insertPbpTambahan(Request $request){
        $r = DB::connection($request->db)->table('temp_tambahan')->where('kprk', $request->kprk)->get();
        foreach($r as $k => $v){
            $resp = DB::connection($request->db)->table($v->kprk)->updateOrInsert(['prefik'=>$v->prefik],
                    [
                        'nama'=>$v->nama,
                        'provinsi'=>$v->provinsi,
                        'kabupaten'=>$v->kabupaten,
                        'kecamatan'=>$v->kecamatan,
                        'kelurahan'=>$v->kelurahan,
                        'alamat'=>$v->alamat,
                        'rw'=>$v->rw,
                        'rt'=>$v->rt,
                        'umur'=>$v->umur,
                        'kprk'=>$v->kprk,
                        'path_ktp'=>$v->path_ktp,
                        'no_urut'=>$v->no_urut,
                    ]);
        }

        // echo $request->kprk;        

        return Response::JSON(['status'=>true, 'kprk'=>$request->kprk]);
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

    public function alterPrefix(){
    	$user = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
    	foreach ($user as $key => $value) {
    		DB::statement("ALTER TABLE `".$value->name."` CHANGE `prefik` `prefik` VARCHAR(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL");
    	}
    	return Response::JSON(["status"=>true]);
    }

    public function alterTable(){
    	$user = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
    	foreach ($user as $key => $value) {
    		DB::statement("ALTER TABLE `".$value->name."` ADD `status_penerima` INT NOT NULL DEFAULT '1' AFTER `tgl_upload`, ADD `pbp_uploaded` INT NOT NULL DEFAULT '0' AFTER `status_penerima`");
    	}
    	return Response::JSON(["status"=>true]);
    }

    public function truncateFields(){
    	$user = DB::table('users')->select('name')->where('id','!=','34')->groupBy('name')->get();
    	foreach ($user as $key => $value) {
    		DB::table($value->name)->update(['tgl_serah'=>'', 'transactor'=>NULL, 'path_ktp'=>NULL, 'path_pbp'=>NULL, 'tgl_upload'=>'']);
    	}
    	return Response::JSON(["status"=>true]);
    }

    public function generateUsername(Request $request){
    	$user = DB::table($request->table)->select('*')->get();
    	foreach ($user as $key => $value) {
    		$password = \Hash::make($value->password);
    		DB::table('users')->insert(
    			[
    				'name'=>$value->name,
    				'email'=>$value->email,
    				'officer_name'=>$value->officer_name,
    				'username'=>$value->username,
    				'password'=>$password,
    				'role'=>$value->role,
    				'db'=>$value->db
    			]);
    	}

    	return Response::JSON(["status"=>true]);
    }

    public function settingsTahap(){
        $data = DB::table('tahap')->where('name','like','%'.date('Y'))->get();
        return Response::JSON($data);
    }

    public function migrationList(Request $request){
        if($request->db=='jatim_op_db'){
            $request->db = 'mysql';
        }
        $user = DB::table('users')->select('name')->where('db',$request->db)->groupBy('name')->get();
        echo "<ul>";
        foreach ($user as $key => $value) {
            echo '<li><a href="'.url('api/migrating/').'/'.$request->db.'/'.$request->tahap.'/'.$value->name.'" target="_blank">'.$value->name.'</a></li>';
        }
        echo "</ul>";
    }

    public function migratePbp($db, $tahap, $kprk){
        $resp = DB::connection($db)->table($kprk)->select('kecamatan')->where('tgl_serah','!=','')->get();
        $string = "INSERT INTO `2023_OKT` (kprk, prefik, tgl_serah, transactor, path_pbp, tgl_upload, status_penerima, pbp_uploaded)
SELECT kprk, prefik, tgl_serah, transactor, path_pbp, tgl_upload, status_penerima, pbp_uploaded FROM `60400`
WHERE tgl_serah !='';";

    }

    public function migrateTahap($db, $tahap, $kprk){
        $resp = DB::connection($db)->table($tahap)->where('kprk',$kprk)->get();
        $kodetahap = strtolower($tahap)."_";
        foreach ($resp as $key => $value) {
            DB::table($kprk)->where('prefik',$value->prefik)->update(
            [
                $kodetahap.'tgl_serah'=>$value->tgl_serah, 
                $kodetahap.'transactor'=>$value->transactor, 
                $kodetahap.'path_pbp'=>$value->path_pbp, 
                $kodetahap.'tgl_upload'=>$value->tgl_upload, 
                $kodetahap.'status_penerima'=>$value->status_penerima, 
                $kodetahap.'pbp_uploaded'=>$value->pbp_uploaded
            ]
            );
        }

        return Response::JSON(['status'=>true]);
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
                            $pbp_total = $pbp_total->where('path_ktp',NULL);
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
                        $rencana_salur_b = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$value->kecamatan)->where('path_ktp','B')->first()->total;
                        
                        $tahap = strtolower($request->tahap)."_";
                        $pbp_total = DB::connection($request->db)
                        ->table($v->kode_map)
                        ->selectRaw("count(*)as total")
                        ->where('kecamatan', $value->kecamatan)
                        ->where('kabupaten', $request->kab)
                        ->where($tahap.'tgl_serah','!=','');

                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        $pbp_total = $pbp_total->first()->total;

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
                        $rencana_salur_b = DB::connection($request->db)->table($v->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kab)->where('kecamatan',$request->kec)->where('kelurahan',$value->kelurahan)->where('path_ktp','B')->first()->total;
                        
                        $tahap = strtolower($request->tahap)."_";
                        $pbp_total = DB::connection($request->db)
                        ->table($v->kode_map)
                        ->selectRaw("count(*)as total")
                        ->where('kecamatan', $request->kec)
                        ->where('kabupaten', $request->kab)
                        ->where('kelurahan', $value->kelurahan)
                        ->where($tahap.'tgl_serah','!=','');

                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        $pbp_total = $pbp_total->first()->total;

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
                        $data[] = [
                            "nama"=>$value->kelurahan,
                            "kuantum"=>number_format((float)$kuantum,0,",","."), 
                            "transporter"=>number_format((float)$transporter,0,",","."), 
                            "persen_transporter"=>$persen_transporter, 
                            "pbp"=>number_format((float)$pbp,0,",","."), 
                            "persen_pbp"=>number_format($persen_pbp,2), 
                            "sisa"=>number_format((int)$sisa,0,",","."), 
                            "persen_sisa"=>number_format($persen_sisa,2),
                            "kuantum_r"=>$kuantum,
                            "pbp_r"=>$pbp,
                            "sisa_r"=>$sisa
                        ];
                    }
                    }

                }else{
                    foreach ($kab as $k => $v) {
                     $tahap = strtolower($request->tahap)."_";
                        $person = DB::connection($request->db)
                        ->table($v->kode_map)
                        ->where('kecamatan', $request->kec)
                        ->where('kabupaten', $request->kab)
                        ->where('kelurahan', $request->kel);

                        if($request->pbp == 'utama'){
                            $person = $person->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $person = $person->where('path_ktp','B');
                        }
                        $kuantum = $person->count();
                        $transporter = 0;
                        $persen_transporter = 0;
                     if($request->tgl_serah == 'true'){
                        $person = $person->where($tahap.'tgl_serah','!=','');
                        $pbp_total = $person->count();
                     }else if($request->tgl_serah == 'false'){
                        $person = $person->where($tahap.'tgl_serah','=','');
                        $pbp_total = $kuantum-$person->count();
                     }

                     $person = $person->get();
                     if($person->count()==0){
                        continue;
                     }
                     $db = ($request->db=='mysql')?'jatim_op_db':$request->db;
                     $status_penerima = [1=>'Penerima Sendiri','Pengganti','Perwakilan','Kolektif'];
                     $status_penerima_class = [1=>'bg-success','bg-primary','bg-warning','bg-dark'];
                      $pbp = $pbp_total;
                             $persen_pbp = ($pbp/$kuantum)*100;
                             $sisa = $kuantum-$pbp;
                             $persen_sisa = ($sisa/$kuantum)*100;
                     foreach ($person as $key => $value) {
                        if($request->tgl_serah == 'true'){
                            // $t = DB::connection(Auth::user()->db)->table($request->tahap)->where('kprk',$v->kode_map)->where('prefik',$value->prefik)->first();

                            $haystack = $value->{$tahap.'path_pbp'};
                            $needle = 'drive';
                                if (strpos($haystack, $needle) !== false) {
                                    $person[$key]->path_pbp_tahap = $haystack;
                                }else{
                                    $person[$key]->path_pbp_tahap = asset('uploads/'.$request->tahap.'/pbp/'.$db.'/pbp_'.$value->prefik.'_'.$value->no_urut.'_'.$value->status_penerima.'.jpg');
                                }
                           
                           $person[$key]->status_penerima_class = $status_penerima_class[$value->{$tahap.'status_penerima'}];
                           $person[$key]->status_penerima = $status_penerima[$value->{$tahap.'status_penerima'}];
                        }
                        
                        
                       
                       $p[] = $person[$key];
                    }

                    $data[] = [
                    
                       "kuantum"=>number_format((float)$kuantum,0,",","."), 
                       "transporter"=>number_format((float)$transporter,0,",","."), 
                       "persen_transporter"=>$persen_transporter, 
                       "pbp"=>number_format((float)$pbp,0,",","."), 
                       "persen_pbp"=>number_format($persen_pbp,2), 
                       "sisa"=>number_format((int)$sisa,0,",","."), 
                       "persen_sisa"=>number_format($persen_sisa,2),
                       "kuantum_r"=>$kuantum,
                                "pbp_r"=>$pbp,
                                "sisa_r"=>$sisa,
                       "person"=>$p
                   ];
                    }

                }
            }
        }


        return Response::JSON($data);
    }

     public function realTahapTableAllKab(Request $request){
   
        
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
                         // $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        // ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik');
                        $tahap = strtolower($request->tahap)."_";
                        $pbp_total = DB::connection($request->db)
                        ->table($value2->kode_map)
                        ->selectRaw("count(*)as total")
                        ->where('kecamatan', $value2->kecamatan)
                        ->where($tahap.'tgl_serah','!=','');
                         // $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        // ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik');
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        // $pbp_total = $pbp_total->where('t.kprk', $value2->kode_map)
                        // ->where('kecamatan', $value2->kecamatan)
                        $pbp_total = $pbp_total->first()->total;

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
                   
                        $rencana_salur = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kabupaten)->first()->total;
                        $rencana_salur_b = DB::connection($request->db)->table($request->kode_map)->selectRaw('count(*)as total')->where('kabupaten',$request->kabupaten)->where('path_ktp','B')->first()->total;
                        $tahap = strtolower($request->tahap)."_";
                        $pbp_total = DB::connection($request->db)
                        ->table($request->kode_map)
                        ->selectRaw("count(*)as total")
                        ->where('kabupaten', $request->kabupaten)
                        ->where($tahap.'tgl_serah','!=','');
                         // $pbp_total = DB::connection($request->db)->table($request->tahap." as t")->selectRaw('count(t.prefik)as total, k.kecamatan, k.path_ktp')
                        // ->leftJoin($value2->kode_map." as k",'t.prefik','=','k.prefik');
                        if($request->pbp == 'utama'){
                            $pbp_total = $pbp_total->where('path_ktp','');
                        }else if($request->pbp == 'tambahan'){
                            $pbp_total = $pbp_total->where('path_ktp','B');
                        }

                        // $pbp_total = $pbp_total->where('t.kprk', $value2->kode_map)
                        // ->where('kecamatan', $value2->kecamatan)
                        $pbp_total = $pbp_total->first()->total;

                        // $pbp_total = 0;
                        if($request->pbp == 'utama'){
                            $rencana_salur = $rencana_salur - $rencana_salur_b;
                        }else if($request->pbp == 'tambahan'){
                            $rencana_salur = $rencana_salur_b;
                        }
                        $kuantum = $rencana_salur;
                        if($kuantum==0){
                            $kuantum = 1;
                        }
                        
                             $pbp = $pbp_total;
                             $persen_pbp = ($pbp/$kuantum)*100;
                             $sisa = $kuantum-$pbp;
                             $persen_sisa = ($sisa/$kuantum)*100;
                        
                       

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

              return Response::JSON($data);
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


     public function hitBulog($db, $tahap, $id, $hit_bulog, $dest='sandbox'){

     		if($dest=='live'){
     			$url = 'https://bpb.bulog.co.id/api/transporter/insert/';
     			$key = 'YAT_zvqXIcAOhy';
     		}else{
     			$url = 'https://bpb-sandbox.bulog.co.id/api/transporter/insert/';
     			$key = 'YAT_KEY_gshuy';
     		}
            
            if($hit_bulog=='1'){
                $param = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('id',$id)->first();
                // $param->transporter_key = 'YAT_zvqXIcAOhy';
                $param->transporter_key = $key;
                $param->kabupaten = $param->kab;
                $param->kecamatan = $param->kec;
                $param->kelurahan = $param->kel;
                $param->kuantum = $param->kuantum*10;
                // unset($param->no_out);
                $curlPost = http_build_query($param); 

                print_r($curlPost);
                // die();
                // echo $url;
                $fp = fopen(public_path().'/curlerrorlog.txt', 'w');
                $ch = curl_init();         
                curl_setopt($ch, CURLOPT_URL, $url);         
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         
                curl_setopt($ch, CURLOPT_POST, 1);         
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_STDERR, $fp);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);


                $data = json_decode(curl_exec($ch), true); 
                $http_code = curl_getinfo($ch,CURLINFO_HTTP_CODE); 
                if($dest=='live'){
                    $status_hit = 1;
                    $status_hit_sandbox = 0;
                }else{
                    $status_hit = 0;
                    $status_hit_sandbox = 1;
                }
               
                // print_r($data);
                // print_r($http_code);
                // print_r($curlPost);
                $id_bulog = (isset($data['data']['id']))?$data['data']['id']:'0';
                if ($http_code != 200) { 
                    $error_msg = 'Failed to receieve access token'; 
                    if (curl_errno($ch)) { 
                        $error_msg = curl_error($ch); 
                        print_r($error_msg);

                        $status_hit = 0;
                        $status_hit_sandbox = 0;
                    } 

                    $status_hit = 0;
                    $status_hit_sandbox = 0;
                    
                }

                if($data['status']==false){
                    $error_msg = $data['message'];
                }

                // echo $id_bulog;
                if ($id_bulog!=0) {
                    $update = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('id',$id)->update(['id_bulog' => $id_bulog, 'status_hit' => $status_hit, 'status_hit_sandbox'=>$status_hit_sandbox, 'error_message'=>$error_msg]);
                }
            }else{
                $data = ['status'=>false, 'message'=>'Hit bulog nonaktif'];
            
            }

            return json_encode($data);
    }

    public function hitBulogAuto($db, $tahap, $dest){
        $res = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('status_hit','0')->get();
        if($res->count()==0){
             echo "tidak ada data untuk diunggah";
           return;
        }
        $hit_bulog = DB::table('settings')->where('name','hit_bulog_enabled')->first()->value;
        foreach ($res as $key => $value) {
            $resp = json_decode($this->hitBulog($db,$tahap,$value->id, $hit_bulog, $dest));

            // if(isset($resp['status'])){
                // echo "sukses hit id: ".$value->id."</br>";
            // }else{
                print_r($resp);
                // die();
            // }
        }
    }

    public function generateBastDoc($db, $tahap){

        $resp = DB::connection($db)->table(strtoupper($tahap)."_data_gudang")->where('transporter_bast','')->get();

        foreach($resp as $v){
            $transporter_bast = 'BAST'.$v->kprk.$v->kecamatan_id.$v->id;
            $transporter_doc = 'SJLN'.$v->kprk.$v->kecamatan_id.$v->id;
            DB::connection($db)->table(strtoupper($tahap)."_data_gudang")
            ->where('id', $v->id)
            ->update([
                'transporter_bast'=>$transporter_bast,
                'transporter_doc'=>$transporter_doc
            ]);
        }

    }

    public function updateMigrasi()
    {
        // Ambil data dari tabel sumber
        $dataSumber = DB::table('2023_SEP')->where('kprk','69100')->get();

        // Loop melalui setiap data sumber
        foreach ($dataSumber as $sumber) {
            // Lakukan update pada tabel tujuan
            DB::table('69100')
                ->where('prefik', $sumber->prefik) // Sesuaikan dengan kolom kunci utama
                ->update([
                    '2023_sep_path_pbp' => $sumber->path_pbp,
                    // Tambahkan kolom-kolom lain sesuai kebutuhan
                ]);
        }

        return response()->json(['message' => 'Data berhasil diupdate']);
    }
}
