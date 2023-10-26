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
		$user = DB::table($request->table)->select('*')->get();
    	foreach ($user as $key => $value) {
    		$password = \Hash::make($value->password);
    		DB::table('users')->updateOrinsert(
                [
                    'name'=>$value->name,
                    'email'=>$value->email,
                    'officer_name'=>$value->officer_name,
                    'username'=>$value->username,
                ],
    			[    				
    				'password'=>$password,
    				'role'=>$value->role,
    				'db'=>$value->db
    			]);
    	}

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

			    	$danom = DB::connection($user->db)->table($request->prefik)->select('*')->skip($skip)->limit($limit)->get();
			   
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
}
