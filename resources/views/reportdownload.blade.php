<table border="1" width="100%">
    <tr>
        <td colspan="7">Nama : {{$name}}</td>
    </tr>
    <tr>
        <td colspan="7">No Telepon : {{$phone}}</td>
    </tr>

    <tr>
        <td height="20" colspan="7"></td>
    </tr>
    <tr>
        <td width="5%">No</td>
        <td width="20%">Nama Pengajuan</td>
        <td width="25%">Deskripsi</td>
        <td width="15%">Tanggal Pengajuan</td>
        <td width="15%">Tanggal Deisetujui / Ditolak</td>
        <td width="10%">Status</td>
        <td width="15%">Total</td>
    </tr>
    <?php
    $no = 1;
    $total = 0;
    $total_disetujui = 0;
    $total_ditolak = 0;
    $total_diproses = 0;
    ?>
    @foreach($pengajuan as $row)
        <tr>
            <td>{{$no++}}</td>
            <td>{{$row->name}}</td>
            <td>{{$row->description}}</td>
            <td>{{$row->created_at}}</td>
            <td>{{$row->datetime_approval}}</td>
            <td>{{$row->status}}</td>
            <td>Rp.{{number_format($row->total_nominal,0,'.','.')}}</td>
        </tr>
        <?php
        if ($row->status == 'Diproses') {
            $total_diproses += $row->total_nominal;
        } elseif ($row->status == 'Disetujui') {
            $total_disetujui += $row->total_nominal;
        } elseif ($row->status == 'Ditolak') {
            $total_ditolak += $row->total_nominal;
        }

        $total += $row->total_nominal;
        ?>
    @endforeach
    <tr>
        <td colspan="6" align="right"> Diproses</td>
        <td>Rp.{{number_format($total_diproses,0,'.','.')}}</td>
    </tr>
    <tr>
        <td colspan="6" align="right"> Disetujui</td>
        <td>Rp.{{number_format($total_disetujui,0,'.','.')}}</td>
    </tr>
    <tr>
        <td colspan="6" align="right"> Ditolak</td>
        <td>Rp.{{number_format($total_ditolak,0,'.','.')}}</td>
    </tr>
    <tr>
        <td colspan="6" align="right"> Total</td>
        <td>Rp.{{number_format($total,0,'.','.')}}</td>
    </tr>
</table>