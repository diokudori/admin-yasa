@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Entry Penyaluran BULOG</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-6 col-sm-12">
          	@if (\Session::has('success'))
    <div class="alert alert-success">
        <h4>{!! \Session::get('success') !!}</h4>
    </div>
@endif
@if($errors->any())
<div class="alert alert-danger">
	<h4>{{$errors->first()}}</h4>

</div>
@endif
            <!-- Horizontal Form -->
            <div class="card card-info">
              <!-- /.card-header -->
              <!-- form start -->
              <input type="hidden" name="admin" value="{{Auth::user()->role}}">
              @if ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
    <button type="button" class="close" data-dismiss="alert">×</button>    
    <strong>{{ $message }}</strong>
</div>
@endif
              <form id="bulog-form" action="{{url('bulog/form/simpan')}}" method="POST">
                @csrf
                <input type="hidden" name="transporter_key" id="transporter_key" value="{{$transporter_key}}">
                <input type="hidden" name="db" id="db" value="{{$db}}">
                <input type="hidden" name="tanggal" value="{{date('Y-m-d')}}">
                <input type="hidden" name="provinsi" value="{{$provinsi}}">
                <input type="hidden" name="kecamatan_id" value="">
                <div class="card-body">
                	<div class="form-group">
                    <label for="item_code">Tahap Penyaluran</label>
                    <select name="tahap" id="tahap" class="form-control select2">
                    	@foreach($tahap as $t)
                    	<option value="{{$t->value}}">{{$t->name}}</option>
                    	@endforeach
                      </select>
                  </div>
                
                <div class="form-group">
                	
                    <label for="item_code">Wilayah</label>
                    <select name="wilayah" id="wilayah" class="form-control select2">
                    	@foreach($wilayah as $w)
                    	<option value="{{$w->name}}" {{($wil==$w->name)?'selected':''}}>{{$w->name}}</option>
                    	@endforeach
                      </select>
                  </div>
                  <div class="form-group">
                    <label for="item_code">Kabupaten</label>
                    <select name="kabupaten" id="kabupaten" class="form-control select2">
                      </select>
                  </div>
                  <div class="form-group">
                    <label for="item_name">Kecamatan</label>
                    	<select name="kecamatan" id="kecamatan" class="form-control select2">
                      </select>
                  </div>
                  <div class="form-group ">
                    <label for="item_description">Kelurahan/Desa</label>
                    <select name="kelurahan" id="kelurahan" class="form-control select2">
                      </select>
                  </div>
                  <div class="form-group ">
                    <label for="item_description">Nomor DO</label>
                    <input name="no_out" id="no_out" class="form-control" />

                  </div>
                  <div class="form-group ">
                    <label for="item_description">Nomor Surat Jalan</label>
                    <input name="surat_jalan" id="surat_jalan" class="form-control" />
                  </div>
                  <div class="form-group">
                <label>Tanggal Alokasi</label>
                <div class="col-sm-10 input-group date" id="expdate" data-target-input="nearest">
                        <input type="text" name="tanggal_alokasi" class="form-control datetimepicker-input" data-target="#expdate" readonly="true" required />
                        <div class="input-group-append" data-target="#expdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
              </div>
              <div class="form-group">
                    <label for="item_description">Titik Penyerahan</label>
                    <input name="titik_penyerahan" id="titik_penyerahan" class="form-control" required />
                  </div>
                <div class="form-group">
                    <label for="item_description">Kuantum</label>
                    <input name="kuantum" id="kuantum" class="form-control" />
                  </div>
                  <div class="form-group ">
                    <label for="item_description">Jumlah PBP</label>
                    <input name="jumlah_pbp" id="jumlah_pbp" class="form-control" required />
                  </div>
                  <div class="form-group ">
                    <label for="item_description">Jumlah SPTJM</label>
                    <input name="jumlah_sptjm" id="jumlah_sptjm" class="form-control" required />
                  </div>
                  <div id="sudah_hit" class="alert alert-success" style="display: none;">Sudah Hit Ke Bulog</div>



                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <!-- <button type="submit" class="btn btn-default">Cancel</button> -->
                  <button type="submit" class="btn btn-info float-right btn-submit">Submit</button>
                  
                </div>
                <!-- /.card-footer -->
              </form>
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->
      
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
@stop

@section('css')
     <link rel="stylesheet" href="{{asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
      <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  	<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" type="text/css" />

@stop

@section('js')

   <!-- InputMask -->
