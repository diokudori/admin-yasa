<?php 

$bulan = array (
		1 => 'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<style type="text/css">
		td, p{
			font-size: 8pt;
		}
		.list td{
  			border: 1px solid black;
  			border-collapse: collapse;
  			text-align: left;
  			font-size: 9pt;
		}

		@page {
  header: page-header;
  footer: page-footer;
  margin-top: 155px;
}
		*{
			font-family: "Calibri";
		}

		.t-head td{
			font-size: 7pt;
		}
	</style>
</head>
<body>
	<htmlpageheader name="page-header">
    <table style="width: 100%">
	<tr>
		<td style="width: 25%"><img src="{{public_path('assets/images/logo-yat.jpg')}}" style="height: 40px;"></td>
		<td style="font-size: 7pt; width: 50%;">
			<h3>BERITA ACARA SERAH TERIMA (BAST)<br>PENERIMA BANTUAN PANGAN - CBP <?=date('Y')?></h3>
			<h4>Nomor Dokumen Out: ...............................</h4>
			<h4>Alokasi Bulan / Tahap : <span style="text-transform: uppercase;"><?=$bulan[(int)date('m')]?></span> <?=date('Y')?></h4>
		</td>
		<td  style="width: 25%">
			<img src="{{public_path('assets/images/logo-bulog.jpeg')}}" style="height: 50px; margin-right: 0">
		</td>
	</tr>
</table>

<table style="width: 100%; font-size: 8pt;">
	<!-- <tr>
		<td style="width: 15%">Provinsi</td> <td style="width: 55%">: JAWA TIMUR</td> 
		<td style="width: 10%; display: none;">KCU / KC</td> <td style="width:20%; display: none;">: {{$kprk}}</td>
	</tr> -->
	<tr>
		<td>Provinsi</td> <td style="width: 50%">: JAWA TIMUR</td>
			<td>Kecamatan</td> <td style="width: 40%">: {{$kecamatan}}</td>
		 
		<!-- <td rowspan="3" style="display: none;">No BAST</td>
		 <td style="width: 20%; display: none;" rowspan="3">
		<p><img src="https://barcode.tec-it.com/barcode.ashx?data={{$prefik}}" style="width: auto; height: 40px;"></p>
	</td> -->
</tr>
	<tr>
		<td>Kabupaten</td> <td>: {{$kabupaten}}</td> 
		<td>Kelurahan/Desa</td> <td>: {{$kelurahan}}</td></tr>
	
</table>

<p style="margin-top: 0;">Kami yang bertanda tangan pada daftar dibawah ini, menyatakan dengan sebenar-benarnya bahwa telah menerima 10 Kg Beras Bantuan Pangan CBP <?=date('Y')?> dengan kualitas baik:</p>
</htmlpageheader>
<div>
	<?php $counter = 0;?>
	@foreach($list as $k => $lis)
<table style="width: 100%; padding-top: 400px; border: solid 1px #000; border-collapse: collapse;" class="list">
	<tr class="t-head"><td style="width: 5%;text-align: center;">NO</td> <td style="width: 22%;text-align: center;">NAMA</td> <td style="width: 40%;text-align: center;">ALAMAT</td> 
		<!-- <td colspan="2" style="width: 10%; text-align: center;">NOMOR BARCODE</td>  -->
		<td style="width: 8%;text-align: center;">JUMLAH <br>(KG)</td> <td style="width: 10%;text-align: center;">TTD</td> 
		<!-- <td style="width: 10%;text-align: center;">TGL SERAH</td> -->
	</tr>
	<?php $odd = true; $show = false;?>
	@foreach($lis as $l)
	<?php $counter++; 
		$qr = $l->prefik.sprintf("%04s", $counter);
	?>
	<tr>
		<td style="text-align: center">{{$counter}}</td>
		<td style="padding: 10px; font-size: 7pt;">{{$l->nama}}</td>
		<td style="padding: 10px; font-size: 7pt;">{{$l->alamat}}, RT {{$l->rt}}, RW {{$l->rw}}</td>
		@if($show)
				@if($odd)
				<td style="text-align: center; padding: 5px 0;"><img src="http://chart.googleapis.com/chart?cht=qr&chs=42x42&choe=UTF-8&chld=L|0&chl={{$qr}}"></td>
				<td></td>
					<?php $odd = false;?>
				@else
				<td></td>
				<td style="text-align: center; padding: 5px 0;"><img src="http://chart.googleapis.com/chart?cht=qr&chs=42x42&choe=UTF-8&chld=L|0&chl={{$qr}}"></td>
					<?php $odd = true;?>
				@endif
		@endif
		
		<td style="text-align: center"><p>10</p></td>
		<td></td>
	</tr>
	@endforeach
	
</table>
@if($k == (sizeof($list)-1))
<br>
<table style="width: 100%">
	<tr>
		<td style="width: 5%; padding: 2.5%"><img src="{{public_path('assets/images/cap.png')}}" style="opacity: .5;"></td>
		<td style="width: 20%">
			<p><b>Aparat Setempat*</b></p>
			<br><br><br>
			<hr>
		</td>
		<td style="width: 50%">		
		<td ></td>
		<td style="width: 5%">		
		<td style="width: 5%; padding: 2.5%"><img src="{{public_path('assets/images/cap.png')}}" style="opacity: .5;"></td>
		<td style="width: 25%">		
			<p style="text-align: right; width: 100%;">..................,..............<?=date('Y')?></p>

			<p>Transporter</p>
			<br><br><br>
			<hr>
		</td>
	</tr>
</table>
@endif
<html-separator/>
<pagebreak>
@endforeach
</div>
<htmlpagefooter name="page-footer">
    
<p style="text-align: right;">Halaman <span>{PAGENO}</span> dari <span>{nb}</span></p>
</htmlpagefooter>




</body>
</html>