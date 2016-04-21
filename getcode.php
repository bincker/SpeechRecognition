<?php
class getcode{
	private $c; //指定允许无法识别的次数，及重播次数
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
        return array('fid' =>$god['sec'],'ord'=>$god['ord'], 'sid'=>$this->sid, 'status' => $status);
    }
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
    private function getjson($jsname){
        if (file_exists($jsname)) {
            $count = json_decode(file_get_contents($jsname), true);
        }else{
            $count['count']=0;
        }
        return $count;
    }
    private function deljson($jsonname){
        if(file_exists($jsonname)){
            unlink($jsonname);
        }
    }

    /**
     *这里查询数据库得到该返回的语音文件
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
    private function GetOrder($cont,$pass){
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
        var_dump($this->index());
    }
}
function createobj($sid,$file,$now,$c){
	if($sid && $file && $now && $c){
		$obj=new getcode($_GET['sid'],$_GET['file'],$_GET['now'],3);
		$obj->test();
	}else{
		echo '缺少参数'."\r\n";
	}
}
createobj($sid=$_GET['sid'],$file=$_GET['file'],$now=$_GET['now'],$c=3);
?>
