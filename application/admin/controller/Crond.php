<?php



namespace app\admin\controller;

use think\Db;

use think\Controller;

class Crond extends Controller {
	function __construct(){
	parent::__construct();
	if ( isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] != $_SERVER['SERVER_ADDR'] ) {
		 header("HTTP/1.1 404 Not Found");exit;
		}
	}

	/**

	*curl data

	*/

	public  function catch_dl()

	{

		$res = getWarningFromJjs();

		if($res['status']=='y'){

			$db = Db::name('warning');

			if(count($res['list'])!=0){

				//有报警

				$ids = '';
				foreach ($res['list'] as $value) {

					$data = array();

					$ids .= $value['id'].',';

					$m = $db->where(['id'=>$value['id']])->count();

					if($m==0)

					{
						$data = [
							'id'=>$value['id'],
							'title'=>$value['title'],
							'where'=>$value['where'],
							'num'=>$value['num'],
							'jx_num'=>$value['jx_num'],
							'status'=>$value['status'],
							'haoma'=>$value['haoma'],
							'kd_num'=>$value['kd_num'],
							'w_time'=>date("Y-m-d H:i:s")
						];
						$db->insert($data);

					}else

					{
						$data = ['id'=>$value['id'],'u_time'=>date("Y-m-d H:i:s")];
						$db->update($data);

					}

				}

				//修改不在报警中的数据为失效

				$ids = trim($ids,',');

				$sql = 'update '.config('database.prefix').'warning set status=1 where id not in ('.$ids.')';

				echo $sql;

				$db->execute($sql);

			}else{

				//修改不在报警中的数据为失效

				$sql = 'update '.config('database.prefix').'warning  set status=1 where status=0';

				$db->execute($sql);

				echo $sql;

			}

			echo $res['info'].date('Y-m-d H:i:s')."\r\n";

		}

	}

}

