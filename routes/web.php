<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('home', [UserController::class,'home'])->name('home');
Route::get('home/realisasi', [UserController::class,'homeRealisasi'])->name('home-realisasi');
Route::get('home/realisasi/tahap', [UserController::class,'homeRealisasiTahap'])->name('home-realisasi-tahap');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/bast', function () {
    return view('layout.bast');
});

Route::get('/undangan', function () {
    return view('layout.undangan');
});

Route::post('/login', [LoginController::class,'authenticateUser'])->name('login');
Route::any('/logout', [LoginController::class,'logout'])->name('logout');
Route::get('/reg', [LoginControlle::class, 'registerUser'])->name('reg');

Route::get('/new/user-login',  function(){
	if (Auth::check()) {
		// echo "string";
		return redirect('/home');
	}


	return view('auth.login');
		
})->name('login');

Route::prefix('new')->group(function(){
		Route::get('/bast', [UserController::class,'bastForm'])->name('bast');
		Route::get('/undangan', [UserController::class,'undanganForm'])->name('undangan');

		Route::get('/generate/bast', [UserController::class,'generateBast'])->name('generate-bast');
		Route::get('/generate/undangan', [UserController::class,'generateUndangan'])->name('generate-undangan');


		
});

Route::get('/kabupaten/list', function (Request $request) {
	if(Auth::user()->role!=0){
		$user = DB::table('users')->where('id', Auth::user()->id)->first();
	}else{
		$user = DB::table('users')->where('name', $request->table)->first();
	}
	
    $data = DB::connection($user->db)->table($user->name)->select("kabupaten")->groupBy('kabupaten')->get();
    return Response::JSON($data);
});

Route::get('/kecamatan/list', function (Request $request) {
	if(Auth::user()->role!=0){
		$user = DB::table('users')->where('id', Auth::user()->id)->first();
	}else{
		$user = DB::table('users')->where('name', $request->table)->first();
	}
    $data = DB::connection($user->db)->table($user->name)->select("kecamatan")->where("kabupaten",$request->kab)->groupBy('kecamatan')->get();
    return Response::JSON($data);
});

Route::get('/kelurahan/list', function (Request $request) {
	if(Auth::user()->role!=0){
		$user = DB::table('users')->where('id', Auth::user()->id)->first();
	}else{
		$user = DB::table('users')->where('name', $request->table)->first();
	}
    $data = DB::connection($user->db)->table($user->name)->select("kelurahan")->where("kecamatan",$request->kec)->groupBy('kelurahan')->get();
    return Response::JSON($data);
});


Route::get('realisasi/kabupaten/list', [UserController::class,'realOptKabupaten']);
Route::get('realisasi/kecamatan/list', [UserController::class,'realOptKecamatan']);
Route::get('realisasi/kelurahan/list', [UserController::class,'realOptKelurahan']);
Route::get('realisasi/table/all', [UserController::class,'realTableAll']);
Route::get('realisasi/table/total', [UserController::class,'realTableTotal']);

Route::get('realisasi/tahap/table/all', [UserController::class,'realTahapTableAll']);
Route::get('realisasi/tahap/table/total', [UserController::class,'realTahapTableTotal']);

Route::get('realisasi/tahap/table/all/kab', [UserController::class,'realTahapTableAllKab']);
Route::get('realisasi/tahap/table/total/kab', [UserController::class,'realTahapTableTotalKab']);

Route::get('realisasi/tahap/table/kab/list', [UserController::class,'realTahapTableKabList']);

Route::get('entry/distribution', [UserController::class,'entryDistribution']);
Route::get('entry/distribution/table', [UserController::class,'entryDistributionTable']);
Route::post('entry/distribution/list', [UserController::class,'entryDistributionList']);
Route::post('entry/distribution/upload', [UserController::class,'entryDistributionUpload']);

// Route::get('bulog/entry', [UserController::class,'BulogEntry']);
Route::get('bulog/list', [UserController::class,'BulogList']);
Route::post('bulog/list/data', [UserController::class,'BulogListData']);
Route::get('bulog/data/kec', [UserController::class,'bulogDataKec']);
Route::get('bulog/data/riwayat', [UserController::class,'bulogDataRiwayat']);
Route::post('bulog/form/simpan', [UserController::class,'bulogFormSimpan']);



// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
