<?php
#S-Cart/Core/Front/Models/ShopLink.php
namespace SCart\Core\Front\Models;

use SCart\Core\Front\Models\ShopStore;
use Illuminate\Database\Eloquent\Model;

class ShopLink extends Model
{
    public $timestamps = false;
    public $table = SC_DB_PREFIX.'shop_link';
    protected $guarded = [];
    protected $connection = SC_CONNECTION;
    protected static $getGroup = null;

    public function stores()
    {
        return $this->belongsToMany(ShopStore::class, ShopLinkStore::class, 'link_id', 'store_id');
    }

    public static function getGroup()
    {
        if (!self::$getGroup) {
            $tableLink = (new ShopLink)->getTable();

            $dataSelect = $tableLink.'.*';
            $links = self::selectRaw($dataSelect)
                ->where($tableLink.'.status', 1);
            $storeId = config('app.storeId');
            if (sc_config_global('MultiStorePro') || sc_config_global('MultiVendorPro')) {
                $tableLinkStore = (new ShopLinkStore)->getTable();
                $tableStore = (new ShopStore)->getTable();
                $links = $links->join($tableLinkStore, $tableLinkStore.'.link_id', $tableLink . '.id');
                $links = $links->join($tableStore, $tableStore . '.id', $tableLinkStore.'.store_id');
                $links = $links->where($tableStore . '.status', '1');
                $links = $links->where($tableLinkStore.'.store_id', $storeId);
            }

            $links = $links
                ->orderBy($tableLink.'.sort', 'asc')
                ->orderBy($tableLink.'.id', 'desc')
                ->get()
                ->groupBy('group');
            self::$getGroup = $links;
        }
        return self::$getGroup;
    }

    protected static function boot()
    {
        parent::boot();
        // before delete() method call this
        static::deleting(
            function ($link) {
                $link->stores()->detach();
            }
        );
    }
}
