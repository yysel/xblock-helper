<?php
/**
 * Created by PhpStorm.
 * User: jim
 * Date: 20-4-4
 * Time: 下午4:44
 */

namespace XBlock\Helper;


use XBlock\Helper\FileService\FileUpload;

class Helper
{
    static public function useUploadRoutes($option = [])
    {
        app()->router->group(['prefix' => 'api/file', 'middleware' => 'auth'], function ($route) use ($option) {
            $route->post('upload', function (FileUpload $upload) use ($option) {
                if (($data = $upload->save()) == false) return message(false, '文件上传失败');
                return message(true)->data($data);

            });
            $route->post('remove', function (FileUpload $upload) {
                $upload->remove();
            });
        });
    }


}
