<?php
namespace SCart\Core\Front\Models;

use Illuminate\Database\Eloquent\Model;
use SCart\Core\Front\Models\ShopLanguage;

class Languages extends Model
{
    public $timestamps = false;
    public $table = SC_DB_PREFIX.'languages';
    protected $guarded = [];
    private static $getList = null;
    protected $connection = SC_CONNECTION;


    public static function getListAll($location)
    {
        if (self::$getList === null) {
            self::$getList = self::where('location', $location)->pluck('text', 'code');
        }
        return self::$getList;
    }

    /**
     * Get all positions
     *
     * @return void
     */
    public static function getPosition()
    {
        return self::groupBy('position')->pluck('position')->all();
    }

    /**
     * Get all
     *
     * @param [type] $lang
     * @param [type] $position
     * @return void
     */
    public static function getLanguagesPosition($lang, $position, $keyword = null)
    {
        if (!empty($lang)) {
            $languages = ShopLanguage::getCodeAll();
            if (!in_array($lang, array_keys($languages))) {
                return  [];
            }
            $data =  self::where('location', $lang);
            if (!empty($position)) {
                $data = $data->where('position', $position);
            }
            if (!empty($keyword)) {
                $data = $data->where('code', 'like', '%'.$keyword.'%');
            }
            $data = $data->get()
            ->keyBy('code')
            ->toArray();
            return $data;
        } else {
            return [];
        }
    }
}
