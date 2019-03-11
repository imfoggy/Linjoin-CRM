<?php
namespace app\facade;
use think\Facade;

class Index extends Facade{
	//可以在应用下面的common.php文件中统一配置，也可以用下面的方法进行单个配置
	// protected static function getFacadeClass(){
	// 	return 'app\index\controller\Index';
	// }
}