@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Generate Undangan Form</h1>
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
               <input type="hidden" name="admin" value="{{Auth::user()->admin}}">
               @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-block">
                    <button type="button" class="close" data-dismiss="alert">×</button>    
                    <strong>{{ $message }}</strong>
                </div>
                @endif
              <form id="add-form" action="{{url('new/generate/undangan')}}" method="GET">
                @csrf
                
                <div class="card-body">
                  <div class="form-group">
                    <label for="item_code">Tahun Penyaluran</label>
                    <input type="number" name="tahun" class="form-control" value="<?=date('Y')?>">
                  </div>
                  <div class="form-group">
                    <label for="item_code">Bulan Penyaluran</label>
                    <input type="text" name="bulan" class="form-control" value="{{$bulans}}">
                  </div>
                  <div class="form-group">
                    <label for="item_code">Jenis Penerima</label>
                    <select name="jenis_penerima" id="jenis_penerima" class="form-control select2">
                      @foreach($tambahan as $t => $v)
                      <option value="{{$t}}">{{$v}}</option>
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
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <!-- <button type="submit" class="btn btn-default">Cancel</button> -->
                  <button type="submit" class="btn btn-info float-right">Submit</button>
                  
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
@stop

@section('js')

   <!-- InputMask -->
<script src="{{asset('plugins/moment/moment.min.js')}}"></script>
   <script src="{{asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js')}}"></script>
   <script src="{{asset('plugins/select2/js/select2.full.min.js')}}"></script>
   <script type="text/javascript">
      $(document).ready(function(){
    var wil = $("#wilayah");
      var admin = $('input[name=admin').val();
      
   
    var kab = $("#kabupaten");
    var kec = $("#kecamatan");
    var kel = $("#kelurahan");

    
wil.trigger('change');
if(admin==0){
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
  });



kab.on("change", function(){
    $.ajax({
      type: 'GET',
      url: '/kecamatan/list',
      data: {kab: kab.val()}
  }).then(function (data) {
    console.log(data);
    kec.html("");
      // create the option and append to Select2
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
      url: '/kelurahan/list',
      data: {kec: kec.val()}
  }).then(function (data) {
    kel.html("");
    console.log(data);
      // create the option and append to Select2
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

   });
   </script>
@stop