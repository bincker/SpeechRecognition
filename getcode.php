<?php
class getcode{
	private $c;
	private $sid;
	private $file;
	private $now;
	function __construct($sid,$file,$now,$c){
			$this->sid=$sid;
			$this->file=$file;
			$this->now=$now;
			$this->c=$c;
	}
    /**
     *这里接收各项参数
     */
    public function index()
    {
		umask(0007);
        chdir('/wav2txt/sample');
        $order = './sample v2.hivoice.cn 80 /wav/' . $this->file . ' /txt/'.$this->file.'.txt';
        exec($order, $output, $status);
        if ($status == 0) {
            $cont = file_get_contents('/txt/'.$this->file.'.txt');
            $cont = explode("\n", $cont);
            $text = str_replace('/wav/' . $this->file . '	pcm16k	', '', $cont[count($cont) - 2]);
        } else {
            $text = '程序执行出错，代码' . $status;
        }
        $god=$this->GetOrder($text,$this->now);
        return json_encode(array('VoiceFileName' =>$god['sec'],'CommandType'=>$god['ord'], 'SessionId'=>$this->sid));
    }

    /**
     * 新建或修改Json文件
     */
    private function addjson($jsonname){
        if (file_exists($jsonname)) {
            $data=$this->getjson($jsonname);
            $data['count']=$data['count']+1;
        }else {
            $data['count']=0;
        }
        $json_string = json_encode($data);
        file_put_contents($jsonname, $json_string);
    }

    /**
     * 读取Json文件
     */
    private function getjson($jsname){
        if (file_exists($jsname)) {
            $count = json_decode(file_get_contents($jsname), true);
        }else{
            $count['count']=0;
        }
        return $count;
    }

    /**
     * 删除Json文件
     */
    private function deljson($jsonname){
        if(file_exists($jsonname)){
            unlink($jsonname);
        }
    }

    /**
     *这里查询数据库得到该返回的语音文件,现简单写死，供演示使用
     */
    private function Select($pass,$bool){
        $jsname=$this->sid.$this->now.'.json';
        $ord=1;
        if($pass=='1') {
            if($bool==0){
                $sec=3;
            }elseif ($bool==1){
                $this->addjson($jsname);
                $sec=2;
            }else {
                if($this->getjson($jsname)['count']<$this->c){
                    $sec=$pass;
                    $this->addjson($jsname);
                }else{
                    $sec=8;
                    $ord=0;
                    $this->deljson($jsname);
                }
            }
        }elseif($pass=='2') {
            if($bool==0){
                $sec=5;
            }elseif ($bool==1){
                $sec=4;
            }else{
                if($this->getjson($jsname)['count']<$this->c){
                    $sec=$pass;
                    $this->addjson($jsname);
                }else{
                    $sec=8;
                    $ord=0;
                    $this->deljson($jsname);
                }
            }
        }elseif($pass=='3') {
            if ($bool == 0) {
                $sec = 7;
            } elseif ($bool == 1) {
                $sec = 6;
            } else{
                if($this->getjson($jsname)['count']<$this->c){
                    $sec=$pass;
                    $this->addjson($jsname);
                }else{
                    $sec=8;
                    $ord=0;
                    $this->deljson($jsname);
                }
            }
        }else{
            $ord=0;
            $sec=8;
        }
        if($pass>3){
            $sec = 8;
            $ord=0;
        }
        return array('sec'=>$sec,'ord'=>$ord);
    }

    /**
     * @param $cont         //语音转写产生的文本内容
     * @param $pass         //上一个节点的唯一标识
     * @return array|int    //返回下个要播的节点及指令（1表示播放并录音提交识别，0表示播放后挂机）
     */
    private function GetOrder($cont, $pass){
        $code=2;
        if(strpos($cont,"不是")){
            $code=0;
        }
        if(strpos($cont,"是")){
            $code=1;
        }
        $code=$this->Select($pass,$code);
        return $code;
    }
    /**
     *这里测试一下程序返回结果是否正常
     */
    public function test()
    {
        echo $this->index();
    }
}

/**
 * @param $sid   //当前语音识别进程的唯一标识
 * @param $file  //要处理的语音文件
 * @param $now   //当前节点的唯一标识，用以配合语音转写结果，得到下一个节点
 * @param $c     //控制无法识别时的重拨次数，为$c+1次
 */
function CreateObj($sid, $file, $now, $c){
	if($sid && $file && $now && $c){
		$obj=new getcode($sid,$file,$now,3);
		$obj->test();
	}else{
		echo '缺少参数'."\r\n";
	}
}

/** 
 * 使用CreateObj()函数控制对象创建
 */
$jsonstr=file_get_contents('php://input');
//$jsonstr='{"RoutineId":2,"VoiceFileName":"test.wav","SessionId":"135"}';
$params=json_decode($jsonstr , true);
//echo $jsonstr;
//var_dump($params['SessionId']);
CreateObj($sid=$params['SessionId'],$file=$params['VoiceFileName'],$now=$params['RoutineId'],$c=3);
?>
