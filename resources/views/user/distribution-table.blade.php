@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>TAHAP {{$tahaptext->name}}</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-6 col-sm-12">
            <!-- Horizontal Form -->
            <div class="card card-info">
              <!-- /.card-header -->
              <!-- form start -->
              <input type="hidden" name="admin" value="{{Auth::user()->role}}">
              
              
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Daftar Penerima Bantuan (Belum Realisasi)</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="pbp-table" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>No Urut</th>
                    <th>Nama</th>
                    <th>Prefik</th>
                    <th>Alamat</th>
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
       
          <div class="col-md-6 col-sm-12">
            <div class="form-group">
      <label>Foto Realisasi</label>
    </div>
            <form action="{{url('entry/distribution/upload')}}" class="dropzone" id="upload-form">
    @csrf
    <input type="hidden" name="tahap" value="{{$tahap}}">
              <input type="hidden" name="wilayah" value="{{$wilayah}}">
              <input type="hidden" name="kabupaten" value="{{$kabupaten}}">
              <input type="hidden" name="kecamatan" value="{{$kecamatan}}">
              <input type="hidden" name="kelurahan" value="{{$kelurahan}}">
              <div class="form-group">
                <label>Tanggal Serah</label>
                <div class="col-sm-10 input-group date" id="expdate" data-target-input="nearest">
                        <input type="text" name="tgl_serah" class="form-control datetimepicker-input" data-target="#expdate" readonly/>
                        <div class="input-group-append" data-target="#expdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
              </div>
              <div class="form-group">
                <label>Status Penerima</label>
                <select class="form-control" name="status_penerima">
                  <option value="1">Penerima Sendiri</option>
                  <option value="2">Pengganti</option>
                  <option value="3">Perwakilan</option>
                  <option value="4">Kolektif</option>
                  </select>
              </div>
    <div class="previews"></div>
    
    
  <button type="submit" class="btn btn-success" id="btn-upload-form" style="display: none;">Submit</button>

  
  </form>
  <div class="form-group">
    <button class="btn btn-success item-right m-1" id="btn-upload">Simpan</button>
  </div>
  
          </div>
        </div>
        <div class="row">
          <!-- left column -->
          <div class="col-md-6 col-sm-12">
            <!-- Horizontal Form -->
            <div class="card card-info">
              <!-- /.card-header -->
              <!-- form start -->
              <input type="hidden" name="admin" value="{{Auth::user()->role}}">
              
              
              <div class="card">
              <div class="card-header">
                <h3 class="card-title">Daftar Penerima Bantuan (Sudah Realisasi)</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <table id="pbp-real-table" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>No Urut</th>
                    <th>Nama</th>
                    <th>Prefik</th>
                    <th>Alamat</th>
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

   

    let myDropzone = new Dropzone("#upload-form", {
        maxFiles: 100,
        acceptedFiles: ".jpg",
        addRemoveLinks: true,
        autoProcessQueue: false,
         headers: {
           'Cache-Control': null,
           'X-Requested-With': null,
        }, 
        init: function() {
          var myDropzone = this;
          
          // First change the button to actually tell Dropzone to process the queue.
          this.element.querySelector("button[type=submit]").addEventListener("click", function(e) {
            // Make sure that the form isn't actually being sent.
            e.preventDefault();
            e.stopPropagation();
            // var bn = myDropzone.element.querySelector("input[name=batch_name]").value;
            // console.log(bn);
            var t = $('input[name=tgl_serah').val();
            if(t==''){
              alert('Silahkan isi dahulu tanggal serah');
             
            }else{
               myDropzone.processQueue();
            }
           
            
          });

          this.on("success", function(file, resp) {

            console.log(resp);
            // resp = JSON.stringify(resp);
            console.log(resp.status);
            if(resp.status==true){
              console.log("berhasil");
              toastr.success('Berhasil menambah data penyaluran');
              dataPbp.ajax.reload();
              dataPbpReal.ajax.reload();
              myDropzone.removeFile(file);
            }else{
              toastr.warning(resp.msg);
            }
            
            
          });

          this.on("addedfile", function(file) {
            console.log(file.name);
            
            
          });
          }
        });
  
  var dataPbp = $("#pbp-table").DataTable({
    dom: '<"top"Bif>rt<"bottom"lp><"clear">',
      processing: true,
        serverSide: true,
        ajax: {
            url: "<?=url('entry/distribution/list')?>",
            data: function(form){
              var a = $('input[name=tahap]').val();
              var b = $('input[name=wilayah]').val();
              var c = $('input[name=kabupaten]').val();
              var d = $('input[name=kecamatan]').val();
              var e = $('input[name=kelurahan]').val();
               form.tahap = a;
               form.wilayah = b;
               form.kabupaten = c;
               form.kecamatan = d;
               form.kelurahan = e;
               form.real = false;
            },
            type: 'POST',
        },
        "columnDefs": [ {
        "targets": 0,
        "searchable": false,
        "render": function(data, type, row, meta){
            return meta.row + meta.settings._iDisplayStart + 1;
        },
      } ],
        columns: [
            { data: 'no_urut' },
            { data: 'nama' },
            { data: 'prefik' },
            { data: 'alamat' },
        ],
      "responsive": true, "lengthChange": false, "autoWidth": false,
    });


  var dataPbpReal = $("#pbp-real-table").DataTable({
    dom: '<"top"Bif>rt<"bottom"lp><"clear">',
      processing: true,
        serverSide: true,
        ajax: {
            url: "<?=url('entry/distribution/list')?>",
            data: function(form){
              var a = $('input[name=tahap]').val();
              var b = $('input[name=wilayah]').val();
              var c = $('input[name=kabupaten]').val();
              var d = $('input[name=kecamatan]').val();
              var e = $('input[name=kelurahan]').val();
               form.tahap = a;
               form.wilayah = b;
               form.kabupaten = c;
               form.kecamatan = d;
               form.kelurahan = e;
               form.real = true;
            },
            type: 'POST',
        },
        "columnDefs": [ {
        "targets": 0,
        "searchable": false,
        "render": function(data, type, row, meta){
            return meta.row + meta.settings._iDisplayStart + 1;
        },
      } ],
        columns: [
            { data: 'no_urut' },
            { data: 'nama' },
            { data: 'prefik' },
            { data: 'alamat' },
        ],
      "responsive": true, "lengthChange": false, "autoWidth": false,
    });


   });

    $("#btn-upload").click(function(){
      console.log("test");
      $("#btn-upload-form").click();
    });
   </script>
@stop