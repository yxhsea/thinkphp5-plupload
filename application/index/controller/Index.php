<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Index extends Controller{
    public function index(){
        $rootUrl = $this->request->root(true); //ROOT域名
        $rootUrl = explode('index.php',$rootUrl)[0];
        //模板资源变量分配
        foreach (config('TMPL_PARSE_STRING') as $key => $value) {
            $this->view->assign('_'.$key,$rootUrl.$value);
        }
        return $this->fetch();
    }

    //图片上传方法
    public function upload_images(){
        if($this->request->isPost()){
            //接收参数
            $images = $this->request->file('file');

            //计算md5和sha1散列值，TODO::作用避免文件重复上传
            $md5 = $images->hash('md5');
            $sha1= $images->hash('sha1');

            //判断图片文件是否已经上传
            $img = Db::name('picture')->where(['md5'=>$md5,'sha1'=>$sha1])->find();
            if(!empty($img)){
                return json(['status'=>1,'msg'=>'上传成功','data'=>['img_id'=>$img['id'],'img_url'=>$this->request->root(true).'/'.$img['path']]]);
            }else{
                // 移动到框架应用根目录/public/uploads/picture/目录下
                $imgPath = 'public' . DS . 'uploads' . DS . 'picture';
                $info = $images->move(ROOT_PATH . $imgPath);
                $path = 'public/uploads/picture/'.date('Ymd',time()).'/'.$info->getFilename();
                $data = [
                    'path' => $path ,
                    'md5' => $md5 ,
                    'sha1' => $sha1 ,
                    'status' => 1 ,
                    'create_time' => time() ,
                ];
                if($img_id=Db::name('picture')->insertGetId($data)){
                    return json(['status'=>1,'msg'=>'上传成功','data'=>['img_id'=>$img_id,'img_url'=>$this->request->root(true).'/'.$path]]);
                }else{
                    return json(['status'=>0,'msg'=>'写入数据库失败']);
                }
            }
        }else{
            return ['status'=>0,'msg'=>'非法请求!'];
        }
    }
}
