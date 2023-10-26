<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HelperController;
use Illuminate\Support\Facades\Auth;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::any('/login', function(Request $request){
    $version = '5.1';
    $data = $request->json()->all();
    // print_r($data);
    $password = \Hash::make($data['password']);
    $user = DB::table('users')->where('username',$data['username'])->get();
    if(isset($data['version'])){
        if($data['version']==$version){
            if($user->count()>0){
                $pass = Hash::check($data['password'], $user[0]->password);
                $batch = DB::table('settings')->where('name','batch_enabled')->first();
                $batch_exp = explode(',',$batch->value);
                // $user[0]->kolektif = false;
                if(in_array($user[0]->db, $batch_exp)){
                    $user[0]->batch_dev = true;
                }else{
                    $user[0]->batch_dev = false;
                }

                $kolektif = DB::table('settings')->where('name','kolektif_enabled')->first();
                $kolektif_exp = explode(',',$kolektif->value);
                // $user[0]->kolektif = false;
                if(in_array($user[0]->db, $kolektif_exp)){
                    $user[0]->kolektif = true;
                }else{
                    $user[0]->kolektif = false;
                }

                if(!$pass){
                    $user = [];
                }
            }
        }else{
            return Response::JSON([]);
        }
    }else{
        return Response::JSON([]);
    }
    
    return Response::JSON($user);

});

Route::any('/create-pass', function(Request $request){
    // $data = $request->json()->all();
    // print_r($data);
    $password = \Hash::make($request->pass);
    
    
    return Response::JSON([$password]);

});

