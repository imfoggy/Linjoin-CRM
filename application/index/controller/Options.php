<?php
namespace app\index\controller;
use think\Request;

class Options extends Base{

	use \app\traits\controller\Mytraits;

	public $config;

	public function initialize(){
		// $this->config = [
		// 	'product_unit'=> get_options('product_unit'),
		// ];

		// $this->assign('_config', $this->config);
	}

	/**
	 * 线索来源设置
	 * @Author Foggy
	 * @Date   2018-11-05
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @return [type]            [description]
	 */
	public function clueFrom(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$clueFrom = get_options('clue_from');
		switch ($action) {
			case 'save':
				if($request->value){
					$clueFrom[] = $request->value;
					$clueFrom = array_values($clueFrom);
					$string = '';
					if(count($clueFrom) > 0){
						foreach ($clueFrom as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('clue_from', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $clueFrom[$request->id] ? $clueFrom[$request->id] : '';
				break;

			case 'update':
				$id = $request->clue_from_id;
				$clueFrom[$id] = $request->value;
				$clueFrom = array_values($clueFrom);
				$string = '';
				if(count($clueFrom) > 0){
					foreach ($clueFrom as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('clue_from', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($clueFrom[$value]);
			            }
			        }else{
			        	unset($clueFrom[$request->id]);
			        }
					//然后重新索引数组
					$clueFrom = array_values($clueFrom);
					$string = '';
					if(count($clueFrom) > 0){
						foreach ($clueFrom as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('clue_from', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('clueFrom', $clueFrom);
				return view('clueFrom_index');
				break;
		}
	}

	/**
	 * 跟进类型
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function followUp(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$followUp = get_options('follow_up');
		switch ($action) {
			case 'save':
				if($request->value){
					$followUp[] = $request->value;
					$followUp = array_values($followUp);
					$string = '';
					if(count($followUp) > 0){
						foreach ($followUp as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('follow_up', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $followUp[$request->id] ? $followUp[$request->id] : '';
				break;

			case 'update':
				$id = $request->follow_up_id;
				$followUp[$id] = $request->value;
				$followUp = array_values($followUp);
				$string = '';
				if(count($followUp) > 0){
					foreach ($followUp as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('follow_up', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($followUp[$value]);
			            }
			        }else{
			        	unset($followUp[$request->id]);
			        }
					//然后重新索引数组
					$followUp = array_values($followUp);
					$string = '';
					if(count($followUp) > 0){
						foreach ($followUp as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('follow_up', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('followUp', $followUp);
				return view('followUp_index');
				break;
		}
	}

	/**
	 * 公司性质
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function companyProperty(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$companyProperty = get_options('customer_company_property');
		switch ($action) {
			case 'save':
				if($request->value){
					$companyProperty[] = $request->value;
					$companyProperty = array_values($companyProperty);
					$string = '';
					if(count($companyProperty) > 0){
						foreach ($companyProperty as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_company_property', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $companyProperty[$request->id] ? $companyProperty[$request->id] : '';
				break;

			case 'update':
				$id = $request->company_property_id;
				$companyProperty[$id] = $request->value;
				$companyProperty = array_values($companyProperty);
				$string = '';
				if(count($companyProperty) > 0){
					foreach ($companyProperty as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_company_property', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($companyProperty[$value]);
			            }
			        }else{
			        	unset($companyProperty[$request->id]);
			        }
					//然后重新索引数组
					$companyProperty = array_values($companyProperty);
					$string = '';
					if(count($companyProperty) > 0){
						foreach ($companyProperty as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_company_property', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('companyProperty', $companyProperty);
				return view('companyProperty_index');
				break;
		}
	}

	/**
	 * 客户状态
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function customerStatus(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$customerStatus = get_options('customer_status');
		switch ($action) {
			case 'save':
				if($request->value){
					$customerStatus[] = $request->value;
					$customerStatus = array_values($customerStatus);
					$string = '';
					if(count($customerStatus) > 0){
						foreach ($customerStatus as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_status', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $customerStatus[$request->id] ? $customerStatus[$request->id] : '';
				break;

			case 'update':
				$id = $request->customer_status_id;
				$customerStatus[$id] = $request->value;
				$customerStatus = array_values($customerStatus);
				$string = '';
				if(count($customerStatus) > 0){
					foreach ($customerStatus as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_status', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($customerStatus[$value]);
			            }
			        }else{
			        	unset($customerStatus[$request->id]);
			        }
					//然后重新索引数组
					$customerStatus = array_values($customerStatus);
					$string = '';
					if(count($customerStatus) > 0){
						foreach ($customerStatus as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_status', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('customerStatus', $customerStatus);
				return view('customerStatus_index');
				break;
		}
	}

	/**
	 * 客户行业
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function customerIndustry(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$customerIndustry = get_options('customer_industry');
		switch ($action) {
			case 'save':
				if($request->value){
					$customerIndustry[] = $request->value;
					$customerIndustry = array_values($customerIndustry);
					$string = '';
					if(count($customerIndustry) > 0){
						foreach ($customerIndustry as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_industry', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $customerIndustry[$request->id] ? $customerIndustry[$request->id] : '';
				break;

			case 'update':
				$id = $request->customer_industry_id;
				$customerIndustry[$id] = $request->value;
				$customerIndustry = array_values($customerIndustry);
				$string = '';
				if(count($customerIndustry) > 0){
					foreach ($customerIndustry as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_industry', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($customerIndustry[$value]);
			            }
			        }else{
			        	unset($customerIndustry[$request->id]);
			        }
					//然后重新索引数组
					$customerIndustry = array_values($customerIndustry);
					$string = '';
					if(count($customerIndustry) > 0){
						foreach ($customerIndustry as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_industry', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('customerIndustry', $customerIndustry);
				return view('customerIndustry_index');
				break;
		}
	}

	/**
	 * 客户来源
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function customerFrom(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$customerFrom = get_options('customer_from');
		switch ($action) {
			case 'save':
				if($request->value){
					$customerFrom[] = $request->value;
					$customerFrom = array_values($customerFrom);
					$string = '';
					if(count($customerFrom) > 0){
						foreach ($customerFrom as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_from', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $customerFrom[$request->id] ? $customerFrom[$request->id] : '';
				break;

			case 'update':
				$id = $request->customer_from_id;
				$customerFrom[$id] = $request->value;
				$customerFrom = array_values($customerFrom);
				$string = '';
				if(count($customerFrom) > 0){
					foreach ($customerFrom as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_from', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($customerFrom[$value]);
			            }
			        }else{
			        	unset($customerFrom[$request->id]);
			        }
					//然后重新索引数组
					$customerFrom = array_values($customerFrom);
					$string = '';
					if(count($customerFrom) > 0){
						foreach ($customerFrom as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_from', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('customerFrom', $customerFrom);
				return view('customerFrom_index');
				break;
		}
	}

	/**
	 * 员工数
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function customerEmployee(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$customerEmployee = get_options('customer_employee');
		switch ($action) {
			case 'save':
				if($request->value){
					$customerEmployee[] = $request->value;
					$customerEmployee = array_values($customerEmployee);
					$string = '';
					if(count($customerEmployee) > 0){
						foreach ($customerEmployee as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_employee', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $customerEmployee[$request->id] ? $customerEmployee[$request->id] : '';
				break;

			case 'update':
				$id = $request->customer_employee_id;
				$customerEmployee[$id] = $request->value;
				$customerEmployee = array_values($customerEmployee);
				$string = '';
				if(count($customerEmployee) > 0){
					foreach ($customerEmployee as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_employee', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($customerEmployee[$value]);
			            }
			        }else{
			        	unset($customerEmployee[$request->id]);
			        }
					//然后重新索引数组
					$customerEmployee = array_values($customerEmployee);
					$string = '';
					if(count($customerEmployee) > 0){
						foreach ($customerEmployee as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_employee', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('customerEmployee', $customerEmployee);
				return view('customerEmployee_index');
				break;
		}
	}

	/**
	 * 客户联系人角色
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function contactRole(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$contactRole = get_options('customer_contact_role');
		switch ($action) {
			case 'save':
				if($request->value){
					$contactRole[] = $request->value;
					$contactRole = array_values($contactRole);
					$string = '';
					if(count($contactRole) > 0){
						foreach ($contactRole as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_contact_role', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $contactRole[$request->id] ? $contactRole[$request->id] : '';
				break;

			case 'update':
				$id = $request->contact_role_id;
				$contactRole[$id] = $request->value;
				$contactRole = array_values($contactRole);
				$string = '';
				if(count($contactRole) > 0){
					foreach ($contactRole as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('customer_contact_role', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($contactRole[$value]);
			            }
			        }else{
			        	unset($contactRole[$request->id]);
			        }
					//然后重新索引数组
					$contactRole = array_values($contactRole);
					$string = '';
					if(count($contactRole) > 0){
						foreach ($contactRole as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('customer_contact_role', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('contactRole', $contactRole);
				return view('contactRole_index');
				break;
		}
	}

	/**
	 * 产品单位设置
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function productUnit(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$productUnit = get_options('product_unit');
		switch ($action) {
			case 'save':
				if($request->value){
					$productUnit[] = $request->value;
					$productUnit = array_values($productUnit);
					$string = '';
					if(count($productUnit) > 0){
						foreach ($productUnit as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('product_unit', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $productUnit[$request->id] ? $productUnit[$request->id] : '';
				break;

			case 'update':
				$id = $request->product_unit_id;
				$productUnit[$id] = $request->value;
				$productUnit = array_values($productUnit);
				$string = '';
				if(count($productUnit) > 0){
					foreach ($productUnit as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('product_unit', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($productUnit[$value]);
			            }
			        }else{
			        	unset($productUnit[$request->id]);
			        }
					//然后重新索引数组
					$productUnit = array_values($productUnit);
					$string = '';
					if(count($productUnit) > 0){
						foreach ($productUnit as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('product_unit', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('productUnit', $productUnit);
				return view('productUnit_index');
				break;
		}
	}

	/**
	 * 币种
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function currency(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$currency = get_options('currency_config');
		switch ($action) {
			case 'save':
				if($request->value){
					$currency[] = $request->value;
					$currency = array_values($currency);
					$string = '';
					if(count($currency) > 0){
						foreach ($currency as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('currency_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $currency[$request->id] ? $currency[$request->id] : '';
				break;

			case 'update':
				$id = $request->currency_id;
				$currency[$id] = $request->value;
				$currency = array_values($currency);
				$string = '';
				if(count($currency) > 0){
					foreach ($currency as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('currency_config', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($currency[$value]);
			            }
			        }else{
			        	unset($currency[$request->id]);
			        }
					//然后重新索引数组
					$currency = array_values($currency);
					$string = '';
					if(count($currency) > 0){
						foreach ($currency as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('currency_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('currency', $currency);
				return view('currency_index');
				break;
		}
	}

	/**
	 * 职位
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function positions(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$positions = get_options('positions_config');
		switch ($action) {
			case 'save':
				if($request->value){
					$positions[] = $request->value;
					$positions = array_values($positions);
					$string = '';
					if(count($positions) > 0){
						foreach ($positions as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('positions_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $positions[$request->id] ? $positions[$request->id] : '';
				break;

			case 'update':
				$id = $request->positions_id;
				$positions[$id] = $request->value;
				$positions = array_values($positions);
				$string = '';
				if(count($positions) > 0){
					foreach ($positions as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('positions_config', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($positions[$value]);
			            }
			        }else{
			        	unset($positions[$request->id]);
			        }
					//然后重新索引数组
					$positions = array_values($positions);
					$string = '';
					if(count($positions) > 0){
						foreach ($positions as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('positions_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('positions', $positions);
				return view('positions_index');
				break;
		}
	}

	/**
	 * 重量
	 * @Author Foggy
	 * @Date   2018-11-06
	 * @WeChat [vita_hacker]
	 * @Email  [x_foggy@163.com]
	 * @param  Request           $request [description]
	 * @return [type]                     [description]
	 */
	public function weight(Request $request){
		$action = $request->action ? $request->action : 'index';
		//线索客户来源
		$weight = get_options('weight_config');
		switch ($action) {
			case 'save':
				if($request->value){
					$weight[] = $request->value;
					$weight = array_values($weight);
					$string = '';
					if(count($weight) > 0){
						foreach ($weight as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('weight_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;

			case 'edit':
				return $weight[$request->id] ? $weight[$request->id] : '';
				break;

			case 'update':
				$id = $request->weight_id;
				$weight[$id] = $request->value;
				$weight = array_values($weight);
				$string = '';
				if(count($weight) > 0){
					foreach ($weight as $key => $value) {
						$key++;
						$string .= $key.'-'.$value.';';
					}

					$string = trim($string, ';');
				}

				$bool = set_option('weight_config', $string, 'string');

				if($bool > 0){
					$this->success('操作成功');
				}else{
					$this->error('操作失败');
				}
				break;

			case 'delete':
				if($request->id > 0){
					//判断id是单个还是多个
			        if(is_array($request->id)){
			            foreach ($request->id as $key => $value) {
			            	unset($weight[$value]);
			            }
			        }else{
			        	unset($weight[$request->id]);
			        }
					//然后重新索引数组
					$weight = array_values($weight);
					$string = '';
					if(count($weight) > 0){
						foreach ($weight as $key => $value) {
							$key++;
							$string .= $key.'-'.$value.';';
						}

						$string = trim($string, ';');
					}

					$bool = set_option('weight_config', $string, 'string');

					if($bool > 0){
						$this->success('操作成功');
					}else{
						$this->error('操作失败');
					}
				}
				break;
			
			default:
		        $this->assign('weight', $weight);
				return view('weight_index');
				break;
		}
	}
}