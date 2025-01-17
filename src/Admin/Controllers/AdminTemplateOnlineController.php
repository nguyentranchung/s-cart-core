<?php
namespace SCart\Core\Admin\Controllers;

use App\Http\Controllers\RootAdminController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AdminTemplateOnlineController extends RootAdminController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        $arrTemplateLibrary = [];
        $resultItems = '';
        $htmlPaging = '';
        $sc_version = config('s-cart.core');
        $filter_free = request('filter_free', 0);
        $filter_type = request('filter_type', '');
        $filter_keyword = request('filter_keyword', '');

        $page = request('page') ?? 1;
        $url = config('s-cart.api_link').'/templates/?page[size]=20&page[number]='.$page;
        $url .='&version='.$sc_version;
        $url .='&filter_free='.$filter_free;
        $url .='&filter_type='.$filter_type;
        $url .='&filter_keyword='.$filter_keyword;
        $ch            = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $dataApi   = curl_exec($ch);
        curl_close($ch);
        $dataApi = json_decode($dataApi, true);
        if (!empty($dataApi['data'])) {
            foreach ($dataApi['data'] as $key => $data) {
                $arrTemplateLibrary[] = [
                    'sku' => $data['sku'] ?? '',
                    'key' => $data['key'] ?? '',
                    'name' => $data['name'] ?? '',
                    'description' => $data['description'] ?? '',
                    'image' => $data['image'] ?? '',
                    'image_demo' => $data['image_demo'] ?? '',
                    'path' => $data['path'] ?? '',
                    'file' => $data['file'] ?? '',
                    'version' => $data['version'] ?? '',
                    'scart_version' => $data['scart_version'] ?? '',
                    'price' => $data['price'] ?? 0,
                    'price_final' => $data['price_final'] ?? 0,
                    'price_promotion' => $data['price_promotion'] ?? 0,
                    'is_free' => $data['is_free'] ?? 0,
                    'download' => $data['download'] ?? 0,
                    'username' =>  $data['username'] ?? '',
                    'times' =>  $data['times'] ?? 0,
                    'points' =>  $data['points'] ?? 0,
                    'rated' =>  $data['rated'] ?? 0,
                    'date' =>  $data['date'] ?? '',
                    'link' =>  $data['link'] ?? '',
                ];
            }
            $resultItems = sc_language_render('product.admin.result_item', ['item_from' => $dataApi['from'] ?? 0, 'item_to' => $dataApi['to']??0, 'total' =>  $dataApi['total'] ?? 0]);
            $htmlPaging .= '<ul class="pagination pagination-sm no-margin pull-right">';
            if ($dataApi['current_page'] > 1) {
                $htmlPaging .= '<li class="page-item"><a class="page-link pjax-container" href="'.sc_route_admin('admin_template_online').'?page='.($dataApi['current_page'] - 1).'" rel="prev">«</a></li>';
            } else {
                for ($i = 1; $i < $dataApi['last_page']; $i++) {
                    if ($dataApi['current_page'] == $i) {
                        $htmlPaging .= '<li class="page-item active"><span class="page-link pjax-container">'.$i.'</span></li>';
                    } else {
                        $htmlPaging .= '<li class="page-item"><a class="page-link" href="'.sc_route_admin('admin_template_online').'?page='.$i.'">'.$i.'</a></li>';
                    }
                }
            }
            if ($dataApi['current_page'] < $dataApi['last_page']) {
                $htmlPaging .= '<li class="page-item"><a class="page-link pjax-container" href="'.sc_route_admin('admin_template_online').'?page='.($dataApi['current_page'] + 1).'" rel="next">»</a></li>';
            }
            $htmlPaging .= '</ul>';
        }
    
    
        $title = sc_language_render('admin.template.list');
    
        return view($this->templatePathAdmin.'screen.template_online')->with(
            [
                    "title" => $title,
                    "arrTemplateLocal" => sc_get_all_template(),
                    "arrTemplateLibrary" => $arrTemplateLibrary,
                    "filter_keyword" => $filter_keyword ?? '',
                    "filter_type" => $filter_type ?? '',
                    "filter_free" => $filter_free ?? '',
                    "resultItems" => $resultItems,
                    "htmlPaging" => $htmlPaging,
                    "dataApi" => $dataApi,
                ]
        );
    }

    public function install()
    {
        $response = ['error' => 0, 'msg' => 'Install success'];
        $key = request('key');
        $key = str_replace('.', '-', $key);
        $path = request('path');
        try {
            $data = file_get_contents($path);
            $pathTmp = $key.'_'.time();
            $fileTmp = $pathTmp.'.zip';
            Storage::disk('tmp')->put($pathTmp.'/'.$fileTmp, $data);
        } catch (\Exception $e) {
            $response = ['error' => 1, 'msg' => $e->getMessage()];
        }

        $unzip = sc_unzip(storage_path('tmp/'.$pathTmp.'/'.$fileTmp), storage_path('tmp/'.$pathTmp));
        if ($unzip) {
            $checkConfig = glob(storage_path('tmp/'.$pathTmp) . '/*/src/config.json');
            if (!$checkConfig) {
                return $response = ['error' => 1, 'msg' => 'Cannot found file config.json'];
            }
            $folderName = explode('/src', $checkConfig[0]);
            $folderName = explode('/', $folderName[0]);
            $folderName = end($folderName);
            
            File::copyDirectory(storage_path('tmp/'.$pathTmp.'/'.$folderName.'/public'), public_path('templates/'.$key));
            File::copyDirectory(storage_path('tmp/'.$pathTmp.'/'.$folderName.'/src'), resource_path('views/templates/'.$key));
            File::deleteDirectory(storage_path('tmp/'.$pathTmp));
        } else {
            $response = ['error' => 1, 'msg' => 'error while unzip'];
        }
        return response()->json($response);
    }
}
