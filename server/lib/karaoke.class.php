<?php
/**
 * Main Karaoke class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Karaoke extends AjaxResponse 
{
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
    
    public function __construct(){
        parent::__construct();
    }
    
    public function createLink(){
        
        preg_match("/\/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);
        
        $media_id = $tmp_arr[1];

        $master = new KaraokeMaster();
        
        try {
            $res = $master->play($media_id);
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }
        
        var_dump($res);
        
        return $res;
    }
    
    private function getData(){
        
        $offset = $this->page * MAX_PAGE_ITEMS;
        
        $where = array('status' => 1);
        
        if (!$this->stb->isModerator()){
            $where['accessed'] = 1;
        }
        
        $like = array();
        
        if (@$_REQUEST['abc'] && @$_REQUEST['abc'] !== '*'){
            
            $letter = $_REQUEST['abc'];
            
            if (@$_REQUEST['sortby'] == 'name'){
                $like = array('karaoke.name' => $letter.'%');
            }else{
                $like = array('karaoke.singer' => $letter.'%');
            }
        }
        
        if (@$_REQUEST['search']){
            
            $letters = $_REQUEST['search'];
            
            $search['karaoke.name']   = '%'.$letters.'%';
            $search['karaoke.singer'] = '%'.$letters.'%';
        }
        
        return $this->db
                        ->from('karaoke')
                        ->where($where)
                        ->like($like)
                        ->like($search, 'OR ')
                        ->limit(MAX_PAGE_ITEMS, $offset);
    }
    
    public function getOrderedList(){
     
        $result = $this->getData();
        
        if (@$_REQUEST['sortby']){
            $sortby = $_REQUEST['sortby'];
            
            if ($sortby == 'name'){
                $result = $result->orderby('karaoke.name');
            }elseif ($sortby == 'singer'){
                $result = $result->orderby('karaoke.singer');
            }
            
        }else{
            $result = $result->orderby('karaoke.singer');
        }
        
        $this->setResponseData($result);
        
        return $this->getResponse('prepareData');
    }
    
    public function prepareData(){
        
        for ($i = 0; $i < count($this->response['data']); $i++){
            
            $this->response['data'][$i]['cmd'] = '/media/'.$this->response['data'][$i]['id'].'.mpg';
        }
        
        return $this->response;
    }
    
    public function getAbc(){
        
        $abc = array();
        
        foreach ($this->abc as $item){
            $abc[] = array(
                        'id'    => $item,
                        'title' => $item
                     );
        }
        
        return $abc;
    }
}

?>