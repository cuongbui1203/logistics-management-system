<?php

use App\Enums\AddressTypeEnum;
use App\Models\Image;
use App\Models\Order;
use App\Models\WorkPlate;

if (!function_exists('storeImage')) {
    function storeImage($path, $file)
    {
        $pathImage = Storage::put($path, $file);
        $image = new Image();
        $image->url = $pathImage;
        $image->save();
        return $image->id;
    }
}

if (!function_exists('deleteImage')) {
    function deleteImage($id)
    {
        $image = Image::find($id);
        $path = $image->url;
        Storage::delete($path);
        $image->delete();
    }
}

if (!function_exists('api_path')) {
    /**
     * get path of api directory
     *
     * @param string $path
     * @return string
     */
    function api_path($path = '')
    {
        return base_path('/routes/api/' . $path);
    }
}

if (!function_exists('getAddressName')) {
    function getAddressName(string $id, int $type)
    {
        $table = "";
        switch ($type) {
            case 1:
                $table = "provinces";
                break;
            case 2:
                $table = "districts";
                break;
            case 3:
                $table = "wards";
                break;
            default:
                throw new Exception("unknown type");
        }
        // return $table;
        try {
            $address = DB::connection('sqlite_vn_map')
                ->table(DB::raw("$table t"))
                ->where("t.code", $id)
                ->select(DB::raw("t.name"))
                ->get();
            return $address[0]->name;
        } catch (Exception $e) {
            return "unknown";
        }
    }
}


if (!function_exists('getAddressCode')) {
    /**
     * lay code cua 1 dia chi tu id cua wards
     *
     * @param string $id address wards id
     * @param integer $type 1,2,3
     * @return string|null
     * @throws Exception
     */
    function getAddressCode(string $id, int $type)
    {
        $res = '';
        // dd($type);
        switch ($type) {
            case AddressTypeEnum::Province:
                $res =  DB::connection('sqlite_vn_map')
                    ->table(DB::raw("wards w"))
                    ->join(DB::raw("districts d"), "d.code", "=", "w.district_code")
                    ->join(DB::raw("provinces p"), "p.code", "=", "d.province_code")
                    ->where(DB::raw("w.code"), "=", $id)
                    ->select(DB::raw("p.code"))
                    ->first();

                break;
            case AddressTypeEnum::District:
                $res = DB::connection('sqlite_vn_map')
                    ->table(DB::raw("wards w"))
                    ->join(DB::raw("districts d"), "d.code", "=", "w.district_code")
                    ->where(DB::raw("w.code"), "=", $id)
                    ->select(DB::raw("d.code"))
                    ->first();
                break;
            case AddressTypeEnum::Ward:
                return $id;
            default:
                throw new Exception("unknown type");
        }
        return $res->code;
    }
}

if (!function_exists('getAddress')) {
    function getAddress($addressId)
    {
        $res =  DB::connection('sqlite_vn_map')
            ->table(DB::raw("wards w"))
            ->join(DB::raw("districts d"), "d.code", "=", "w.district_code")
            ->join(DB::raw("provinces p"), "d.province_code", "=", "p.code")
            ->select(
                DB::raw("p.full_name as provinceName"),
                DB::raw("d.full_name as districtName"),
                DB::raw("w.full_name as wardName"),
                DB::raw('p.code as provinceCode'),
                DB::raw('d.code as districtCode'),
                DB::raw('w.code as wardCode')
            )
            ->where(DB::raw("w.code"), $addressId)
            ->first();
        if (!$res) {
            throw new Exception("loi truy van cho address co id: " . $addressId, 1);
        }
        $res = (object)array(
            'provinceCode' => $res->provinceCode,
            'districtCode' => $res->districtCode,
            'wardCode' => $res->wardCode,
            'province' => $res->provinceName,
            'district' => $res->districtName,
            'ward' => $res->wardName,
        );
        return $res;
    }
}

if (!function_exists('getAddressRank')) {
    /**
     * get rank of address over addressID
     *
     * @param string $addressId
     * @return int|string
     */
    function getAddressRank(string $addressId)
    {
        $db = DB::connection('sqlite_vn_map');
        $p = $db->table('provinces')->where('code', $addressId);
        $d = $db->table('districts')->where('code', $addressId);
        $w = $db->table('wards')->where('code', $addressId);

        if ($p->exists()) {
            return AddressTypeEnum::Province;
        }

        if ($d->exists()) {
            return AddressTypeEnum::District;
        }

        if ($w->exists()) {
            return AddressTypeEnum::Ward;
        }

        return -1;
    }
}

if (!function_exists('routing')) {
    /**
     * routing the order to the next transportPoint, Transaction or next state shipping.
     *
     * @param Order $order
     * @return WorkPlate|null
     */
    function routing(Order $order)
    {
        $idAddressHT = $order->notifications->last()->to->address_id;
        $vungHt = $order->notifications->last()->to->vung;
        $capHt = $order->notifications->last()->to->cap;
        $idAddressN = $order->receiver_address_id;

        if ($idAddressHT === $idAddressN && $capHt === AddressTypeEnum::Ward) {
            return null;
        }

        $resMain = null;
        switch ($capHt) {
            case AddressTypeEnum::Ward:
                $res = WorkPlate::where(
                    'vung',
                    '=',
                    getAddressCode($idAddressHT, AddressTypeEnum::District)
                )
                    ->first();
                if (!$res) {
                    $resMain =  null;
                    break;
                }

                $resMain = $res;
                break;

            case AddressTypeEnum::District:
                $codeNn = getAddressCode($idAddressN, AddressTypeEnum::District);
                if ($codeNn == $vungHt) {
                    $res = WorkPlate::where('vung', '=', $idAddressN)->first();
                } else {
                    $res = WorkPlate::where('vung', '=', getAddressCode($idAddressHT, AddressTypeEnum::Province))->first('id');
                }
                $resMain =  $res;
                break;

            case AddressTypeEnum::Province:
                if ($vungHt == getAddressCode($idAddressN, AddressTypeEnum::Province)) {
                    $res = WorkPlate::where('vung', '=', getAddressCode($idAddressN, AddressTypeEnum::District))->first();
                } else {
                    $res = WorkPlate::where('vung', '=', getAddressCode($idAddressN, AddressTypeEnum::Province))->first();
                }
                $resMain =  $res;
                break;

            default:
                break;
        }

        return $resMain;
    }
}

if (!function_exists('routingAnother')) {
    /**
     * lay vi tri goi y tiep theo
     *
     * @param Order $order order can xu ly
     * @return WorkPlate|null
     */
    function routingAnother(Order $order)
    {
        $noti = $order->notifications->last();
        $idAddressHT = $noti->to_address_id ?? $noti->to->address_id; // address id hiện tại
        $capHt = getAddressRank($idAddressHT); // cap hien tai
        $idAddressN = $order->receiver_address_id; // address id ng nhan
        $res = null;
        $count = 0;

        while ($capHt < AddressTypeEnum::Province) {
            $capHt++;
            $count++;
            $wp = WorkPlate::where('vung', getAddressCode($idAddressHT, $capHt))->first();
            if ($wp) {
                $res = $wp;
                break;
            };
        }
        if ($res)
            return $res;
        while ($capHt >= AddressTypeEnum::Ward) {
            $wp = WorkPlate::where('vung', getAddressCode($idAddressN, $capHt))->first();
            if ($wp) {
                $res = $wp;
                break;
            };
            $capHt--;
        }
        if ($res)
            return $res;
        else
            return null;
    }
}
