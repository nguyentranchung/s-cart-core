<?php
namespace SCart\Core\Admin\Controllers;

use App\Http\Controllers\RootAdminController;
use SCart\Core\Front\Models\ShopLanguage;
use Validator;
use SCart\Core\Admin\Models\AdminCategory;

class AdminCategoryController extends RootAdminController
{
    public $languages;

    public function __construct()
    {
        parent::__construct();
        $this->languages = ShopLanguage::getListActive();
    }

    public function index()
    {
        $categoriesTitle =  AdminCategory::getListTitleAdmin();
        $data = [
            'title'         => sc_language_render('admin.category.list'),
            'subTitle'      => '',
            'icon'          => 'fa fa-indent',
            'urlDeleteItem' => sc_route_admin('admin_category.delete'),
            'removeList'    => 1, // 1 - Enable function delete list item
            'buttonRefresh' => 1, // 1 - Enable button refresh
            'buttonSort'    => 1, // 1 - Enable button sort
            'css'           => '',
            'js'            => '',
        ];
        //Process add content
        $data['menuRight']    = sc_config_group('menuRight', \Request::route()->getName());
        $data['menuLeft']     = sc_config_group('menuLeft', \Request::route()->getName());
        $data['topMenuRight'] = sc_config_group('topMenuRight', \Request::route()->getName());
        $data['topMenuLeft']  = sc_config_group('topMenuLeft', \Request::route()->getName());
        $data['blockBottom']  = sc_config_group('blockBottom', \Request::route()->getName());

        $listTh = [
            'id'     => 'ID',
            'image'  => sc_language_render('admin.category.image'),
            'title'  => sc_language_render('admin.category.title'),
            'parent' => sc_language_render('admin.category.parent'),
            'top'    => sc_language_render('admin.category.top'),
            'status' => sc_language_render('admin.category.status'),
            'sort'   => sc_language_render('admin.category.sort'),
        ];

        if (sc_config_global('MultiStorePro') && session('adminStoreId') == SC_ID_ROOT) {
            // Only show store info if store is root
            $listTh['shop_store'] = sc_language_render('front.store_list');
        }

        $listTh['action'] = sc_language_render('action.title');

        $sort_order = sc_clean(request('sort_order') ?? 'id_desc');
        $keyword    = sc_clean(request('keyword') ?? '');
        $arrSort = [
            'id__desc' => sc_language_render('filter_sort.id_desc'),
            'id__asc' => sc_language_render('filter_sort.id_asc'),
            'title__desc' => sc_language_render('filter_sort.title_desc'),
            'title__asc' => sc_language_render('filter_sort.title_asc'),
        ];
        
        $dataSearch = [
            'keyword'    => $keyword,
            'sort_order' => $sort_order,
            'arrSort'    => $arrSort,
        ];
        $dataTmp = (new AdminCategory)->getCategoryListAdmin($dataSearch);
        
        if (sc_config_global('MultiStorePro') && session('adminStoreId') == SC_ID_ROOT) {
            // Only show store info if store is root
            $arrId = $dataTmp->pluck('id')->toArray();
            if (function_exists('sc_get_list_store_of_category')) {
                $dataStores = sc_get_list_store_of_category($arrId);
            } else {
                $dataStores = [];
            }
        }

        $dataTr = [];
        foreach ($dataTmp as $key => $row) {
            $dataMap = [
                'id' => $row['id'],
                'image' => sc_image_render($row->getThumb(), '50px', '50px', $row['title']),
                'title' => $row['title'],
                'parent' => $row['parent'] ? ($categoriesTitle[$row['parent']] ?? '') : 'ROOT',
                'top' => $row['top'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'status' => $row['status'] ? '<span class="badge badge-success">ON</span>' : '<span class="badge badge-danger">OFF</span>',
                'sort' => $row['sort'],
            ];

            if (sc_config_global('MultiStorePro') && session('adminStoreId') == SC_ID_ROOT) {
                // Only show store info if store is root
                if (!empty($dataStores[$row['id']])) {
                    $storeTmp = $dataStores[$row['id']]->pluck('code', 'id')->toArray();
                    $storeTmp = array_map(function ($code) {
                        return '<a target=_new href="'.sc_get_domain_from_code($code).'">'.$code.'</a>';
                    }, $storeTmp);
                    $dataMap['shop_store'] = '<i class="nav-icon fab fa-shopify"></i> '.implode('<br><i class="nav-icon fab fa-shopify"></i> ', $storeTmp);
                }
            }
            $dataMap['action'] = '
            <a href="' . sc_route_admin('admin_category.edit', ['id' => $row['id']]) . '"><span title="' .sc_language_render('action.edit') . '" type="button" class="btn btn-flat btn-primary"><i class="fa fa-edit"></i></span></a>&nbsp;
            <span onclick="deleteItem(' . $row['id'] . ');"  title="' . sc_language_render('action.delete') . '" class="btn btn-flat btn-danger"><i class="fas fa-trash-alt"></i></span>';
            $dataTr[] = $dataMap;
        }

        $data['listTh'] = $listTh;
        $data['dataTr'] = $dataTr;
        $data['pagination'] = $dataTmp->appends(request()->except(['_token', '_pjax']))->links($this->templatePathAdmin.'component.pagination');
        $data['resultItems'] = sc_language_render('admin.result_item', ['item_from' => $dataTmp->firstItem(), 'item_to' => $dataTmp->lastItem(), 'total' =>  $dataTmp->total()]);


        //menuRight
        $data['menuRight'][] = '<a href="' . sc_route_admin('admin_category.create') . '" class="btn  btn-success  btn-flat" title="New" id="button_create_new">
        <i class="fa fa-plus" title="'.sc_language_render('action.add_new').'"></i>
        </a>';
        //=menuRight

        //menuSort
        $optionSort = '';
        foreach ($arrSort as $key => $sort) {
            $optionSort .= '<option  ' . (($sort_order == $key) ? "selected" : "") . ' value="' . $key . '">' . $sort . '</option>';
        }

        $data['urlSort'] = sc_route_admin('admin_category.index', request()->except(['_token', '_pjax', 'sort_order']));
        $data['optionSort'] = $optionSort;
        //=menuSort

        //menuSearch
        $data['topMenuRight'][] = '
                <form action="' . sc_route_admin('admin_category.index') . '" id="button_search">
                <div class="input-group input-group" style="width: 350px;">
                    <input type="text" name="keyword" class="form-control rounded-0 float-right" placeholder="' . sc_language_render('search.placeholder') . '" value="' . $keyword . '">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                </form>';
        //=menuSearch

        return view($this->templatePathAdmin.'screen.list')
            ->with($data);
    }

    /*
     * Form create new item in admin
     * @return [type] [description]
     */
    public function create()
    {
        $data = [
            'title' => sc_language_render('admin.category.add_new_title'),
            'subTitle' => '',
            'title_description' => sc_language_render('admin.category.add_new_des'),
            'icon' => 'fa fa-plus',
            'languages' => $this->languages,
            'category' => [],
            'categories' => (new AdminCategory)->getTreeCategoriesAdmin(),
            'url_action' => sc_route_admin('admin_category.create'),
        ];

        return view($this->templatePathAdmin.'screen.category')
            ->with($data);
    }

    /*
     * Post create new item in admin
     * @return [type] [description]
     */
    public function postCreate()
    {
        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make(
            $data,
            [
                'parent'                 => 'required',
                'sort'                   => 'numeric|min:0',
                'alias'                  => 'required|unique:"'.AdminCategory::class.'",alias|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100',
                'descriptions.*.title'   => 'required|string|max:200',
                'descriptions.*.keyword' => 'nullable|string|max:200',
                'descriptions.*.description' => 'nullable|string|max:300',
            ],
            [
                'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('admin.category.title')]),
                'alias.regex' => sc_language_render('admin.category.alias_validate'),
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        $dataInsert = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'parent'   => (int) $data['parent'],
            'top'      => !empty($data['top']) ? 1 : 0,
            'status'   => !empty($data['status']) ? 1 : 0,
            'sort'     => (int) $data['sort'],
        ];
        $category = AdminCategory::createCategoryAdmin($dataInsert);
        $dataDes = [];
        $languages = $this->languages;
        foreach ($languages as $code => $value) {
            $dataDes[] = [
                'category_id' => $category->id,
                'lang'        => $code,
                'title'       => $data['descriptions'][$code]['title'],
                'keyword'     => $data['descriptions'][$code]['keyword'],
                'description' => $data['descriptions'][$code]['description'],
            ];
        }
        AdminCategory::insertDescriptionAdmin($dataDes);

        if (sc_config_global('MultiStorePro')) {
            // If multi-store
            $shopStore        = $data['shop_store'] ?? [];
            $category->stores()->detach();
            if ($shopStore) {
                $category->stores()->attach($shopStore);
            }
        }

        sc_clear_cache('cache_category');

        return redirect()->route('admin_category.index')->with('success', sc_language_render('action.create_success'));
    }

    /*
     * Form edit
     */
    public function edit($id)
    {
        $category = AdminCategory::getCategoryAdmin($id);

        if (!$category) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data = [
            'title'             =>sc_language_render('action.edit'),
            'subTitle'          => '',
            'title_description' => '',
            'icon'              => 'fa fa-edit',
            'languages'         => $this->languages,
            'category'          => $category,
            'categories'        => (new AdminCategory)->getTreeCategoriesAdmin(),
            'url_action'        => sc_route_admin('admin_category.edit', ['id' => $category['id']]),
        ];
        return view($this->templatePathAdmin.'screen.category')
            ->with($data);
    }

    /*
     * update status
     */
    public function postEdit($id)
    {
        $category = AdminCategory::getCategoryAdmin($id);
        if (!$category) {
            return redirect()->route('admin.data_not_found')->with(['url' => url()->full()]);
        }

        $data = request()->all();

        $langFirst = array_key_first(sc_language_all()->toArray()); //get first code language active
        $data['alias'] = !empty($data['alias'])?$data['alias']:$data['descriptions'][$langFirst]['title'];
        $data['alias'] = sc_word_format_url($data['alias']);
        $data['alias'] = sc_word_limit($data['alias'], 100);

        $validator = Validator::make(
            $data,
            [
            'parent'                 => 'required',
            'sort'                   => 'numeric|min:0',
            'alias'                  => 'required|regex:/(^([0-9A-Za-z\-_]+)$)/|string|max:100|unique:"'.AdminCategory::class.'",alias,' . $id . '',
            'descriptions.*.title'   => 'required|string|max:200',
            'descriptions.*.keyword' => 'nullable|string|max:200',
            'descriptions.*.description' => 'nullable|string|max:300',
            ],
            [
                'descriptions.*.title.required' => sc_language_render('validation.required', ['attribute' => sc_language_render('admin.category.title')]),
                'alias.regex'                   => sc_language_render('admin.category.alias_validate'),
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($data);
        }
        //Edit
        $dataUpdate = [
            'image'    => $data['image'],
            'alias'    => $data['alias'],
            'parent'   => $data['parent'],
            'sort'     => $data['sort'],
            'top'      => empty($data['top']) ? 0 : 1,
            'status'   => empty($data['status']) ? 0 : 1,
        ];

        $category->update($dataUpdate);
        $category->descriptions()->delete();
        $dataDes = [];
        foreach ($data['descriptions'] as $code => $row) {
            $dataDes[] = [
                'category_id' => $id,
                'lang'        => $code,
                'title'       => $row['title'],
                'keyword'     => $row['keyword'],
                'description' => $row['description'],
            ];
        }
        AdminCategory::insertDescriptionAdmin($dataDes);

        if (sc_config_global('MultiStorePro')) {
            // If multi-store
            $shopStore        = $data['shop_store'] ?? [];
            $category->stores()->detach();
            if ($shopStore) {
                $category->stores()->attach($shopStore);
            }
        }
        
        sc_clear_cache('cache_category');

        //
        return redirect()->route('admin_category.index')->with('success', sc_language_render('action.edit_success'));
    }

    /*
    Delete list Item
    Need mothod destroy to boot deleting in model
    */
    public function deleteList()
    {
        if (!request()->ajax()) {
            return response()->json(['error' => 1, 'msg' => sc_language_render('admin.method_not_allow')]);
        } else {
            $ids = request('ids');
            $arrID = explode(',', $ids);
            $arrDontPermission = [];
            foreach ($arrID as $key => $id) {
                if (!$this->checkPermisisonItem($id)) {
                    $arrDontPermission[] = $id;
                }
            }
            if (count($arrDontPermission)) {
                return response()->json(['error' => 1, 'msg' => sc_language_render('admin.remove_dont_permisison') . ': ' . json_encode($arrDontPermission)]);
            }
            AdminCategory::destroy($arrID);
            sc_clear_cache('cache_category');
            return response()->json(['error' => 0, 'msg' => '']);
        }
    }

    /**
     * Check permisison item
     */
    public function checkPermisisonItem($id)
    {
        return AdminCategory::getCategoryAdmin($id);
    }
}
