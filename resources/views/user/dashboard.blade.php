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
        @foreach($data as $d)
        <div class="row">
          <div class="col-md-12">
            <h3>{{$d['provinsi']}}</h3>
          </div>
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
            	<div class="overlay dark">
      						<i class="fas fa-3x fa-sync-alt fa-spin"></i>
      				</div>
              <div class="inner">
                <h3 id="total-all">{{$d['totalAll']}}</h3>

                <p>Kuantum</p>
              </div>
              <div class="icon">
                <i class="ion ion-bag"></i>
              </div>
              <a href="#" class="small-box-footer"></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
            	<div class="overlay dark">
      						<i class="fas fa-3x fa-sync-alt fa-spin"></i>
      				</div>
              <div class="inner">
                <h3 id="real-all">{{$d['totalReal']}}</h3>

                <p>Terealisasi</p>
              </div>
              <div class="icon">
                <i class="ion ion-stats-bars"></i>
              </div>
              <a href="#" class="small-box-footer"></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
            	<div class="overlay dark">
						<i class="fas fa-3x fa-sync-alt fa-spin"></i>
				</div>
              <div class="inner">
                <h3 id="not-real-all">{{$d['totalNotReal']}}</h3>

                <p>Sisa Realisasi</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="#" class="small-box-footer"></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
            	<div class="overlay dark">
						<i class="fas fa-3x fa-sync-alt fa-spin"></i>
				</div>
              <div class="inner">
              	
                <h3 ><span id="persen-all">{{$d['totalPercent']}}</span><sup style="font-size: 20px">%</sup></h3>

                <p>Prosentase</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="#" class="small-box-footer"></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->

        @endforeach
        <!-- Main row -->
        <div class="row" style="display: none;">
        	<div class="col-md-3">
        		<div class="form-group">
        			<label>Urutkan Berdasarkan</label>
        			<select name="filter" class="form-control">
        				<option value="totalAll">Kuantum</option>
        				<option value="totalReal">Terealisasi</option>
        				<option value="totalNotReal">Sisa Realisasi</option>
        				<option value="totalPercent">Prosentase</option>
        			</select>
        		</div>
        	</div>
        	<div class="col-md-3">
        		<div class="form-group">
        			<label>Urutan</label>
        			<select name="order_by" class="form-control">
        				<option value="asc">A-Z 0-9</option>
        				<option value="desc">Z-A 9-0</option>
        			</select>
        		</div>
        	</div>
        	<div class="col-md-1">
        		<div class="form-group">
        			<label style="color: transparent;">Action</label>
        			<button class="btn btn-primary form-control" id="btn-filter" disabled>Urutkan</button>
        		</div>
        	</div>
        </div>
        <div class="row" style="display: none;">
          <div class="col-md-6">
          	<div class="card">
              <!-- /.card-header -->
              <div class="card-body p-0">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th style="width: 10px">No.</th>
                      <th>Wilayah</th>
                      <th>Kuantum</th>
                      <th>Terealisasi</th>
                      <th>Sisa Realisasi</th>
                      <th>Prosentase</th>
                    </tr>
                  </thead>
                  <tbody id="table-real-left">
                    
                    
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <div class="col-md-6">
          	<div class="card">
              <!-- /.card-header -->
              <div class="card-body p-0">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th style="width: 10px">No.</th>
                      <th>Wilayah</th>
                      <th>Kuantum</th>
                      <th>Terealisasi</th>
                      <th>Sisa Realisasi</th>
                      <th>Prosentase</th>
                    </tr>
                  </thead>
                  <tbody id="table-real-right">
                    
                    
                  </tbody>
                </table>
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
    <style type="text/css">
    	.badge{
    		font-size: 100% !important;
    		width: 100%;
    	}
    	.content-wrapper {
    		background-color: #fff;
    	}
    </style>
@stop

@section('js')
    <script type="text/javascript">
    	$(document).ready(function(){

        $('.overlay').hide();

    		var dataAll = [];
    		$.ajax({
			    type: 'GET',
			    url: 'api/data/dashboard/all',
			}).then(function (data) {
				console.log(data);
			    $("#total-all").html(data.totalAll);
			    $("#real-all").html(data.totalReal);
			    $("#not-real-all").html(data.totalNotReal);
			    $("#persen-all").html(data.totalPercent);
			    $(".overlay").hide();
			    $('#btn-filter').removeAttr('disabled');
			});

			// $.ajax({
			//     type: 'GET',
			//     url: 'api/data/dashboard/wilayah',
			// }).then(function (data) {
			// 	console.log(data);
			// 	var no = 0;
			//     for(var i in data){
			//     	var str = '';
			//     	for(var j in data[i]){
			//     		no += 1;
			//     		str += '<tr>';
			//     		str += '<td>'+no+'</td>';
			//     		str += '<td>'+data[i][j].name+' '+data[i][j].email+'</td>';
			//     		str += '<td><span class="badge bg-info">'+data[i][j].totalAll+'</span></td>';
			//     		str += '<td><span class="badge bg-success">'+data[i][j].totalReal+'</span></td>';
			//     		str += '<td><span class="badge bg-danger">'+data[i][j].totalNotReal+'</span></td>';
			//     		str += '<td><span class="badge bg-warning">'+data[i][j].totalPercent+'%</span></td>';
      //               	str += '</tr>';

      //               	dataAll.push(data[i][j]);
			//     	}
			//     	if(i==0){
      //               		$("#table-real-left").html(str);
      //               	}else if(i==1){
			// 				$("#table-real-right").html(str);
      //               	}
			//     }
			// });

			

			$('#btn-filter').on('click', function(){
				var order_by = $('select[name=order_by]').find(":selected").val();
			var filter = $('select[name=filter]').find(":selected").val();
					$('#btn-filter').attr('disabled','true');
				$.ajax({
				    type: 'POST',
				    url: 'api/data/dashboard/wilayah/filter',
				    data: { dataAll: dataAll, order_by: order_by, filter: filter }
				}).then(function (data) {
					console.log(data);

					var no = 0;
				    for(var i in data){
				    	var str = '';
				    	for(var j in data[i]){
				    		no += 1;
				    		str += '<tr>';
				    		str += '<td>'+no+'</td>';
				    		str += '<td>'+data[i][j].name+' '+data[i][j].email+'</td>';
				    		str += '<td><span class="badge bg-info">'+data[i][j].totalAll+'</span></td>';
				    		str += '<td><span class="badge bg-success">'+data[i][j].totalReal+'</span></td>';
				    		str += '<td><span class="badge bg-danger">'+data[i][j].totalNotReal+'</span></td>';
				    		str += '<td><span class="badge bg-warning">'+data[i][j].totalPercent+'%</span></td>';
	                    	str += '</tr>';

	                    	
				    	}
				    	if(i==0){
	                    		$("#table-real-left").html(str);
	                    	}else if(i==1){
								$("#table-real-right").html(str);
	                    	}

	                    	$('#btn-filter').removeAttr('disabled');
				    }
				});

			});
			



				 
    	});
    </script>
@stop