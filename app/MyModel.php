<?php

namespace App;
Use DB;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\MyModel;

class MyModel extends Authenticatable{
    protected $fillable = [];
	protected $guarded = [];
	protected $model_class = null;
	protected $main_model = null;
	
	function count_all_records($options = array()){
		$this->_call_start_function();
		$options['select'] = 'id';
		$cache_keys = $this->_get_cache_key('cache_count_all_records', $options);
		$result = $this->_cache_process($cache_keys[0], $cache_keys[1], array(), 'GET', $options);
		if($result !== FALSE){
			return $this->_call_last_function($result, false);
		}else{
			if(!$this->_get_conditions($options)){
				return $this->_call_last_function(0, false);
			}else{
				$result = $this->main_model->count();
				$this->_cache_process($cache_keys[0], $cache_keys[1], $result, 'SAVE', $options);
				return $this->_call_last_function($result, true);
			}	
		}
	}
	function search_all_records($options = array()){
		$this->_call_start_function();
		$cache_keys = $this->_get_cache_key('cache_search_all_records', $options);
		$result = $this->_cache_process($cache_keys[0], $cache_keys[1], array(), 'GET', $options);
		if($result !== FALSE){
			return $this->_call_last_function($result, false);
		}else{
			if(!$this->_get_conditions($options)){
				return $this->_call_last_function(array(), false);
			}else{
				if(isset($options['first'])){
					$result = $this->main_model->first();
				}else{
					$result = $this->main_model->get();
				}
				$this->_cache_process($cache_keys[0], $cache_keys[1], $result, 'SAVE', $options);
				return $this->_call_last_function($result, true);
			}	
		}
	}
	function search_record_by_id_object($id, $options = array()){
		$this->_call_start_function();
		$cache_keys = $this->_get_cache_key('cache_search_record_by_id_object_'.$id, $options);
		$result = $this->_cache_process($cache_keys[0], $cache_keys[1], array(), 'GET', $options);
		if($id){
			$options['id'] = $id;
			if($result !== FALSE){
				return $this->_call_last_function($result, false);
			}else{
				if(!$this->_get_conditions($options)){
					return $this->_call_last_function(array(), false);
				}else{
					$result = $this->main_model->first();
					$this->_cache_process($cache_keys[0], $cache_keys[1], $result, 'SAVE', $options);
					return $this->_call_last_function($result, true);
				}
			}
		}else{
			return null;
		}
	}
	function search_record_paging($options = array(), $limit = false, $offset = 0){
		$this->_call_start_function();
		$options['limit'] = $limit;
		$options['offset'] = $offset;
		$cache_keys = $this->_get_cache_key('cache_search_record_paging', $options);
		$result = $this->_cache_process($cache_keys[0], $cache_keys[1], array(), 'GET', $options);
		if($result !== FALSE){
			return $this->_call_last_function($result, false);
		}else{
			if(!$this->_get_conditions($options)){
				return $this->_call_last_function(array(), false);
			}else{
				$result = $this->main_model->get();
				$this->_cache_process($cache_keys[0], $cache_keys[1], $result, 'SAVE', $options);
				return $this->_call_last_function($result, true);
			}	
		}
	}
	
	function insert_record($data, $options = array()){
		$this->_call_start_function();
		if($this->_get_conditions($options)){
			$insert_id = $this->main_model->insertGetId($data);
			$cache_files = array(
				$this->_get_cache_key('cache_count_all_records', $options, 0),
				$this->_get_cache_key('cache_search_all_records', $options, 0),
				$this->_get_cache_key('cache_search_record_paging', $options, 0),
			);
			$this->_cache_delete($cache_files, $options);
			return $this->_call_last_function($insert_id, true);
		}else{
			return $this->_call_last_function(FALSE, false);
		}
	}
	function update_record($ids, $data, $options = array()){
		$this->_call_start_function();
		if(!is_array($ids) && $ids){
			$ids = array($ids);	
		}
		$options['ids'] = $ids;
		if(is_array($ids) && count($ids) > 0 && $this->_get_conditions($options)){
			$this->main_model->update($data);
			$cache_files = array(
				$this->_get_cache_key('cache_count_all_records', $options, 0),
				$this->_get_cache_key('cache_search_all_records', $options, 0),
				$this->_get_cache_key('cache_search_record_paging', $options, 0),
			);
			foreach($ids as $id){
				$cache_files[] = $this->_get_cache_key('cache_search_record_by_id_object_'.$id, $options, 0);
			}
			$this->_cache_delete($cache_files, $options);
			return $this->_call_last_function($ids, true);
		}else{
			return $this->_call_last_function(FALSE, false);
		}
	}
	function delete_record($ids, $options = array()){
		$this->_call_start_function();
		if(!is_array($ids)){
			if(!$ids){
				$ids = array();
			}else{
				$ids = array($ids);
			}
		}
		$options['ids'] = $ids;
		if($this->_get_conditions($options)){
			$this->main_model->delete();
			$cache_files = array(
				$this->_get_cache_key('cache_count_all_records', $options, 0),
				$this->_get_cache_key('cache_search_all_records', $options, 0),
				$this->_get_cache_key('cache_search_record_paging', $options, 0),
			);
			foreach($ids as $id){
				$cache_files[] = $this->_get_cache_key('cache_search_record_by_id_object_'.$id, $options, 0);
			}
			$this->_cache_delete($cache_files, $options);
			return $this->_call_last_function($ids, true);
		}else{
			return $this->_call_last_function(FALSE, false);
		}
	}
	