Route::any('/data/dashboard/all', function(Request $request){

    $provinsi = DB::table('data_provinsi')->get();
    foreach($provinsi as $p){
        $user = DB::table('users')->select('name')->where('role','!=','0')->where('db',$p->db)->groupBy('name')->get();
        $totalAll = 0;
        $totalReal = 0;
        $totalNotReal = 0;
        $totalPercent = 0;

        foreach ($user as $key => $value) {
            try{
                $tmpAll = DB::table($value->name)->select(DB::RAW("count(*)as total"))->first();
                $tmpReal = DB::table($value->name)->select(DB::RAW("count(*)as total"))->where('tgl_serah','!=','')->first();
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
    
    return Response::JSON($response);

});


Route::any('/data/dashboard/wilayah', function(Request $request){
    // $data = $request->json()->all();
    // print_r($data);
    $user = DB::table('users')->select('name','email')->where('role','!=','0')->groupBy('name')->get();
    $totalAll = 0;
    $totalReal = 0;
    $totalNotReal = 0;
    $totalPercent = 0;
    // die();
    // echo "string";
    $arr = [];
    foreach ($user as $key => $value) {
        try{
            $tmpAll = DB::table($value->name)->select(DB::RAW("count(*)as total"))->first();
            $tmpReal = DB::table($value->name)->select(DB::RAW("count(*)as total"))->where('tgl_serah','!=','')->first();
            $totalAll = $tmpAll->total;
            $totalReal = $tmpReal->total;
            $totalNotReal = $totalAll-$totalReal;
            $totalPercent = ($totalReal/$totalAll)*100;
            $totalAll = number_format($totalAll,0,",",".");
            $totalReal = number_format($totalReal,0,",",".");
            $totalNotReal = number_format($totalNotReal,0,",",".");
            $totalPercent = number_format($totalPercent,2,",",".");
            $tmpArr = ["name"=>$value->name, "email"=>$value->email, "totalAll"=>$totalAll, "totalReal"=> $totalReal, "totalNotReal"=>$totalNotReal, "totalPercent"=> $totalPercent];
            array_push($arr, $tmpArr);
        }catch(\Illuminate\Database\QueryException $ex){ 
          // dd($ex->getMessage()); 
          // Note any method of class PDOException can be called on $ex.
            continue;
        }
        
    }

    $chunk = array_chunk($arr, ceil(count($arr) / 2));
    
    
    return Response::JSON($chunk);

});

// function cmp($a, $b) {

//    return $a["value"] - $b["value"];
// }

// function build_sorter2($key) {
//     return function ($a, $b) use ($key) {
//         return strnatcmp($a[$key], $b[$key]);
//     };
// }

Route::get('/settings/enabled/{param}', function($param){
    DB::table('settings')->where('name','enable_generate_bast')->update(['value'=>$param]);
    DB::table('settings')->where('name','enable_generate_undangan')->update(['value'=>$param]);
    return Response::JSON(["status"=>"true"]);
});

Route::any('/data/dashboard/wilayah/filter', function(Request $request){
    $data = $request->all();
    $arr = $data['dataAll'];
    $tmp = [];
    $filter = $data['filter'];
    foreach ($arr as $key => $value) {
        // echo $filter;
        $val =  $arr[$key][$filter];

        array_push($tmp, ["key"=>$key, "value"=>$val]);
    }

    $tmp2 = [];
    usort($tmp, build_sorter2("value"));

    foreach ($tmp as $item) {
        // echo $item['key'] . ', ' . $item['value'] . "\n";
        array_push($tmp2, $arr[$item['key']]);
    }
    if($data['order_by']=='desc'){
        $tmp2 = array_reverse($tmp2);
    }
    // print_r($arr);
    $chunk = array_chunk($tmp2, ceil(count($tmp2) / 2));
    
    
    return Response::JSON($chunk);

});

Route::any('/kabupaten/list', function (Request $request) {
    $data = $request->json()->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $data = DB::connection($user->db)->table($user->name)->select("kabupaten")->groupBy('kabupaten')->get();
    return Response::JSON($data);
});

Route::any('/kecamatan/list', function (Request $request) {
    $data = $request->json()->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $data = DB::connection($user->db)->table($user->name)->select("kecamatan")->where("kabupaten",$data['kab'])->groupBy('kecamatan')->get();
    return Response::JSON($data);
});

Route::any('/kelurahan/list', function (Request $request) {
    $data = $request->json()->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $data = DB::connection($user->db)->table($user->name)->select("kelurahan")->where("kecamatan",$data['kec'])->groupBy('kelurahan')->get();
    return Response::JSON($data);
});

Route::any('/data/list', function (Request $request) {
    $data = $request->json()->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $data = DB::connection($user->db)->table($user->name)->select(DB::RAW('count(*)as total'))
    ->where("kabupaten",$data['kab'])
    ->where("kecamatan",$data['kec'])
    ->where("kelurahan",$data['kel']);
    // $data_belum = DB::table($user->name)->where("tgl_serah","")->get();
    $total = $data->first();
    $belum_foto = $data->where('tgl_serah','')->first();
    $sudah_foto = $total->total-$belum_foto->total;

    

    $resp = ["total"=>$total->total, "sudah_foto" => $sudah_foto, "belum_foto" => $belum_foto->total, "data_belum"=>[], "data_total"=>[]];

    return Response::JSON([$resp]);
});

Route::any('/offline/data/list', function (Request $request) {
    $data = $request->json()->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $data = DB::connection($user->db)->table($user->name)->select('*')
    ->where("kabupaten",$data['kab'])
    ->where("kecamatan",$data['kec'])
    ->where("kelurahan",$data['kel']);
    // $data_belum = DB::table($user->name)->where("tgl_serah","")->get();
    $total = $data->get();
    $belum_foto = $data->where('tgl_serah','')->get();
    $sudah_foto = $total->count()-$belum_foto->count();

    

    $resp = ["total"=>$total->count(), "sudah_foto" => $sudah_foto, "belum_foto" => $belum_foto->count(), "data_belum"=>$belum_foto, "data_total"=>$total];

    return Response::JSON([$resp]);
});

Route::any('/data/nomor', function (Request $request) {
    $data = $request->json()->all();
    if($data['nomor']!=""){
        $user = DB::table('users')->where('id', $data['user_id'])->first();


        $resp = DB::connection($user->db)->table($user->name)->select("*")
        ->where("kabupaten",$data['kab'])
        ->where("kecamatan",$data['kec'])
        ->where("kelurahan",$data['kel']);

        // $tmp = $resp->where("no_urut",$data['nomor'])->get();
        $tmp = $resp->where("prefik",$data['nomor'])->get();


        if($tmp->count()==0){
            $resp = DB::table($user->name)->select("*")
            ->where("kabupaten",$data['kab'])
            ->where("kecamatan",$data['kec'])
            ->where("kelurahan",$data['kel']);
            $tmp = $resp->where("no_urut",$data['nomor'])->get();
            if($tmp->count()==0){
                $resp = DB::table($user->name)->select("*")
            ->where("kabupaten",$data['kab']);
                $tmp = $resp->where("nama","like","%".$data['nomor']."%")->get();
                $resp = $tmp;
            }else{
               
                $resp = $tmp;
            }
        }else{
            $resp = $tmp;
        }


    }else{
        $resp = [];
    }
    

    return Response::JSON($resp);
});

Route::middleware('cors')->any('/data/update', function (Request $request) {
    $res = $request->json()->all();
    // print_r($data);
    // return Response::JSON($data['prefik']);
    $user = DB::table('users')->where('id', $res['user_id'])->first();
    $date = date('Y-m-d H:i:s');
    $resdata = $res['data'];
    if($user->db=='mysql'){
        return Response::JSON(["status"=>false, "tgl_serah"=>""]);
    }
    foreach($resdata as $k => $data){
        // $img = str_replace("data:image/jpeg;base64,", "", $data['img_pbp']);
        $ifp = fopen( public_path().'/uploads/pbp/pbp_'.$data['prefik'].'.jpg', 'wb' ); 
        if($data['img_pbp']!=''){
            $img = explode( ',', $data['img_pbp'] );
            // we could add validation here with ensuring count( $data ) > 1
            $write = fwrite( $ifp, base64_decode( $img[ 1 ] ) );

            // clean up the file resource
            fclose( $ifp ); 

            if($write!==false){
                $pbp_uploaded = 1;
            }else{
                $pbp_uploaded = 0;
            }
        }else{
            $pbp_uploaded = 0;
        }

        $resp = DB::connection($user->db)->table($user->name)->where("id", $data['id'])->update(
            ["tgl_serah"=>$date, "transactor"=>$res['user_id'], "path_ktp"=>$data['path_ktp'], "path_pbp"=>$data['path_pbp'], "status_penerima"=>$data['status_penerima'], "pbp_uploaded"=>$pbp_uploaded]);
    }
    

    return Response::JSON(["status"=>$resp, "tgl_serah"=>$date]);
});


Route::middleware('throttle:1000,1')->any('/data/update/new', function (Request $request) {
    $res = $request->json()->all();
    $version = '5.1';
    // print_r($data);
    // return Response::JSON($data['prefik']);
    $user = DB::table('users')->where('id', $res['user_id'])->first();
    $date = date('Y-m-d H:i:s');
    $data = $res['data'];

    if(!isset($res['version']) && $res['version']!=$version){
        return Response::JSON(["status"=>false, "tgl_serah"=>'']);
    }
    
        // $img = str_replace("data:image/jpeg;base64,", "", $data['img_pbp']);
        if($user->db == 'mysql'){
            $folder = 'jatim_op_db';
        }else{
            $folder = $user->db;
        }

        $enabled = DB::table('settings')->where('name','upload_enabled')->where('value','like','%'.$folder.'%')->get()->count();

        // print_r($folder);
        if($enabled>0){
            $endpoint = "https://ptyaons-apps.com/drive/driveSync.php";
        }else{
            return Response::JSON(["status"=>false, "tgl_serah"=>'']);
        }

        $filename = 'pbp_'.$data['prefik'].'.jpg';
        $filepath = public_path().'/uploads/'.$folder.'/pbp/'.$filename;

        $ifp = fopen( $filepath, 'wb' ); 
        if(isset($data['img_pbp']) && $data['img_pbp']!=''){
            $img = explode( ',', $data['img_pbp'] );
            // we could add validation here with ensuring count( $data ) > 1
            $write = fwrite( $ifp, base64_decode( $img[ 1 ] ) );

            // clean up the file resource
            fclose( $ifp ); 

            if($write!==false){
                $pbp_uploaded = 1;

                // $client = new \GuzzleHttp\Client();
                
                // // $db = ($user->db=='mysql')?'jatim_op_db':$user->db;
                // $response = $client->request('GET', $endpoint, ['form_params' => [
                //     'filepath' => $ifp, 
                //     'db' => $folder
                // ]]);


                // $statusCode = $response->getStatusCode();
                // // $content = $response->getBody();
                // $content = json_decode($response->getBody(), true);
                // if($folder!='dev_papua_db'){
                    return Redirect::to($endpoint."?filepath=".$filepath."&filename=".$filename."&db=".$folder."&table=".$user->name."&user_id=".$res['user_id']."&status_penerima=".$data['status_penerima']."&id=".$data['id']."&kabupaten=".$data['kabupaten']."&kecamatan=".$data['kecamatan']."&kelurahan=".$data['kelurahan']."&tgl_serah=".$data['tgl_serah']);
                // }
                

            }else{
                $pbp_uploaded = 0;
            }
        }else{
            $pbp_uploaded = 0;
        }

        // if($pbp_uploaded==1){
        //     $data['path_pbp'] = $content['file_url'];
        // }

        if($data['status_penerima']=='4'){
            $resp = DB::connection($user->db)->table($user->name)
            ->where("kabupaten", $data['kabupaten'])
            ->where("kecamatan", $data['kecamatan'])
            ->where("kelurahan", $data['kelurahan'])
            ->update(
            ["tgl_serah"=>$date, "transactor"=>$res['user_id'], "path_ktp"=>$data['path_ktp'], "path_pbp"=>$data['path_pbp'], "status_penerima"=>$data['status_penerima'], "pbp_uploaded"=>$pbp_uploaded]);
        }else{

            $resp = DB::connection($user->db)->table($user->name)->where("id", $data['id'])->update(
            ["tgl_serah"=>$date, "transactor"=>$res['user_id'], "path_ktp"=>$data['path_ktp'], "path_pbp"=>$data['path_pbp'], "status_penerima"=>$data['status_penerima'], "pbp_uploaded"=>$pbp_uploaded]);
        }

        
    
    

    return Response::JSON(["status"=>$resp, "tgl_serah"=>$date]);
});

Route::middleware('throttle:1000,1')->any('/data/update/new-tahap', function (Request $request) {
    $res = $request->json()->all();
    $version = '6';
    // print_r($data);
    // return Response::JSON($data['prefik']);
    $user = DB::table('users')->where('id', $res['user_id'])->first();
    $date = date('Y-m-d H:i:s');
    $data = $res['data'];

    if(!isset($res['version']) && $res['version']!=$version){
        return Response::JSON(["status"=>false, "tgl_serah"=>'']);
    }
    
        // $img = str_replace("data:image/jpeg;base64,", "", $data['img_pbp']);
        if($user->db == 'mysql'){
            $folder = 'jatim_op_db';
        }else{
            $folder = $user->db;
        }

        $enabled = DB::table('settings')->where('name','upload_enabled')->where('value','like','%'.$folder.'%')->get()->count();

        // print_r($folder);
        if($enabled>0){
            $endpoint = "https://ptyaons-apps.com/drive/driveSync.php";
        }else{
            return Response::JSON(["status"=>false, "tgl_serah"=>'']);
        }

        $filename = 'pbp_'.$data['prefik'].'.jpg';
        $filepath = public_path().'/uploads/'.$folder.'/pbp/'.$filename;

        $ifp = fopen( $filepath, 'wb' ); 
        if(isset($data['img_pbp']) && $data['img_pbp']!=''){
            $img = explode( ',', $data['img_pbp'] );
            // we could add validation here with ensuring count( $data ) > 1
            $write = fwrite( $ifp, base64_decode( $img[ 1 ] ) );

            // clean up the file resource
            fclose( $ifp ); 

            if($write!==false){
                $pbp_uploaded = 1;

                // $client = new \GuzzleHttp\Client();
                
                // // $db = ($user->db=='mysql')?'jatim_op_db':$user->db;
                // $response = $client->request('GET', $endpoint, ['form_params' => [
                //     'filepath' => $ifp, 
                //     'db' => $folder
                // ]]);


                // $statusCode = $response->getStatusCode();
                // // $content = $response->getBody();
                // $content = json_decode($response->getBody(), true);
                if($folder!='dev_papua_db'){
                    return Redirect::to($endpoint."?filepath=".$filepath."&filename=".$filename."&db=".$folder."&table=".$user->name."&user_id=".$res['user_id']."&status_penerima=".$data['status_penerima']."&id=".$data['id']."&kabupaten=".$data['kabupaten']."&kecamatan=".$data['kecamatan']."&kelurahan=".$data['kelurahan']."&tgl_serah=".$data['tgl_serah']."&tahap=".$data['tahap']);
                }
                

            }else{
                $pbp_uploaded = 0;
            }
        }else{
            $pbp_uploaded = 0;
        }

        // if($pbp_uploaded==1){
        //     $data['path_pbp'] = $content['file_url'];
        // }

        if($data['status_penerima']=='4'){
            $pb = DB::connection($user->db)->table($user->name)
            ->where("kabupaten", $data['kabupaten'])
            ->where("kecamatan", $data['kecamatan'])
            ->where("kelurahan", $data['kelurahan'])
            ->get();

            foreach($pb as $k => $v){
                $resp = DB::connection($user->db)->table($data['tahap'])->updateOrInsert(["prefik"=>$v->prefik],
                ["tgl_serah"=>$date, 
                "transactor"=>$res['user_id'], 
                "path_pbp"=>$data['path_pbp'], 
                "status_penerima"=>$data['status_penerima'], 
                "pbp_uploaded"=>$pbp_uploaded]);
            }

        }else{

            $pb = DB::connection($user->db)->table($user->name)->where("id", $data['id'])->get();

            if($pb->count()>0){
                $resp = DB::connection($user->db)->table($data['tahap'])->updateOrInsert(["prefik"=>$data['prefik']],
            ["tgl_serah"=>$date, "transactor"=>$res['user_id'], "path_pbp"=>$data['path_pbp'], "status_penerima"=>$data['status_penerima'], "pbp_uploaded"=>$pbp_uploaded]);
            }else{
                $resp = false;
            }


        }

        
    
    

    return Response::JSON(["status"=>$resp, "tgl_serah"=>$date]);
});



Route::any('/data/tgl_upload', function (Request $request) {
    die();
    $table = DB::table("users")->whereNotIn('id', ['34'])->get();
    foreach($table as $t => $tv){
        DB::statement("ALTER TABLE `".$tv->name."` ADD `tgl_upload` VARCHAR(25) NOT NULL DEFAULT '' AFTER `path_pbp`");
    }
    

});

Route::any('/data/urutkan', function (Request $request) {

    // die();

    $table = DB::table($request->table)->get();
   
   
   // print_r($table);

   foreach($table as $t => $tv){
        // $check = DB::table('INFORMATION_SCHEMA_COLUMNS')->select('')
        // DB::statement("ALTER TABLE `".$tv->name."` ADD `tgl_serah` VARCHAR(25) NOT NULL DEFAULT '' AFTER `prefik`, ADD `transactor` INT NULL DEFAULT NULL AFTER `tgl_serah`, ADD `no_urut` INT NOT NULL AFTER `id`");
        // DB::statement("ALTER TABLE `".$tv->name."` ADD `path_pbp` TEXT NULL DEFAULT NULL AFTER `transactor`, ADD `path_ktp` TEXT NULL DEFAULT NULL AFTER `path_pbp`");
    // echo $tv->db;
        $kec = DB::connection($tv->db)->table($tv->name)->select("kecamatan")->groupBy("kecamatan")->get();
        foreach($kec as $k => $v){
            $kel = DB::connection($tv->db)->table($tv->name)->select("kelurahan")->where("kecamatan", $v->kecamatan)->groupBy("kelurahan")->get();
            foreach($kel as $k1 => $v1){
                $d = DB::connection($tv->db)->table($tv->name)->select("*")
                ->where("kecamatan", $v->kecamatan)
                ->where("kelurahan", $v1->kelurahan)
                ->orderBy("nama","asc")->get();
               $counter = 1;
               foreach($d as $k2 => $v2){
                    DB::connection($tv->db)->table($tv->name)->where("id",$v2->id)->update(["no_urut"=>$counter]);
                    $counter++;
               }
            }

           
        }
   }
    // $table = "65100";
    


    return Response::JSON(["status"=>true]);
});

Route::any('/data/offline/wilayah', function (Request $request) {
    $data = $request->json()->all();
    // $data = $request->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $kab = DB::connection($user->db)->table($user->name)->select("kabupaten")->groupBy("kabupaten")->get();
    $resp = [];
    foreach($kab as $k => $v){
        $resp[] = ['name'=>$v->kabupaten,'data'=>[]];
        $kec = DB::connection($user->db)->table($user->name)->select("kecamatan")->where('kabupaten',$v->kabupaten)->groupBy("kecamatan")->get();
        foreach($kec as $k2 => $v2){
            $resp[$k]['data'][$k2] = ['name'=>$v2->kecamatan,'data'=>[]];
            $kel = DB::connection($user->db)->table($user->name)->select("kelurahan")->where('kecamatan',$v2->kecamatan)->groupBy("kelurahan")->get();
            foreach($kel as $k3 => $v3){
                $resp[$k]['data'][$k2]['data'][$k3] = ['name'=>$v3->kelurahan];
                
            }
        }
    }

    return Response::JSON($resp);
});


Route::any('/data/offline/list', function (Request $request) {
    // $data = $request->json()->all();
    $data = $request->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $resp = DB::connection($user->db)->table($user->name)->select("*")->get();

    return Response::JSON($resp);
});

Route::any('/data/offline/upload', function (Request $request) {
    $data = $request->json()->all();
    // $data = $request->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();
    $total = 0;

    $pbp_uploaded = 0;
    foreach($data['data'] as $k => $v){
        $resp = DB::connection($user->db)->table($user->name)->where('id',$v['id'])->update(['tgl_serah'=>$v['tgl_serah'], 'path_pbp'=>$v['path_pbp'], 'transactor'=>$data['user_id'], 'tgl_upload'=>date('Y-m-d H:i:s'), 'status_penerima'=>$v['status_penerima'], 'pbp_uploaded'=>$pbp_uploaded]);
        if($resp){
            $total++;
            unset($data['data'][$k]);
        }
    }
    

    return Response::JSON(["status"=>true, "total"=>$total, "data"=>$data['data']]);
});

Route::any('/data/sync/list', function (Request $request) {
    $data = $request->json()->all();
    // $data = $request->all();
    $user = DB::table('users')->where('id', $data['user_id'])->first();

    $prefik = [];
    $status_penerima = [];
    $temp = [];
    foreach($data['files'] as $k => $v){
        $exp = explode("_", $v['filename']);
        $prefik[] = $exp[1];
        $temp[$exp[1]] = $k;
        // if(sizeof($exp)==3){
        //     $status_penerima[$exp[1]] = str_replace(".jpg", "", $exp[2]);
        // }
        
    }

    // print_r($status_penerima);

    $resp = DB::connection($user->db)->table($user->name)->select("*")
    ->where('kabupaten', $data['kab'])
    ->where('kecamatan', $data['kec'])
    ->where('kelurahan', $data['kel'])
    ->where('tgl_serah', '')
    ->whereIn('prefik', $prefik)
    ->get();

    foreach($resp as $k => $v){
        $resp[$k]->tgl_serah = $data['files'][$temp[$v->prefik]]['date'];
        $resp[$k]->date_folder = $data['files'][$temp[$v->prefik]];
        // if(sizeof($status_penerima)>0){
        //     $resp[$k]->status_penerima = $status_penerima[$v->prefik];
        // }
        
        $resp[$k]->path_pbp = "/POSBAST/".$data['files'][$temp[$v->prefik]]['date']."/PBP/".$data['files'][$temp[$v->prefik]]['filename'];
    }

    return Response::JSON($resp);
});


Route::any('/column/add/prefix/new', [HelperController::class,'generatePrefixNew']);
Route::any('/column/add/prefix', [HelperController::class,'generatePrefix']);
Route::any('/column/alter/prefix', [HelperController::class,'alterPrefix']);
Route::any('/column/add/username', [HelperController::class,'generateUsername']);
Route::any('/column/alter/all', [HelperController::class,'alterTable']);
Route::any('/column/truncate', [HelperController::class,'truncateFields']);

Route::get('/settings/tahap', [HelperController::class,'settingsTahap']);


Route::any('/bulog/insert', function (Request $request) {
    $endpoint = "https://bpb-sandbox.bulog.co.id/api/transporter/insert/";
    //LIVE
    // $endpoint = "https://bpb.bulog.co.id";
    //DEV
    $transporter_key = 'YAT_KEY_gshuy';
    //PROD
    // $transporter_key = 'YAT_KEY_zvqXIcAOhy';

    $client = new \GuzzleHttp\Client();

    $response = $client->request('POST', $endpoint, ['form_params' => [
        'tranporter_key' => $transporter_key, 
        'no_out' => '',
        'transportec_doc' => 'DOC-1234',
        'tanggal'=>'2023-10-15',
        'tanggal_alokasi'=>'2023-10-15',
        'transporter_bast'=>'BAST-123',
        'titik_penyerahan'=>'Kantor Lurah',
        'kelurahan'=>'Desa ABC',
        'kecamatan'=>'Taman',
        'kabupaten'=>'Sidoarjo',
        'provinsi'=>'Jawa Timur',
        'kuantum'=>'1000',
        'jumlah_pbp'=>'80',
        'jumlah_sptjm'=>'20',
        'kecamatan_id'=>'12623',
    ]]);

    // url will be: http://my.domain.com/test.php?key1=5&key2=ABC;

    $statusCode = $response->getStatusCode();
    $content = $response->getBody();

    // or when your server returns json
    // $content = json_decode($response->getBody(), true);
    print_r($statusCode);
    print_r($content);
    // return $content;
});



