<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		@page {
		  header: page-header;
		  footer: page-footer;
		  margin-top: 10px;
		}
		*{
			font-family: calibri;
		}
		p{
			margin-bottom: 0;
			margin-top:0;
			font-size: 9pt;
		}
		ol{
			margin-left: 0;
			margin-block-start: 0;
			margin-inline-start: 0;
			padding-inline-start: 15px;
		}

		.text {
  /*align-self: flex-start;*/
  vertical-align: middle;
}


.paragraph {
  /*display: flex;*/
  text-align: right;
}
	</style>
</head>
<body>
	<?php $counter = 0;?>
@foreach($list as $lis)
		@foreach($lis as $k => $l)
		<?php //$counter++;
			// $qr = $l->prefik.sprintf("%04s", $counter);
			$qr = $l->prefik;
			// $alamat = $l->alamat.", RT ".$l->rt.", RW ".$l->rw.", ".$l->kelurahan.", ".$l->kecamatan.", ".$l->kabupaten;
			$alamat = $l->alamat.", RT ".$l->rt.", RW ".$l->rw.", ".$l->kelurahan.", ".$l->kecamatan;
			// $str = strlen($alamat);
			// if($str>75){
			// 	$alamat = $l->alamat.", RT ".$l->rt.", RW ".$l->rw.", ".$l->kelurahan.", ".$l->kecamatan;
			// }
		?>
<table style="width: 100%">
	<tr>
		<td style="width: 33%"><img src="{{public_path('assets/images/logo-bulog.jpeg')}}" style="height: 35px;"></td>
		<td style="width: 33%"><img src="{{public_path('assets/images/logo-bpn.jpeg')}}" style="height: 35px;"></td>
		<td style="width: 33%"><img src="{{public_path('assets/images/logo-yat.jpg')}}" style="height: 35px;"></td>
	</tr>

</table>
<table style="width: 100%">
	<tr>
		<td style="width: 30%; vertical-align: top;" >
			<p><b>PEMBERITAHUAN<b></p>
			<p>Nomor: {{$l->prefik}}</p>
		</td>
		<td>
			<p><b>KEPADA:</b></p>
			<p>{{$l->nama}}</p>
			<p>{{$alamat}}</p>
		</td>
	</tr>
</table>
<div style="font-size: 8pt !important; ">
<p>Dengan Hormat,</p>
<p style="margin-bottom: 0;">Berdasarkan Keputusan Pemerintah Republik Indonesia c.q. Badan Pangan Nasional Republik Indonesia, Bapak/Ibu/Sdr/i dinyatakan berhak memperoleh Bantuan Pangan Tahun {{$tahun}} dari Pemerintah RI. Harap menjadi perhatian Bapak/Ibu penerima Bantuan Pangan <span style="text-transform: capitalize;">{{$bulans}}</span> {{$tahun}}:</p>
<ol style="margin-top:0; font-size: 9pt;">
<li>Persyaratan pengambilan/penerimaan Bantuan Pangan Tahun {{$tahun}} dengan membawa dan menunjukkan Surat Undangan, KTP-el dan/atau Kartu Keluarga asli.</li>
<li>Dalam penyaluran Bantuan Pangan Tahun {{$tahun}} PT. Yasa Artha Trimanunggal tidak memungut biaya apapun. Jika ada pungutan oleh Petugas silahkan laporkan dengan menghubungi PIC Kantor Pusat PT. Yasa Artha Trimanunggal, dengan melampirkan bukti terkait.</li>
<li>Pada saat penyerahan akan dilakukan pendataan geotagging dan foto diri PBP (Penerima Bantuan Pangan) Tahun {{$tahun}} oleh Juru Serah.</li>
</ol>
<table style="width: 100%">
	<tr>
		<td>
			<p>Hormat Kami,</p>
			<p>PT. Yasa Artha Trimanunggal</p>
			
		</td>
		<td style="text-align: right">
			
			<p style="vertical-align: middle; font-size: 28pt;">
				{{$l->no_urut}}
				<?php 
			$qrcode = QrCode::size(42)->generate($qr);
			$code = (string)$qrcode;
       		echo substr($code,38);
			?>
			
		</p>
		</td>
		<!-- <td style="border: solid 1px #000; border-collapse: collapse; text-align: center; display: none;">
			<p>Tanda Tangan Penerima</p>
			<br>
			<br>
			<br>
			<p>{{$l->nama}}</p>
		</td>
		<td style="border: solid 1px #000; border-collapse: collapse; text-align: center; display: none;">
			<p>Paraf Petugas</p>
			<br>
			<br>
			<br>
			<br>
			<br>
			<p></p>
		</td> -->
	</tr>
</table>
</div>
@if(($k+1)%3!=0)
<hr>
@endif
		@endforeach

<html-separator/>
<pagebreak>
@endforeach
<!-- <htmlpagefooter name="page-footer">
<p><b>Catatan:</b></p>
<p>Surat Pemberitahuan ini berlaku juga sebagai Undangan untuk Pengambilan Bantuan Pangan.</p>
</htmlpagefooter> -->
</body>
</html>