<?php

namespace SCart\Core\Admin\Models;

use SCart\Core\Front\Models\ShopNews;
use Cache;
use SCart\Core\Front\Models\ShopNewsDescription;
use SCart\Core\Front\Models\ShopNewsStore;

class AdminNews extends ShopNews
{
    protected static $getListTitleAdmin = null;
    protected static $getListNewsGroupByParentAdmin = null;
    /**
     * Get news detail in admin
     *
     * @param   [type]  $id  [$id description]
     *
     * @return  [type]       [return description]
     */
    public static function getNewsAdmin($id)
    {
        $data = self::where('id', $id);
        if (sc_config_global('MultiVendorPro')) {
            if (session('adminStoreId') != SC_ID_ROOT) {
                $tableNewsStore = (new ShopNewsStore)->getTable();
                $tableNews = (new ShopNews)->getTable();
                $data = $data->leftJoin($tableNewsStore, $tableNewsStore . '.news_id', $tableNews . '.id');
                $data = $data->where($tableNewsStore . '.store_id', session('adminStoreId'));
            }
        }
        $data = $data->first();
        return $data;
    }

    /**
     * Get list news in admin
     *
     * @param   [array]  $dataSearch  [$dataSearch description]
     *
     * @return  [type]               [return description]
     */
    public static function getNewsListAdmin(array $dataSearch)
    {
        $keyword          = $dataSearch['keyword'] ?? '';
        $sort_order       = $dataSearch['sort_order'] ?? '';
        $arrSort          = $dataSearch['arrSort'] ?? '';
        $tableDescription = (new ShopNewsDescription)->getTable();
        $tableNews     = (new ShopNews)->getTable();

        $newsList = (new ShopNews)
            ->leftJoin($tableDescription, $tableDescription . '.news_id', $tableNews . '.id')
            ->where($tableDescription . '.lang', sc_get_locale());

        $tableNews = (new ShopNews)->getTable();
        if (sc_config_global('MultiVendorPro')) {
            if (session('adminStoreId') != SC_ID_ROOT) {
                $tableNewsStore = (new ShopNewsStore)->getTable();
                $newsList = $newsList->leftJoin($tableNewsStore, $tableNewsStore . '.news_id', $tableNews . '.id');
                $newsList = $newsList->where($tableNewsStore . '.store_id', session('adminStoreId'));
            }
        }

        if ($keyword) {
            $newsList = $newsList->where(function ($sql) use ($tableDescription, $keyword) {
                $sql->where($tableDescription . '.title', 'like', '%' . $keyword . '%');
            });
        }

        if ($sort_order && array_key_exists($sort_order, $arrSort)) {
            $field = explode('__', $sort_order)[0];
            $sort_field = explode('__', $sort_order)[1];
            $newsList = $newsList->orderBy($field, $sort_field);
        } else {
            $newsList = $newsList->orderBy($tableNews.'.id', 'desc');
        }
        $newsList = $newsList->paginate(20);

        return $newsList;
    }


    /**
     * Get array title news
     * user for admin
     *
     * @return  [type]  [return description]
     */
    public static function getListTitleAdmin()
    {
        $tableDescription = (new ShopNewsDescription)->getTable();
        $table = (new AdminNews)->getTable();
        if (sc_config_global('cache_status') && sc_config_global('cache_news')) {
            if (!Cache::has(session('adminStoreId').'_cache_news_'.sc_get_locale())) {
                if (self::$getListTitleAdmin === null) {
                    $data = self::join($tableDescription, $tableDescription.'.news_id', $table.'.id')
                    ->where('lang', sc_get_locale());
                    if (sc_config_global('MultiVendorPro')) {
                        if (session('adminStoreId') != SC_ID_ROOT) {
                            $tableNewsStore = (new ShopNewsStore)->getTable();
                            $data = $data->leftJoin($tableNewsStore, $tableNewsStore . '.news_id', $table . '.id');
                            $data = $data->where($tableNewsStore . '.store_id', session('adminStoreId'));
                        }
                    }
                    $data = $data->pluck('title', 'id')->toArray();
                    self::$getListTitleAdmin = $data;
                }
                sc_set_cache(session('adminStoreId').'_cache_news_'.sc_get_locale(), self::$getListTitleAdmin);
            }
            return Cache::get(session('adminStoreId').'_cache_news_'.sc_get_locale());
        } else {
            if (self::$getListTitleAdmin === null) {
                $data = self::join($tableDescription, $tableDescription.'.news_id', $table.'.id')
                ->where('lang', sc_get_locale());
                if (sc_config_global('MultiVendorPro')) {
                    if (session('adminStoreId') != SC_ID_ROOT) {
                        $tableNewsStore = (new ShopNewsStore)->getTable();
                        $data = $data->leftJoin($tableNewsStore, $tableNewsStore . '.news_id', $table . '.id');
                        $data = $data->where($tableNewsStore . '.store_id', session('adminStoreId'));
                    }
                }
                $data = $data->pluck('title', 'id')->toArray();
                self::$getListTitleAdmin = $data;
            }
            return self::$getListTitleAdmin;
        }
    }


    /**
     * Create a new news
     *
     * @param   array  $dataInsert  [$dataInsert description]
     *
     * @return  [type]              [return description]
     */
    public static function createNewsAdmin(array $dataInsert)
    {
        return self::create($dataInsert);
    }


    /**
     * Insert data description
     *
     * @param   array  $dataInsert  [$dataInsert description]
     *
     * @return  [type]              [return description]
     */
    public static function insertDescriptionAdmin(array $dataInsert)
    {
        return ShopNewsDescription::create($dataInsert);
    }

    /**
    * Get total news of system
    *
    * @return  [type]  [return description]
    */
    public static function getTotalNews()
    {
        return self::count();
    }
}
