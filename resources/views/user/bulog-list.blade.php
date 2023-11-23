@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Daftar Penyaluran BULOG</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12 col-sm-12">
            <!-- Horizontal Form -->
            <div class="card card-info">
              <!-- /.card-header -->
              <!-- form start -->
              <input type="hidden" name="admin" value="{{Auth::user()->role}}">
              <input type="hidden" name="provinsi" id="provinsi" value="{{Auth::user()->db}}">
              <input type="hidden" name="name" id="name" value="{{Auth::user()->name}}">
              
              
              <div class="card">
              <div class="card-header">
                <div class="form-group">
                  <label>Tahap Penyaluran</label>
                  <select name="tahap" class="form-control">
                    @foreach($tahap as $t)
                    <option value="{{$t->value}}">{{$t->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group">
                  <label for="item_code">Kota/Kabupaten</label>
                        <select name="kabupaten" id="kabupaten" class="form-control select2">
                          @foreach($kabupaten as $k)
                    <option value="{{$k->kabupaten}}">{{$k->kabupaten}}</option>
                    @endforeach
                          </select>
                </div>
                <div class="form-group">
                  <label for="item_code">Kecamatan</label>
                        <select name="kecamatan" id="kecamatan" class="form-control select2">
                          <option value="">Pilih Kecamatan</option>   
                        </select>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="bulog-table" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>No.</th>
                    <th>Nomor Surat Jalan</th>
                    <th>Nomo DO</th>
                    <th>Wilayah</th>
                    <th>Tanggal Alokasi</th>
                    <th>Titik Penyerahan</th>
                    <th>Jumlah</th>
                    <th>Status Hit</th>
                  </tr>
                  </thead>
                  <tbody>
                    
                  </tbody>
                  
                </table>
              </div>
              <!-- /.card-body -->
            </div>
    
            </div>
            <!-- /.card -->

          </div>
          <!--/.col (left) -->
       
        </div>
        
      </div><!-- /.container-fluid -->
@stop

@section('css')
     <link rel="stylesheet" href="{{asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css')}}">
      <link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  	<link rel="stylesheet" href="{{asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
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

<script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
<script src="https://unpkg.com/imask"></script>
<script src="{{asset('plugins/daterangepicker/daterangepicker.js')}}"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
  Dropzone.autoDiscover = false;
  // Note that the name "myDropzone" is the camelized
  // id of the form.

</script>
   <script type="text/javascript">
   	$(document).ready(function(){

      $('#expdate').datetimepicker({
        format: 'YYYY-MM-DD'
    });

      // $('input[name=tgl_serah]').attr('readonly','true');

    $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
  
  var dataPbp = $("#bulog-table").DataTable({
    dom: '<"top"Bif>rt<"bottom"lp><"clear">',
      processing: true,
        serverSide: true,
        ajax: {
            url: "<?=url('bulog/list/data')?>",
            data: function(form){
              var a = $('select[name=tahap]').val();
              var b = $('select[name=kabupaten]').val();
              var c = $('select[name=kecamatan]').val();
               form.tahap = a;
               form.kabupaten = b;
               form.kecamatan = c;
            },
            type: 'POST',
        },
        "columnDefs": [ {
        "targets": 0,
        "searchable": false,
        "render": function(data, type, row, meta){
            return meta.row + meta.settings._iDisplayStart + 1;
        },
      },{
        "targets": 3,
        "searchable": false,
        "render": function(data, type, row, meta){
          var string = '<p>Kab: '+data['kab']+'<br>';
            string += 'Kec: '+data['kec']+'<br>';
            string += 'Kel: '+data['kel']+'<br>';
            string += 'Kecamatan id bulog: '+data['kecamatan_id']+'<br></p>';
            return string;
        },
      },{
        "targets": 6,
        "searchable": false,
        "render": function(data, type, row, meta){
          // var string = '<p>Kuantum: '+data['kuantum']+'<br>';
          var string = '<p>';
          string += 'PBP: '+data['jumlah_pbp']+'<br>';
            string += 'SPTJM: '+data['jumlah_sptjm']+'<br></p>';
            return string;
        },
      },{
        "targets": 7,
        "searchable": false,
        "render": function(data, type, row, meta){
          var string = '';

            if(data.status_hit=='1'){
              string = '<span class="badge badge-success">Sudah</span>';
            }else{
              string = '<span class="badge badge-warning">Belum</span><br><button class="btn btn-success btn-upload" data-id="'+data.id+'">Unggah ke Bulog</button>';
            }
            return string;
        },
      } ],
        columns: [
            { data: null },
            { data: 'transporter_bast' },
            { data: 'no_out' },
            { data: null },
            { data: 'tanggal_alokasi' },
            { data: 'titik_penyerahan' },
            { data: null },
            { data: null },
        ],
      "responsive": true, "lengthChange": false, "autoWidth": false,
    });

$('select[name=tahap]').on('change',function(){
  dataPbp.ajax.reload();
});

var prov = $('#provinsi');
var name = $('#name');
var kab = $('#kabupaten');
var kec = $('#kecamatan');
kab.on("change", function(){
   if(kab.val()==''){
    kec.html('');
        var option = new Option("Pilih Kecamatan", "", true, true);
          kec.append(option);
      kec.val('');
      kec.trigger('change');
    }

    $.ajax({
      type: 'GET',
      url: "<?php echo url('realisasi/kecamatan/list')?>",
      data: { db: prov.val(), table: name.val() , kab: kab.val()}
  }).then(function (data) {
    console.log(data);
      // create the option and append to Select2
      kec.html('');
       var option = new Option("Pilih Kecamatan", "", true, true);
        kec.append(option);
      for(var i in data){
        var option = new Option(data[i].kecamatan, data[i].kecamatan, true, true);
        kec.append(option);
      }
      
      // kec.trigger('change');
      kec.val('');
      // manually trigger the `select2:select` event
      kec.trigger({
          type: 'select2:select',
          params: {
              data: data
          }
      });
  });
  });

 kab.trigger('change');

 kec.on('change', function(){
    if(kec.val()!=''){
      dataPbp.ajax.reload();
    }
 });

});
   </script>
@stop