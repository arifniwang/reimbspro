<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Config;
use Mail;

class ApiReimbursementController extends \crocodicstudio\crudbooster\controllers\ApiController
{
	
	function __construct()
	{
		$this->table = "pengajuan";
		$this->permalink = "reimbursement";
		$this->method_type = "post";
	}
	
	
	public function hook_before(&$postdata)
	{
		set_time_limit(90); // 1.5 minutes
		
		$validator['id'] = 'required'; //id users
		$validator['type'] = 'required|in:draft,submit';
		if (Request::input('type') == 'submit') {
			$validator['name'] = 'required';
			$validator['nota'] = 'required|file';
			CRUDBooster::Validator($validator);
			
			$nota = Request::file('nota');
			$extension = $nota->getClientOriginalExtension();
			$nota = file_get_contents($nota);
			$valid_json = CRUDBooster::isJSON($nota);
			$now = date('Y-m-d H:i:s');
			$created_at = (Request::input('created_at') == '' ? $now : date('Y-m-d H:i:s', strtotime(Request::input('created_at'))));
			
			$users = DB::table('users')
				->where('id', Request::input('id'))
				->first();
			if (empty($users)) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Akun anda tidak ditemukan';
			} elseif ($users->deleted_at != '') {
				$result['api_status'] = 0;
				$result['api_code'] = 440;
				$result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
			} elseif ($extension != 'json') {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Nota harus berupa json';
			} elseif ($nota == '' || !$valid_json) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Json tidak valid';
			} elseif (!is_array(json_decode($nota))) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Json harus berupa array';
			} else {
				$json_message = '';
				$json = json_decode($nota);
				$total_nominal = 0;
				
				/**
				 * VALIDATE JSON FILE
				 */
				foreach ($json as $row) {
					if ($row->type == 'new') {
						if ($row->date == '') {
							$valid_json = false;
							$json_message .= 'Tanggal nota tidak boleh kosong';
							break;
						} elseif ($row->nominal == '') {
							$valid_json = false;
							$json_message .= 'Nominal nota tidak boleh kosong';
							break;
						} elseif ($row->id_kategori == '') {
							$valid_json = false;
							$json_message .= 'Kategori nota tidak boleh kosong';
							break;
						} else {
							$total_nominal += (int)number_format($row->nominal, 0, '', '');
						}
					} elseif ($row->type == 'update' || $row->type == 'existing') {
						if ($row->date == '') {
							$valid_json = false;
							$json_message .= 'Tanggal nota tidak boleh kosong';
							break;
						} elseif ($row->nominal == '') {
							$valid_json = false;
							$json_message .= 'Nominal nota tidak boleh kosong';
							break;
						} elseif ($row->id_kategori == '') {
							$valid_json = false;
							$json_message .= 'Kategori nota tidak boleh kosong';
							break;
						} else {
							$total_nominal += (int)number_format($row->nominal, 0, '', '');
						}
					}
				}
				
				$check = DB::table('pengajuan')
					->where('id_users', Request::input('id'))
					->where('name', Request::input('name'))
					->where('total_nominal', $total_nominal)
					->whereDate('created_at', $created_at)
					->count();
				if ($check > 0) {
					$result['api_status'] = 0;
					$result['api_code'] = 401;
					$result['api_message'] = 'Pengajuan gagal, data duplicate';
				} else {
					if (!$valid_json) {
						$result['api_status'] = 0;
						$result['api_code'] = 401;
						$result['api_message'] = $json_message;
					} else {
						/**
						 * SAVE PENGAJUAN
						 */
						$id_pengajuan = Request::input('id_reimbursement');
						$save_pengajuan['time_server'] = $now;
						$save_pengajuan['strtotime'] = strtotime($created_at);
						$save_pengajuan['id_users'] = Request::input('id');
						$save_pengajuan['name'] = Request::input('name');
						$save_pengajuan['description'] = Request::input('description');
						$save_pengajuan['status'] = 'Diproses';
						$save_pengajuan['total_nominal'] = $total_nominal;
						if ($id_pengajuan == '' || $id_pengajuan == 0) {
							$save_pengajuan['created_at'] = $created_at;
							$id_pengajuan = DB::table('pengajuan')->insertGetId($save_pengajuan);
							$act = ($id_pengajuan ? TRUE : FALSE);
						} else {
							$save_pengajuan['updated_at'] = $now;
							$act = DB::table('pengajuan')->where('id', $id_pengajuan)->update($save_pengajuan);
						}
						
						if (!$id_pengajuan || !$act) {
							$result['api_status'] = 0;
							$result['api_code'] = 401;
							$result['api_message'] = 'Pengajuan gagal, silahkan di coba kembali';
						} else {
							/**
							 * SAVE DETAIL PENGAJUAN
							 */
							$table = '';
							$no = 1;
							$save = [];
							foreach ($json as $row) {
								if ($row->type == 'new') {
									$image = ($row->image == '' ? '' : CRUDBooster::uploadBase64($row->image));
									
									$rest['id_pengajuan'] = $id_pengajuan;
									$rest['created_at'] = $created_at;
									$rest['image'] = $image;
									$rest['date'] = date('Y-m-d', strtotime($row->date));
									$rest['nominal'] = number_format($row->nominal, 0, '', '');
									$rest['id_kategori'] = $row->id_kategori;
									$rest['description'] = $row->description;
									
									$save[] = $rest;
								} elseif ($row->type == 'update') {
									$detail = DB::table('pengajuan_detail')
										->where('id', $row->id)
										->first();
									$img = substr($row->image, 0, 4);
									if ($img != 'http') {
										$image = CRUDBooster::uploadBase64($row->image);
										$path_delete = storage_path('app/' . $detail->image);
										if ($detail->image != '' && file_exists($path_delete)) {
											unlink($path_delete);
										}
									} else {
										$image = $detail->image;
									}
									
									$change['updated_at'] = $now;
									$change['image'] = $image;
									$change['date'] = date('Y-m-d', strtotime($row->date));
									$change['nominal'] = number_format($row->nominal, 0, '', '');
									$change['id_kategori'] = $row->id_kategori;
									$change['description'] = $row->description;
									DB::table('pengajuan_detail')->where('id', $row->id)->update($change);
								} elseif ($row->type == 'delete') {
									$delete['deleted_at'] = $now;
									DB::table('pengajuan_detail')->where('id', $row->id)->update($delete);
								}
								
								/**
								 * MAKE TABLE EMAIL
								 */
								if ($row->type == 'new' || $row->type == 'update') {
									$kategori = DB::table('kategori')
										->where('id', $row->id_kategori)
										->first();
									
									$table .= '<tr>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      ' . $no++ . '
                  </td>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      <a href="' . url($image) . '" target="_blank">
                          <img src="' . url($image) . '" alt="" width="100%">
                      </a>
                  </td>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      ' . date('d M Y', strtotime($row->date)) . '
                  </td>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      ' . $kategori->name . '
                  </td>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      ' . $row->description . '
                  </td>
                  <td style="border: 1px solid #96a5b1;border-collapse: collapse;padding: 5px;color: #2d263b;
                                          vertical-align: top;font-size: 12px;">
                      Rp' . number_format($row->nominal, 0, ',', '.') . '
                  </td>
              </tr>';
								}
							}
							DB::table('pengajuan_detail')->insert($save);
							
							/**
							 * SEND NOTIFIKASI TO BACKEND
							 */
							$url = 'detail-pengajuan?parent_table=pengajuan&parent_columns=id_users,name,total_nominal,description&parent_columns_alias=Pegawai,Nama%20Pengajuan,Total,Dekripsi&parent_id=' . $id_pengajuan . '&return_url=' . CRUDBooster::adminPath('pengajuan') . '&foreign_key=id_pengajuan&label=';
							$cms_users = DB::table('cms_users')
								->join('cms_privileges', 'cms_privileges.id', '=', 'cms_users.id_cms_privileges')
								->get();
							$save_notif = [];
							foreach ($cms_users as $row) {
								$push_notif['id_cms_users'] = $row->id;
								$push_notif['is_read'] = 0;
								$push_notif['created_at'] = $now;
								$push_notif['content'] = 'Pengajuan - ' . $users->name;
								$push_notif['url'] = CRUDBooster::adminPath($url);
								$push_notif['id_relation'] = $id_pengajuan;
								$push_notif['type'] = 1; //pengajuan
								$save_notif[] = $push_notif;
							}
							DB::table('cms_notifications')->insert($save_notif);
							
							/**
							 * SEND NOTIFICATION TO APPS
							 */
							$save_usr_notif['created_at'] = date('Y-m-d H:i:s');
							$save_usr_notif['id_pengajuan'] = $id_pengajuan;
							$save_usr_notif['title'] = 'REIMBURSEMENT DIPROSES';
							$save_usr_notif['content'] = 'Pengajuan “' . Request::input('name') . '” berhasil dikirim dan sedang diproses';
							$save_usr_notif['date'] = date('Y-m-d');
							$save_usr_notif['type'] = 'Diproses';
							$act_notif = DB::table('users_notification')->insert($save_usr_notif);
							
							$regid = DB::table('users_regid')
								->where('id_users', $users->id)
								->pluck('regid')
								->toArray();
							$data_notif['title'] = $save_usr_notif['title'];
							$data_notif['content'] = $save_usr_notif['content'];
							$data_notif['id'] = $act_notif;
							$data_notif['id_pengajuan'] = $id_pengajuan;
							CRUDBooster::sendFCM($regid, $data_notif);
							
							/**
							 * SEND EMAIL
							 */
							$email = DB::table('email')
								->whereNull('deleted_at')
								->where('type', 'reimbursement_mail')
								->first();
							$cc = DB::table('email')
								->whereNull('deleted_at')
								->where('type', 'reimbursement_cc')
								->pluck('value')
								->toArray();
							$data = [];
							$data['users_image'] = ($users->image == '' ? '' : url($users->image));
							$data['users_name'] = $users->name;
							$data['users_phone'] = $users->phone;
							$data['created_at'] = date('d M Y, H:i');
							$data['name'] = Request::input('name');
							$data['description'] = Request::input('description');
							$data['table'] = $table;
							$data['total'] = 'Rp' . number_format($total_nominal, 0, ',', '.');
							$data['detail_pengajuan'] = CRUDBooster::adminPath($url);
							
							$config['to'] = $email->value;
							$config['cc'] = $cc;
							$config['data'] = $data;
							$config['subject'] = 'Pengajuan Reimbursement - ' . $users->name;
							$config['template'] = '/email/pengajuan';
							self::sendEmail($config);
							
							$result['api_status'] = 1;
							$result['api_code'] = 200;
							$result['api_message'] = 'Pengajuan berhasil ditambah';
							$result['id_reimbursement'] = $id_pengajuan;
						}
					}
				}
			}
		} else {
			$nota = Request::file('nota');
			$now = date('Y-m-d H:i:s');
			$created_at = (Request::input('created_at') == '' ? $now :
				date('Y-m-d H:i:s', strtotime(Request::input('created_at'))));
			$json = json_decode(file_get_contents($nota));
			$total_nominal = 0;
			
			$users = DB::table('users')
				->where('id', Request::input('id'))
				->first();
			if (empty($users)) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Akun anda tidak ditemukan';
			} elseif ($users->deleted_at != '') {
				$result['api_status'] = 0;
				$result['api_code'] = 440;
				$result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
			} elseif ($nota && $nota->getClientOriginalExtension() != 'json') {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Nota harus berupa json';
			} elseif ($nota && (file_get_contents($nota) == '' || !CRUDBooster::isJSON(file_get_contents($nota)))) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Json tidak valid';
			} elseif ($nota && !is_array($json)) {
				$result['api_status'] = 0;
				$result['api_code'] = 401;
				$result['api_message'] = 'Json harus berupa array';
			} else {
				/**
				 * VALIDATE JSON FILE
				 */
				foreach ($json as $row) {
					if ($row->type == 'delete') continue;
					$total_nominal += (int)number_format($row->nominal, 0, '', '');
				}
				
				$check = DB::table('pengajuan')
					->where('id_users', Request::input('id'))
					->where('name', Request::input('name'))
					->where('total_nominal', $total_nominal)
					->whereDate('created_at', $created_at)
					->count();
				if ($check > 0) {
					$result['api_status'] = 0;
					$result['api_code'] = 401;
					$result['api_message'] = 'Pengajuan gagal, data duplicate';
				} else {
					/**
					 * SAVE PENGAJUAN
					 */
					$id_pengajuan = Request::input('id_reimbursement');
					$save_pengajuan['time_server'] = $now;
					$save_pengajuan['strtotime'] = strtotime($created_at);
					$save_pengajuan['id_users'] = Request::input('id');
					$save_pengajuan['name'] = Request::input('name');
					$save_pengajuan['description'] = Request::input('description');
					$save_pengajuan['status'] = 'Draft';
					$save_pengajuan['total_nominal'] = $total_nominal;
					if ($id_pengajuan == '' || $id_pengajuan == 0) {
						$save_pengajuan['created_at'] = $created_at;
						$id_pengajuan = DB::table('pengajuan')->insertGetId($save_pengajuan);
						$act = ($id_pengajuan ? TRUE : FALSE);
					} else {
						$save_pengajuan['updated_at'] = $now;
						$act = DB::table('pengajuan')->where('id', $id_pengajuan)->update($save_pengajuan);
					}
					
					if (!$id_pengajuan || !$act) {
						$result['api_status'] = 0;
						$result['api_code'] = 401;
						$result['api_message'] = 'Pengajuan gagal, silahkan di coba kembali';
					} else {
						/**
						 * SAVE DETAIL PENGAJUAN
						 */
						$save = [];
						foreach ($json as $row) {
							if ($row->type == 'new') {
								$image = ($row->image == '' ? '' : CRUDBooster::uploadBase64($row->image));
								
								$rest['id_pengajuan'] = $id_pengajuan;
								$rest['created_at'] = $created_at;
								$rest['image'] = $image;
								$rest['date'] = date('Y-m-d', strtotime($row->date));
								$rest['nominal'] = number_format($row->nominal, 0, '', '');
								$rest['id_kategori'] = $row->id_kategori;
								$rest['description'] = $row->description;
								
								$save[] = $rest;
							} elseif ($row->type == 'update') {
								$detail = DB::table('pengajuan_detail')
									->where('id', $row->id)
									->first();
								$img = substr($row->image, 0, 4);
								if ($img != 'http') {
									$image = CRUDBooster::uploadBase64($row->image);
									$path_delete = storage_path('app/' . $detail->image);
									if ($detail->image != '' && file_exists($path_delete)) {
										unlink($path_delete);
									}
								} else {
									$image = $detail->image;
								}
								
								$change['updated_at'] = $now;
								$change['image'] = $image;
								$change['date'] = date('Y-m-d', strtotime($row->date));
								$change['nominal'] = number_format($row->nominal, 0, '', '');
								$change['id_kategori'] = $row->id_kategori;
								$change['description'] = $row->description;
								DB::table('pengajuan_detail')->where('id', $row->id)->update($change);
							} elseif ($row->type == 'delete') {
								$delete['deleted_at'] = $now;
								DB::table('pengajuan_detail')->where('id', $row->id)->update($delete);
							}
						}
						DB::table('pengajuan_detail')->insert($save);
						
						$result['api_status'] = 1;
						$result['api_code'] = 200;
						$result['api_message'] = 'Pengajuan berhasil ditambah';
						$result['id_reimbursement'] = $id_pengajuan;
					}
				}
			}
		}
		if (Request::file('nota')) {
			$nota = Request::file('nota');
			$nota->move(public_path('log_json'), $nota->getClientOriginalName());
		}
		$res = response()->json($result);
		$res->send();
		exit;
	}
	
	public function hook_query(&$query)
	{
		//This method is to customize the sql query
		
	}
	
	public function hook_after($postdata, &$result)
	{
		//This method will be execute after run the main process
		
	}
	
	public function sendEmail($config = [])
	{
		
		\Config::set('mail.driver', CRUDBooster::getSetting('smtp_driver'));
		\Config::set('mail.host', CRUDBooster::getSetting('smtp_host'));
		\Config::set('mail.port', CRUDBooster::getSetting('smtp_port'));
		\Config::set('mail.username', CRUDBooster::getSetting('smtp_username'));
		\Config::set('mail.password', CRUDBooster::getSetting('smtp_password'));
		
		$to = $config['to'];
		$data = $config['data'];
		$template = $config['template'];
		$cc = $config['cc'];
		$subject = $config['subject'];
		
		$template = file_get_contents(base_path('resources/views' . $template . '.blade.php'));
		$html = str_replace('[logo]', url('assets/image/img_reimbspro_logo.png'), $template);
		$html = str_replace('[line]', url('assets/image/line.png'), $html);
		$html = str_replace('[admin_path]', CRUDBooster::adminPath(), $html);
		foreach ($data as $key => $val) {
			$html = str_replace('[' . $key . ']', $val, $html);
		}
		$attachments = ($config['attachments']) ?: [];
		
		Mail::send("email/email_template", ['content' => $html], function ($message)
		use ($to, $subject, $template, $attachments, $cc) {
			$message->priority(1);
			$message->to($to);
			
			$from_name = 'RembsPro';
			$message->from('no-reply@rembspro.com', $from_name);
			
			if ($cc) {
				$message->cc($cc);
			}
			
			if (count($attachments)) {
				foreach ($attachments as $attachment) {
					$message->attach($attachment);
				}
			}
			
			$message->subject($subject);
		});
		
		return $html;
	}
	
}
