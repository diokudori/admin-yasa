@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<div class="row">
	<div class="col-md-10" style="display: flex;
    justify-content: flex-start;
    align-items: flex-start;">
		<img src="{{asset('assets/images/logo-yat.jpeg')}}" style="height: 75px;" >
		<h1><span style="font-size: 75%; color: #5f5d5d;">Online System</span><br>Dashboard</h1>
	</div>
	<div class="col-md-2 text-right">
		<p>
		<img src="{{asset('assets/images/logo-bulog.jpeg')}}" style="height: 75px;" ></p>
	</div>
</div>
    
    
    
@stop

@section('content')
    <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        
        <!-- Main row -->
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label>Tahap</label>
              <select name="tahap" id="tahap" class="form-control select2">
                <option value="">Pilih Tahap</option>       
                @foreach($tahap as $t)
                <option value="{{$t->value}}">{{strtoupper($t->name)}}</option>
                @endforeach
                <!-- <option value="mysql">Jawa Timur</option>        -->
                <!-- <option value="sulut_db">Sulawesi Utara</option>       
                <option value="gto_db">Gorontalo</option>       
                <option value="papua_db">Papua</option>       
                <option value="pegunungan_papua">Papua Pegunungan</option>       
                <option value="tengah_papua">Papua Tengah</option>       
                <option value="selatan_papua">Papua Selatan</option>       
                <option value="barat_papua">Papua Barat</option>       
                <option value="baratdaya_papua">Papua Baratdaya</option>      -->  
              </select>
            </div>
          </div>
        	<div class="col-md-3 prov-opt" >
            
        		<div class="form-group">
        			<label>Provinsi</label>
        			<select name="provinsi" id="provinsi" class="form-control select2">
                <option value="">Pilih Provinsi</option>       
                @foreach($provinsi as $p)
                <option value="{{$p->db}}">{{strtoupper($p->nama)}}</option>
                @endforeach
                <!-- <option value="mysql">Jawa Timur</option>        -->
                <!-- <option value="sulut_db">Sulawesi Utara</option>       
                <option value="gto_db">Gorontalo</option>       
                <option value="papua_db">Papua</option>       
                <option value="pegunungan_papua">Papua Pegunungan</option>       
                <option value="tengah_papua">Papua Tengah</option>       
                <option value="selatan_papua">Papua Selatan</option>       
                <option value="barat_papua">Papua Barat</option>       
                <option value="baratdaya_papua">Papua Baratdaya</option>      -->  
              </select>
        		</div>
        	</div>
          <div class="col-md-3 pbp-opt" >
            
            <div class="form-group">
              <label>Jenis Penerima</label>
              <select name="jenis_pbp" id="jenis_pbp" class="form-control select2">
                <option value="">Pilih Jenis Penerima</option>       
                <option value="utama">Utama</option>
                <option value="tambahan">Tambahan</option>
                <option value="all">Semua</option>
                <!-- <option value="mysql">Jawa Timur</option>        -->
                <!-- <option value="sulut_db">Sulawesi Utara</option>       
                <option value="gto_db">Gorontalo</option>       
                <option value="papua_db">Papua</option>       
                <option value="pegunungan_papua">Papua Pegunungan</option>       
                <option value="tengah_papua">Papua Tengah</option>       
                <option value="selatan_papua">Papua Selatan</option>       
                <option value="barat_papua">Papua Barat</option>       
                <option value="baratdaya_papua">Papua Baratdaya</option>      -->  
              </select>
            </div>
          </div>
        	<!-- <div class="col-md-3">
        		<div class="form-group">
        			<label for="item_code">Kota/Kabupaten</label>
                    <select name="kabupaten" id="kabupaten" class="form-control select2">
                      <option value="">Pilih Kota/Kabupaten</option>   
                      </select>
        		</div>
        	</div> -->
         <!--  <div class="col-md-3">
            <div class="form-group">
              <label for="item_name">Kecamatan</label>
                      <select name="kecamatan" id="kecamatan" class="form-control select2">
                        <option value="">Pilih Kecamatan</option>   
                      </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="item_description">Kelurahan/Desa</label>
                    <select name="kelurahan" id="kelurahan" class="form-control select2">
                      <option value="">Pilih Kelurahan/Desa</option>   
                      </select>
            </div>
          </div> -->
          <div class="col-md-3" style="display: none;">
            <div class="form-group">
              <button id="show-tables" class="btn btn-primary" disabled="">Tampilkan Data Realiasi</button>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-12">
          	<ol class="breadcrumb float-sm-left">