	function insert_batch($datas){
		return $this->model_class::insert($datas);
	}
	
	function _get_conditions($options){
		$this->main_model = $this->model_class::query();
		if(isset($options['where_in'])){
			foreach($options['where_in'] as $field_key => $field_vals){
				if(count($field_vals) > 0){
					$this->main_model->whereIn($field_key, $field_vals);
				}
			}
		}
		if(isset($options['ids'])){
			if(is_array($options['ids']) && count($options['ids']) > 0){
				if(count($options['ids']) <= 1){
					$this->main_model->where('id', $options['ids'][0]);
				}else{
					$this->main_model->whereIn('id', $options['ids']);
				}
			}else{
				return FALSE;
			}
		}
		if(isset($options['id'])){
			$this->main_model->where('id', $options['id']);
		}
		if(isset($options['where'])){
			foreach($options['where'] as $field => $val){
				if(is_array($val)){
					$this->main_model->where($field, $val[0], $val[1]);
				}else{
					$this->main_model->where($field, $val);
				}
			}
		}
		if(isset($options['group_by'])){
			foreach($options['group_by'] as $group_by){
				$this->main_model->groupBy($group_by);
			}
		}
		if(isset($options['sort_id'])){
			if($options['sort_id'] === 'asc'){
				$this->main_model->orderBy('id', 'asc');	
			}else{
				$this->main_model->orderBy('id', 'desc');
			}
		}
		if(isset($options['sort_pos'])){
			if($options['sort_pos'] === 'asc'){
				$this->main_model->orderBy('position', 'asc');	
			}else{
				$this->main_model->orderBy('position', 'desc');
			}
		}
		if(isset($options['sort_by'])){
			foreach($options['sort_by'] as $sort_field => $sort_type){
				$this->main_model->orderBy($sort_field, $sort_type);	
			}
		}
		if(isset($options['limit']) && $options['limit']){
			$this->main_model->limit($options['limit']);
			$this->main_model->offset($options['offset']);
		}
		if(isset($options['select']) && $options['select']){
			$this->main_model->select($options['select']);	
		}
		if(isset($options['select_raw']) && $options['select_raw']){
			$this->main_model->selectRaw($options['select_raw']);	
		}
		if(isset($options['order_raw'])){
			$this->main_model->orderByRaw($options['order_raw']);
		}
		if(isset($options['fulltext'])){
			foreach($options['fulltext'] as $fulltext_key => $fulltext_val){
				$this->main_model->whereRaw('MATCH('.$fulltext_key.') AGAINST(? IN BOOLEAN MODE)', [$fulltext_val]);
			}
		}
		return TRUE;
	}
	
	function _get_cache_key($slug, $options, $get = true){
		$cache_file = $slug.'_'.$this->getTable().'_'.md5(serialize($this->_sort_options($options)));
		$cache_file_group = $slug.'_'.$this->getTable().'_group';
		if($get){
			return array($cache_file_group, $cache_file);
		}else{
			return $cache_file_group;
		}
	}
	function _cache_process($cache_file_group, $cache_file, $data, $action, $options){
		if(isset($options['nocache']) || config('app.product_cache_db') === FALSE){
			return FALSE;
		}else{
			$cache_group_time = 7*24*60*60;
			$cache_time = 24*60*60;
			$cur_second = strtotime(date('Y-m-d H:i:s'));
			if($action == 'GET'){
				$cache_group_data = Cache::get($cache_file_group);
				$cache_file_data = Cache::get($cache_file);
				if(!empty($cache_group_data) && !empty($cache_file_data)){
					if($cache_group_data->start > $cache_file_data->start){
						return FALSE;
					}
				}
				return (!empty($cache_file_data)) ? $cache_file_data->data : FALSE;
			}else if($action == 'SAVE'){
				Cache::put($cache_file, (object)array('start' => $cur_second, 'data' => $data), $cache_time);
			}else if($action == 'DELETE'){
				Cache::put($cache_file_group, (object)array('start' => $cur_second, 'data' => 'yes'), $cache_group_time);
			}
		}
	}
	
	function _cache_delete($cache_files, $options){
		foreach($cache_files as $cache_file){
			$this->_cache_process($cache_file, '', array(), 'DELETE', $options);
		}
	}
	function _sort_options($options){
		if(is_array($options)){
			asort($options);
			foreach($options as $option_key => $option_val){
				if(is_array($option_val)){
					asort($option_val);
					$options[$option_key] = $option_val;
				}
			}	
		}
		return $options;	
	}
	function _call_start_function(){
		if(config('app.enable_log_query')){
			\DB::enableQueryLog();
		}
		$this->start_query_time = round(microtime(true) * 1000);
	}
	function _call_last_function($result, $do_query){
		if($do_query && config('app.enable_log_query')){
			$query = $this->_getQuery($this->main_model);
			$end_query_time = round(microtime(true) * 1000);
			$query = trim(preg_replace('/\s+/', ' ', $query));
			$query = date('Y-m-d H:i:s').' | '.($end_query_time - $this->start_query_time).' | '.$query.'|'.url()->current()."\n";
			Log::info($query);
			//echo $query.PHP_EOL;
		}
		return $result;
	}
	function _getQuery($sql){
        $query = str_replace(array('?'), array('\'%s\''), $sql->toSql());
        $query = vsprintf($query, $sql->getBindings());     
        return $query;
	}
}