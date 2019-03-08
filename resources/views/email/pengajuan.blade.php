<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reimbursement</title>
</head>
<body>

<div align="center">
    <table max-width="600" cellpadding="0" cellspacing="0"
           style="background-color: #FFF;color:#2d263b;font-family: Sans-Serif;border: 0;border: 1px solid #ddd;padding: 0;">
        <tbody>
        <tr style="background-color: #04233C;color: #FFF;">
            <td style="padding: 10px 0 10px 15px;">
                <img src="[logo]" alt="" style="max-width: 100%;height: 50px;">
            </td>
            <td align="right" style="font-size: 14px;padding-right: 15px;">Pengajuan Reimbursement</td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 25px 15px 10px;text-align: center;">
                <img src="[users_image]" alt="" height="100px" width="auto">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 0 15px 0 15px;text-align: center;">
                [users_name]
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 0 15px 50px 15px;text-align: center;font-size: 14px;color: #6b767e;">
                <a href="tel:[users_phone]" style="color: #04233C;text-decoration: none;">[users_phone]</a>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 15px;">
                    <td style="font-size: 14px;color: #6b767e;padding-bottom: 10px;vertical-align: top;">Tanggal</td>
                    <td align="right" style="font-size: 14px;padding-bottom: 10px;">[created_at]</td>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 15px;">
                    <td style="font-size: 14px;color: #6b767e;padding-bottom: 10px;vertical-align: top;">
                        Nama Pengajuan
                    </td>
                    <td align="right" style="font-size: 14px;padding-bottom: 10px;">[name]</td>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 15px;">
                    <td style="font-size: 14px;color: #6b767e;padding-bottom: 10px;vertical-align: top;">Deskripsi</td>
                    <td align="right" style="font-size: 14px;padding-bottom: 10px;" width="70%">
                        [description]
                    </td>
                </table>
            </td>
        </tr>
        <tr style="padding-left: 15px;padding-right: 15px;">
            <td colspan="2">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 15px;">
                    <td colspan="2"
                        style="background:url('[line]') repeat-x;
                                height:15px;text-align:center;margin-bottom:20px;font-size:14px;
                                font-family:'Lato',Arial,Helvetica">
                        <span style="background:#ffffff;padding:0 10px;color:#6b767e">DETAILS</span>
                    </td>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="570px" cellpadding="0" cellspacing="0"
                       style="margin:0 15px 30px;font-size: 14px;table-layout: auto;table-layout: fixed;
                       margin-top: 15px;border-collapse: collapse;">
                    <tr style="border-collapse: collapse;">
                        <td width="6%"
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            No
                        </td>
                        <td width="10%"
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            Foto
                        </td>
                        <td width="20%"
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            Tanggal
                        </td>
                        <td width="15%"
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            Kategori
                        </td>
                        <td width=""
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            Deskripsi
                        </td>
                        <td width="19%"
                            style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;">
                            Nominal
                        </td>
                    </tr>
                    [table]
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table width="100%" cellpadding="0" cellspacing="0" style="padding:0 15px 30px;">
                    <td style="font-size: 14px;color:#6b767e;padding-bottom: 10px;vertical-align: top;font-weight: bold;">
                        Total
                    </td>
                    <td align="right" style="font-size: 14px;padding-bottom: 10px;font-weight: bold;">
                        [total]
                    </td>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="background:#f8f8f8;padding:10px 0">
                <div>
                    <p style="text-align:center">
                        <a href="[admin_path]" style="color:#2DC399;text-decoration: none;"
                           target="_blank">
                            Masuk Panel Admin
                        </a><br>
                        <a href="[detail_pengajuan]"
                           style="font-size:12px;text-align:center;color:#6b767e;text-decoration: none;"
                           target="_blank">
                            Lihat Detail Pengajuan
                        </a>
                    </p>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

</body>
</html>