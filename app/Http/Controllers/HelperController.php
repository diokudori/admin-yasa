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
}