</ol>
          </div>
          <div class="col-md-8">
            <div class="card">
              <!-- /.card-header -->
               <div class="card-header">
                <h3 class="card-title">Detil Data</h3>
                <div class="form-group" id="form-option-real">
                  <select class="form-control" name="tgl_serah">

                    <option value="false">Belum Realisasi</option>
                    <option value="true">Sudah Realisasi</option>
                  </select>
                </div>
              </div>
              <div class="card-body p-0  overlay-wrapper">
                <table class="table table-striped table-real">
                  <thead>
                    <tr id="thead-counter">
                      <th style="width: 10px">No.</th>
                      <th>Nama</th>
                      <th>Rencana Salur</th>
                      <!-- <th>Diterima Transporter</th>
                      <th>% Trans</th> -->
                      <th>Diterima PBP</th>
                      <th>% PBP</th>
                      <th>Sisa Realisasi</th>
                      <th>% Sisa</th>
                    </tr>
                    <tr id="thead-person" style="display: none;">
                      <th style="width: 10px">No.</th>
                      <th>Nama</th>
                      <th>Alamat</th>
                      <th>Prefik</th>
                      <th class="real-col">Foto</th>
                      <th class="real-col">Status</th>
                    </tr>
                  </thead>
                  <tbody id="table-real">
                    
                    
                  </tbody>
                </table>
                <div class="overlay overlay-real"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-md-4">
          	<div class="card ">
              <!-- /.card-header -->
              <div class="card-header">
                <h3 class="card-title">Total</h3>
              </div>
              <div class="card-body p-0 overlay-wrapper">
                <table class="table table-striped ">
                  <thead>
                    <tr>
                      <th>Rencana Salur</th>
                      <!-- <th>Diterima Transporter</th>
                      <th>% Trans</th> -->
                      <th>Diterima PBP</th>
                      <th>% PBP</th>
                      <th>Sisa Realisasi</th>
                      <th>% Sisa</th>
                      
                    </tr>
                  </thead>
                  <tbody id="total-table-real">
                   
                  </tbody>
                </table>
                 <div class="overlay overlay-total"><i class="fas fa-3x fa-sync-alt fa-spin"></i><div class="text-bold pt-2">Loading...</div></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>

   
          
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
@stop

@section('css')
<link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
      <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
    <style type="text/css">
    	.badge{
    		font-size: 100% !important;
    		width: 100%;
    	}
    	.content-wrapper {
    		background-color: #fff;
    	}
      .select2-container--default .select2-selection--single .select2-selection__rendered{
        line-height: 16px !important;
      }
    </style>
@stop

@section('js')
 <script src="{{asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{asset('plugins/jszip/jszip.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/pdfmake.min.js')}}"></script>
<script src="{{asset('plugins/pdfmake/vfs_fonts.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{asset('plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
  <!-- InputMask -->
