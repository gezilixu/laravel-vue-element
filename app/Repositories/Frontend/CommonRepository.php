<?php
namespace App\Repositories\Frontend;

use App\Mail\RegisterOrder;
use Illuminate\Support\Facades\Mail;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

class CommonRepository extends BaseRepository
{

    /**
     * 保存图片
     * @param  Array $input 上传的文件
     * @return Array
     */
    public function uploadImage($input)
    {
        $oldImagesName      = $input->getClientOriginalName();
        $fileTmpPath        = $input->getLinkTarget(); // 要上传文件的本地路径
        $imageExtensionName = $input->getClientOriginalExtension();
        $imageSize          = $input->getSize() / 1024; // 单位为KB
        if (!in_array(strtolower($imageExtensionName), ['jpeg', 'jpg', 'gif', 'gpeg', 'png'])) {
            return [
                'status'  => Parent::ERROR_STATUS,
                'data'    => [],
                'message' => '请上传正确的图片',
            ];
        }
        if ($imageSize > config('blog.pictureSize')) {
            return [
                'status'  => Parent::ERROR_STATUS,
                'data'    => [],
                'message' => '上传的图片不得大于500KB',
            ];
        }
        //七牛上传图片
        $auth   = new Auth(config('blog.qiniuAccessKey'), config('blog.qiniuSecretKey'));
        $bucket = config('blog.qiniuImageBucket');
        // 生成上传Token
        $token = $auth->uploadToken($bucket);
        // 构建 UploadManager 对象
        $uploadMgr = new UploadManager();
        // 上传到七牛后保存的文件名
        $newImagesName   = md5(time()) . random_int(5, 5) . "." . $imageExtensionName;
        list($ret, $err) = $uploadMgr->putFile($token, $newImagesName, $fileTmpPath);
        if ($err !== null) {
            return [
                'status'  => Parent::ERROR_STATUS,
                'data'    => [],
                'message' => '头像上传失败',
            ];
        }
        $ret['faceUrl'] = config('blog.qiniuBucketUrl') . '/' . $ret['key'];
        return [
            'status'  => Parent::SUCCESS_STATUS,
            'data'    => $ret,
            'message' => '头像上传成功',
        ];
    }

    public function sendEmail($input)
    {
        return true;
        $mailMessage = (new RegisterOrder($mailData));
        Mail::to("292304400@qq.com")->queue($mailMessage);
    }
}