<script src="{{asset('plugins/moment/moment.min.js')}}"></script>
   <script src="{{asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
   <script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
   <script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/daterangepicker/daterangepicker.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

   <script type="text/javascript">
   	$(document).ready(function(){
   		  $('#expdate').datetimepicker({
        format: 'YYYY-MM-DD'
    });
   	var wil = $("#wilayah");
   		var admin = $('input[name=admin').val();
   		
   
   	var db = $("#db");
   	var kab = $("#kabupaten");
   	var kec = $("#kecamatan");
   	var kel = $("#kelurahan");

   	
wil.trigger('change');
if(admin!=0){
   			wil.removeClass('select2');
 			wil.attr("readonly","true");
 			$.ajax({
	    type: 'GET',
	    url: '/kabupaten/list',
	    data: { table: wil.val() }
	}).then(function (data) {
		console.log(data);
	    // create the option and append to Select2
	    for(var i in data){
	    	var option = new Option(data[i].kabupaten, data[i].kabupaten, true, true);
	    	kab.append(option);
	    }
	    
	    kab.trigger('change');

	    // manually trigger the `select2:select` event
	    kab.trigger({
	        type: 'select2:select',
	        params: {
	            data: data
	        }
	    });
	});
   		}
$(".select2").select2();

wil.on("change", function(){
   	$.ajax({
	    type: 'GET',
	    url: '/pbp-app/public/index.php/kabupaten/list',
	    data: { table: wil.val() }
	}).then(function (data) {
		console.log(data);
	    // create the option and append to Select2
	    kab.html('');
	    for(var i in data){
	    	var option = new Option(data[i].kabupaten, data[i].kabupaten, true, true);
	    	kab.append(option);
	    }
	    
	    kab.trigger('change');

	    // manually trigger the `select2:select` event
	    kab.trigger({
	        type: 'select2:select',
	        params: {
	            data: data
	        }
	    });
	});
	});


kab.on("change", function(){
   	$.ajax({
	    type: 'GET',
	    url: '/pbp-app/public/index.php/kecamatan/list',
	    data: { kab: kab.val(), table: wil.val() }
	}).then(function (data) {
		console.log(data);
	    // create the option and append to Select2
	    kec.html('');
	    for(var i in data){
	    	var option = new Option(data[i].kecamatan, data[i].kecamatan, true, true);
	    	kec.append(option);
	    }
	    
	    kec.trigger('change');

	    // manually trigger the `select2:select` event
	    kec.trigger({
	        type: 'select2:select',
	        params: {
	            data: data
	        }
	    });
	});
	});

	kec.on("change", function(){

		$.ajax({
	    type: 'GET',
	    url: '/pbp-app/public/index.php/kelurahan/list',
	    data: {kec: kec.val(), table: wil.val()}
	}).then(function (data) {
		kel.html("");
		console.log(data);
	    // create the option and append to Select2
	    kel.html('');
	    for(var i in data){
	    	var option = new Option(data[i].kelurahan, data[i].kelurahan, true, true);
	    	kel.append(option);
	    }
	    
	    kel.trigger('change');

	    // manually trigger the `select2:select` event
	    kel.trigger({
	        type: 'select2:select',
	        params: {
	            data: data
	        }
	    });
	});
	});


	kel.on("change", function(){
		var tahap = $('#tahap');
		$.ajax({
          type: 'GET',
          url: "<?=url('realisasi/table/total')?>",
          data: { db: db.val(), kab: kab.val(), kec: kec.val(), kel: kel.val(), tahap: tahap.val()}
        }).then(function (data) {
          console.log(data);
         $('input[name=kuantum').val(data.kuantum);
          getBulogKec();

              
            
        });

	});

	kel.trigger('change');

	// $('input[name=jumlah_sptjm]').on('focusout',function(){
	// 	var pbp = $('input[name=jumlah_pbp').val();
	// 	var newpbp = pbp-$(this).val();
	// 	$('input[name=jumlah_pbp').val(newpbp);
	// });


//   $("#bulog-form").on("submit", function(event){
//       event.preventDefault();

//       $.ajax({
//           type: 'POST',
//           url: "<?=$url_bulog?>",
//           data: $( this ).serialize()
//         }).then(function (data) {
//           console.log("bulog", data);
//               if(data.status){
//                    toastr.success(data.message);
//               }else{
//                 toastr.warning(data.message);
//               }
            
//         });
// });

  function getBulogKec(){
      $.ajax({
          type: 'GET',
          url: "<?=url('bulog/data/kec')?>",
          data: { kec: kec.val()}
        }).then(function (data) {
          console.log(data);
         $('input[name=kecamatan_id]').val(data.kec_id);
           getBulogRiwayat(data.kec_id);
            
        });
  }

  function getBulogRiwayat(kec_id){
  	var tahap = $('select[name=tahap]').val();
  	var db = $('input[name=db]').val();

  		$.ajax({
          type: 'GET',
          url: "<?=url('bulog/data/riwayat')?>",
          data: { kelurahan: kel.val(), kecamatan_id: kec_id, db: db, tahap: tahap}
        }).then(function (data) {
          console.log(data);

          if(data.transporter_bast!=null){
          	$('input[name=transporter_bast]').val(data.transporter_bast);
	          $('input[name=transporter_doc]').val(data.transporter_doc);
	          $('input[name=tanggal_alokasi]').val(data.tanggal_alokasi);
	          $('input[name=titik_penyerahan]').val(data.titik_penyerahan);
	          $('input[name=kuantum]').val(data.kuantum);
	          $('input[name=jumlah_pbp]').val(data.jumlah_pbp+data.jumlah_sptjm);
	          $('input[name=jumlah_sptjm]').val(data.jumlah_sptjm);
	          if(data.status_hit=='1'){
	          	$('#sudah_hit').show();
		          $('.btn-submit').attr('disabled','');
		        }
          }else{
          	$('input[name=transporter_bast]').val('');
	          $('input[name=transporter_doc]').val('');
	          $('input[name=tanggal_alokasi]').val('');
	          $('input[name=titik_penyerahan]').val('');
	          // $('input[name=kuantum]').val('');
	          $('input[name=jumlah_pbp]').val('');
	          $('input[name=jumlah_sptjm]').val('');
	          $('#sudah_hit').hide();
	          
	          	$('.btn-submit').attr('disabled','').removeAttr('disabled');
	          
	          
          }

          
            
        });
  }
	

   });

   
   </script>
@stop