<script src="{{asset('plugins/moment/moment.min.js')}}"></script>
   <script src="{{asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
   <script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
   <script type="text/javascript">
    $(document).ready(function(){
      $('.prov-opt').hide();
      $('.pbp-opt').hide();
      $('.overlay').hide();
      $('.breadcrumb').hide();
      $('#form-option-real').hide();
   var table_real;
    var tahap = $("#tahap");
    var prov = $("#provinsi");
    var pbp = $("#jenis_pbp");
    var admin = $('input[name=admin]').val();
      
    // var kab = $("#kabupaten");
    // var kec = $("#kecamatan");
    // var kel = $("#kelurahan");

    var kab = '';
    var kec = '';
    var kel = '';
    var data_kab = [];

    var q_total = 0;
    var pbp_total = 0;
    var persen_pbp_total = 0;
    var sisa_total = 0;
    var persen_sisa_total = 0;
    var counter_kab = 0;

    var detil_nama = 'Kota/Kab';

    $('#detil_nama').html(detil_nama);
    
prov.trigger('change');

$(".select2").select2();

tahap.on("change", function(){
  if(tahap.val()==''){
      $('.prov-opt').hide();
     
  }else{
      $('.prov-opt').show();
  }
});
prov.on("change", function(){
if(prov.val()==''){
      $('.pbp-opt').hide();
     
  }else{
      $('.pbp-opt').show();
  }
    
  });

pbp.on("change", function(){
q_total = 0;
    pbp_total = 0;
    persen_pbp_total = 0;
    sisa_total = 0;
    persen_sisa_total = 0;
     counter_kab = 0;
$('.overlay').show();
  if(pbp.val()==''){
      $('#show-tables').attr('disabled','');
     
  }else{
   

    $.ajax({
          type: 'GET',
          url: "<?=url('realisasi/tahap/table/kab/list')?>",
          data: { db: prov.val(), kab: kab, tahap: tahap.val()}
        }).then(function (data) {
        
          console.log(data);
            var no = 0;
            counter_kab = no;
             $('#table-real').html('');
             data_kab = data;
            for(var i in data){
              
         
                    no += 1;
                    // getDataKabAll(data_kab[0], 1, data.length);
                    getDataKabAll(data_kab[i], no, data.length);
          
              
              
            }

            
        });


        // $.ajax({
        //   type: 'GET',
        //   url: "realisasi/tahap/table/total",
        //   data: { db: prov.val(), kab: kab, kec: kec, kel: kel, tahap: tahap.val(), pbp: pbp.val()}
        // }).then(function (data) {
        //   console.log(data);
        //   var no = 0;
        //       var str = '';
             
        //         no += 1;
        //         str += '<tr>';
        //         str += '<td><span class="badge bg-info">'+data.kuantum+'</span></td>';
        //         // str += '<td>'+data.transporter+'</td>';
        //         // str += '<td>'+data.persen_transporter+'%</td>';
        //         str += '<td><span class="badge bg-success ">'+data.pbp+'</span></td>';
        //         str += '<td>'+data.persen_pbp+'%</td>';
        //         str += '<td><span class="badge bg-danger ">'+data.sisa+'</span></td>';
        //         str += '<td>'+data.persen_sisa+'%</td>';
        //         str += '</tr>';
        //         $('#total-table-real').html(str);
        //         $('.overlay-total').hide();
        //                 //dataAll.push(data[i][j]);
              
              
            
        // });
  }
});

$('select[name=tgl_serah]').on("change", function(){
    refreshData('kel',null,'');
});
$('table.table-real').on('click', 'a.btn-name', function(){
	$('.overlay').show();
	var id = $(this).data('id');
	var val = $(this).data('val');
  var ip = $(this).data('ip');
	refreshData(id,val,ip);

});

$('ol.breadcrumb').on('click', 'a.btn-breadcrumb', function(){
	$('.overlay').show();
	var id = $(this).data('id');
	var val = $(this).data('val');
  var ip = $(this).data('ip');
  refreshData(id,val,ip);

});

function getDataKabAll(param, no, max){
  if(param.ip==null){
    getDataKabAll(data_kab[no], no+1, max);
  }else{
     param.db = prov.val();
  param.tahap = tahap.val();
  param.pbp = pbp.val();
   $.ajax({
          type: 'GET',
          url: "http://"+param.ip+"/pbp-app/public/index.php/api/realisasi/tahap/table/all/kab",
          // url: "realisasi/tahap/table/all/kab",
          data: param
        }).then(function (data) {
          counter_kab += 1;
          // if(kab.val()!=''){
          //   table_real.destroy();
          // }
          $('.breadcrumb').html('');
          $('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="db" data-val="'+prov.val()+'" data-ip="'+param.ip+'">'+$('#provinsi option:selected').text()+'</a></li>');
          $('.breadcrumb').show();
          console.log(data);
         
          var str = '';
          var id = '';
          if(kab==''){
            id = 'kab';
          }else{
            if(kec==''){
              id = 'kec';
            }else{
              if(kel==''){
                id = 'kel';
              }else{
                id = 'pb'; 
              }
            }
          }
            for(var i in data){
              
         
                    str += '<tr>';
                    str += '<td>'+counter_kab+'</td>';
                    str += '<td><a href="#" class="btn-name" data-val="'+data[i].nama+'" data-id="'+id+'" data-ip="'+param.ip+'">'+data[i].nama+'</a></td>';
                    str += '<td><span class="badge bg-info">'+data[i].kuantum+'</span></td>';
                    // str += '<td>'+data[i].transporter+'</td>';
                    // str += '<td>'+data[i].persen_transporter+'%</td>';
                    str += '<td><span class="badge bg-success ">'+data[i].pbp+'</span></td>';
                    str += '<td>'+data[i].persen_pbp+'%</td>';
                    str += '<td><span class="badge bg-danger ">'+data[i].sisa+'</span></td>';
                    str += '<td>'+data[i].persen_sisa+'%</td>';
                    str += '</tr>';
            
                q_total += parseInt(data[i].kuantum_r);
                pbp_total += parseInt(data[i].pbp_r);
                // persen_pbp_total += parseFloat(data[i].persen_pbp);
                sisa_total += parseInt(data[i].sisa_r);
                // persen_sisa_total += parseFloat(data[i].persen_sisa);
              
              
            }
            var str2 = '';
            persen_pbp_total = (pbp_total/q_total)*100;
            persen_sisa_total = (sisa_total/q_total)*100;
             str2 += '<tr>';
                str2 += '<td><span class="badge bg-info">'+q_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");+'</span></td>';
                str2 += '<td><span class="badge bg-success ">'+pbp_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");+'</span></td>';
                str2 += '<td>'+(Math.round(persen_pbp_total * 100) / 100).toFixed(2)+'%</td>';
                str2 += '<td><span class="badge bg-danger ">'+sisa_total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");+'</span></td>';
                str2 += '<td>'+(Math.round(persen_sisa_total * 100) / 100).toFixed(2)+'%</td>';
                str2 += '</tr>';
                $('#total-table-real').html(str2);

            $('#table-real').append(str);

            if(counter_kab==max){
              $('.overlay-real').hide();
              $('.overlay-total').hide();
            }else{
              // getDataKabAll(data_kab[no], no+1, max);
            }


            
        });
  }
 

}

function refreshData(id, val, ip){
	var db = prov.val();
	$('#thead-person').hide();
     	$('#thead-counter').show();
      $('#form-option-real').hide();
       $('.real-col').show();
   var tgl_serah = $('select[name=tgl_serah]').val();
   console.log(tgl_serah);
   $('.overlay').show();
   $('.overlay').show();
	if(id=='db'){
		db = val;
		kab = '';
		kec = '';
		kel = '';
	}
	else if(id=='kab'){
		kab = val;
		kec = '';
		kel = '';
	}else if(id=='kec'){
		kec = val;
		kel = '';
	}else if(id=='kel'){
    if(val!=null){
      kel = val;
    }
		
		$('#thead-counter').hide();
    $('#thead-person').show();
    $('#form-option-real').show();
    // if(tgl_serah=='true'){
    //           $('.real-col').show();
    //         }else{
    //            $('.real-col').hide();
    //         }
	}


	$('.breadcrumb').html('');
          $('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="db" data-val="'+prov.val()+'">'+$('#provinsi option:selected').text()+'</a></li>');
          if(id=='kab'){
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kab" data-val="'+kab+'">'+kab+'</a></li>');
          }else if(id=='kec'){
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kab" data-val="'+kab+'">'+kab+'</a></li>');
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kec" data-val="'+kec+'">'+kec+'</a></li>');
          }else if(id=='kel'){
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kab" data-val="'+kab+'">'+kab+'</a></li>');
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kec" data-val="'+kec+'">'+kec+'</a></li>');
          	$('.breadcrumb').append('<li class="breadcrumb-item"><a href="#" class="btn-breadcrumb" data-id="kel" data-val="'+kel+'">'+kel+'</a></li>');
          }

         
	$.ajax({
          type: 'GET',
          // url: "http://"+ip+"/pbp-app/public/index.php/api/realisasi/tahap/table/all/kab",
          url: "<?=url('realisasi/tahap/table/all')?>",
          data: { db: db, kab: kab, kec: kec, kel: kel, tahap: tahap.val(), tgl_serah: tgl_serah, pbp: pbp.val()}
        }).then(function (data) {
          // if(kab.val()!=''){
          //   table_real.destroy();
          // }
          
          $('.breadcrumb').show();
          console.log(data);
          var no = 0;
          var str = '';
          var id = '';
          
          if(kab ==''){
          	id = 'kab';
          }else{
          	if(kec ==''){
          		id = 'kec';
          	}else{
          		if(kel ==''){
          			id = 'kel';
          		}else{
          			id = 'pb'; 
          		}
          	}
          }
             if(tgl_serah=='true'){
              $('.real-col').show();
            }else{
               $('.real-col').hide();
            }
            for(var i in data){
              
                if(kel==''){
                    no += 1;
                    str += '<tr>';
                    str += '<td>'+no+'</td>';
                    str += '<td><a href="#" class="btn-name" data-val="'+data[i].nama+'" data-id="'+id+'" data-ip="'+ip+'">'+data[i].nama+'</a></td>';
                    str += '<td><span class="badge bg-info">'+data[i].kuantum+'</span></td>';
                    // str += '<td>'+data[i].transporter+'</td>';
                    // str += '<td>'+data[i].persen_transporter+'%</td>';
                    str += '<td><span class="badge bg-success ">'+data[i].pbp+'</span></td>';
                    str += '<td>'+data[i].persen_pbp+'%</td>';
                    str += '<td><span class="badge bg-danger ">'+data[i].sisa+'</span></td>';
                    str += '<td>'+data[i].persen_sisa+'%</td>';
                    str += '</tr>';
                }else{
                    no += 1;
                    str += '<tr>';
                    str += '<td>'+no+'</td>';
                    str += '<td>'+data[i].nama+'</td>';
                    str += '<td>'+data[i].alamat+'</td>';
                    str += '<td>'+data[i].prefik+'</td>';
                    if(tgl_serah=='true'){
                      str += '<td><a href="'+data[i].path_pbp+'" target="_blank">Lihat foto</a></td>';
                      str += '<td><span class="badge '+data[i].status_penerima_class+'">'+data[i].status_penerima+'</span></td>';
                    }
                    
                    str += '</tr>';
                }
                
                
                        //dataAll.push(data[i][j]);
              
              
            }

           

            $('#table-real').html(str);
            $('.overlay-real').hide();
            // $('.table-real').addClass('datatables');
           // table_real = $('.table-real').DataTable();
        });


        $.ajax({
          type: 'GET',
          // url: "http://"+ip+"/pbp-app/public/index.php/api/realisasi/tahap/table/total/kab",
          url: "<?=url('realisasi/tahap/table/total')?>",
          data: { db: db, kab: kab, kec: kec, kel: kel, tahap: tahap.val(), tgl_serah: $('select[name=tgl_serah]').val(), pbp: pbp.val()}
        }).then(function (data) {
          console.log(data);
          var no = 0;
              var str = '';
             
                no += 1;
                str += '<tr>';
                str += '<td><span class="badge bg-info">'+data.kuantum+'</span></td>';
                // str += '<td>'+data.transporter+'</td>';
                // str += '<td>'+data.persen_transporter+'%</td>';
                str += '<td><span class="badge bg-success ">'+data.pbp+'</span></td>';
                str += '<td>'+data.persen_pbp+'%</td>';
                str += '<td><span class="badge bg-danger ">'+data.sisa+'</span></td>';
                str += '<td>'+data.persen_sisa+'%</td>';
                str += '</tr>';
                $('#total-table-real').html(str);
                $('.overlay-total').hide();
                        //dataAll.push(data[i][j]);
              
              
            
        });
}

// kab.on("change", function(){
//    if(kab.val()==''){
//     kec.html('');
//         var option = new Option("Pilih Kecamatan", "", true, true);
//           kec.append(option);
//       kec.val('');
//       kec.trigger('change');
//     }

//     $.ajax({
//       type: 'GET',
//       url: "<?php//url('realisasi/kecamatan/list')?>",
//       data: { db: prov.val(), table: kab.val() }
//   }).then(function (data) {
//     console.log(data);
//       // create the option and append to Select2
//       kec.html('');
//        var option = new Option("Pilih Kecamatan", "", true, true);
//         kec.append(option);
//       for(var i in data){
//         var option = new Option(data[i].kecamatan, data[i].kecamatan, true, true);
//         kec.append(option);
//       }
      
//       // kec.trigger('change');
//       kec.val('');
//       // manually trigger the `select2:select` event
//       kec.trigger({
//           type: 'select2:select',
//           params: {
//               data: data
//           }
//       });
//   });
//   });

  // kec.on("change", function(){

  //   if(kec.val()==''){
  //     kel.html('');
  //       var option = new Option("Pilih Kelurahan/Desa", "", true, true);
  //         kel.append(option);
  //     kel.val('');
  //     kel.trigger('change');
  //   }

  //   $.ajax({
  //     type: 'GET',
  //     url: "<?php //url('realisasi/kelurahan/list')?>",
  //     data: {db: prov.val(), kecamatan: kec.val(), table: kab.val()}
  // }).then(function (data) {
  //   kel.html("");
  //   console.log(data);
  //     // create the option and append to Select2
  //     kel.html('');
  //     var option = new Option("Pilih Kelurahan/Desa", "", true, true);
  //       kel.append(option);
  //     for(var i in data){
  //       var option = new Option(data[i].kelurahan, data[i].kelurahan, true, true);
  //       kel.append(option);
  //     }
      
  //     // kel.trigger('change');
  //     kel.val('');
  //     // manually trigger the `select2:select` event
  //     kel.trigger({
  //         type: 'select2:select',
  //         params: {
  //             data: data
  //         }
  //     });
  // });
  // });


  // kel.on("change", function(){
  //     $('#table-real').html('');
  //   if(kel.val()!=''){
  //       $('#thead-counter').hide();
  //       $('#thead-person').show();
  //   }else{
  //     $('#thead-person').hide();
  //     $('#thead-counter').show();
        
  //   }

  // });






    $('#show-tables').click(function(){
      // $('.table-real').removeClass('datatables');
      
        $.ajax({
          type: 'GET',
          url: "<?=url('realisasi/table/all')?>",
          data: { db: prov.val(), kab: kab.val(), kec: kec.val(), kel: kel.val(), pbp: pbp.val()}
        }).then(function (data) {
          // if(kab.val()!=''){
          //   table_real.destroy();
          // }
          
          console.log(data);
          var no = 0;
          var str = '';
            for(var i in data){
              
                if(kel.val()==''){
                    no += 1;
                    str += '<tr>';
                    str += '<td>'+no+'</td>';
                    str += '<td>'+data[i].nama+'</td>';
                    str += '<td><span class="badge bg-info">'+data[i].kuantum+'</span></td>';
                    str += '<td>'+data[i].transporter+'</td>';
                    str += '<td>'+data[i].persen_transporter+'%</td>';
                    str += '<td><span class="badge bg-success ">'+data[i].pbp+'</span></td>';
                    str += '<td>'+data[i].persen_pbp+'%</td>';
                    str += '</tr>';
                }else{
                    no += 1;
                    str += '<tr>';
                    str += '<td>'+no+'</td>';
                    str += '<td>'+data[i].nama+'</td>';
                    str += '<td>'+data[i].alamat+'</td>';
                    str += '<td><a href="'+data[i].path_pbp+'" target="_blank">Lihat foto</a></td>';
                    str += '<td><span class="badge '+data[i].status_penerima_class+'">'+data[i].status_penerima+'</span></td>';
                    str += '</tr>';
                }
                
                
                        //dataAll.push(data[i][j]);
              
              
            }

            $('#table-real').html(str);
            // $('.table-real').addClass('datatables');
           // table_real = $('.table-real').DataTable();
        });


        $.ajax({
          type: 'GET',
          url: "<?=url('realisasi/table/total')?>",
          data: { db: prov.val(), kab: kab.val(), kec: kec.val(), kel: kel.val(), pbp: pbp.val()}
        }).then(function (data) {
          console.log(data);
          var no = 0;
              var str = '';
             
                no += 1;
                str += '<tr>';
                str += '<td><span class="badge bg-info">'+data.kuantum+'</span></td>';
                str += '<td>'+data.transporter+'</td>';
                str += '<td>'+data.persen_transporter+'%</td>';
                str += '<td><span class="badge bg-success ">'+data.pbp+'</span></td>';
                str += '<td>'+data.persen_pbp+'%</td>';
                str += '</tr>';
                $('#total-table-real').html(str);
                        //dataAll.push(data[i][j]);
              
              
            
        });




    });








   });
   </script>
    
@stop