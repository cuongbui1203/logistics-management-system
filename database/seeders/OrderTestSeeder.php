<?php

namespace Database\Seeders;

use App\Enums\AddressTypeEnum;
use App\Enums\StatusEnum;
use App\Models\Noti;
use App\Models\Order;
use App\Models\WorkPlate;
use Illuminate\Database\Seeder;
use Str;

class OrderTestSeeder extends Seeder
{

    private function createWorkPlate($name, $addressId, $type, $cap)
    {
        $res = WorkPlate::create([
            'name' => $name,
            'address_id' => $addressId,
            'type_id' => $type,
            'cap' => $cap,
            'vung' => getAddressCode($addressId, $cap),
        ]);
        return $res->id;
    }

    private function createRoute($senderAddressId, $receiverAddressId)
    {
        $cap = getAddressRank($senderAddressId);
        $start = $this->createWorkPlate(
            Str::random(10),
            $senderAddressId,
            config('type.workPlate.transshipmentPoint'),
            $cap
        );
        $cap++;
        while ($cap <= AddressTypeEnum::Province) {
            $this->createWorkPlate(
                Str::random(10),
                $senderAddressId,
                config('type.workPlate.transshipmentPoint'),
                $cap
            );
            $cap++;
        }
        $cap = AddressTypeEnum::Province;
        $vung = getAddressCode($receiverAddressId, $cap);
        while ($receiverAddressId != $vung) {
            $this->createWorkPlate(
                Str::random(10),
                $receiverAddressId,
                config('type.workPlate.transshipmentPoint'),
                $cap
            );
            $cap--;
            $vung = getAddressCode($receiverAddressId, $cap);
        }
        $this->createWorkPlate(
            Str::random(10),
            $receiverAddressId,
            config('type.workPlate.transshipmentPoint'),
            $cap
        );
        return $start;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $idGui = $this->createRoute('27280', '07159');

        $order = new Order();
        $order->sender_name = 'senderName';
        $order->sender_phone = '0123456789';
        $order->sender_address_id = '27280';
        $order->receiver_name = 'receiver';
        $order->receiver_phone = '123123123';
        $order->receiver_address_id = '07159';

        $order->save();

        $notification = new Noti();
        $notification->order_id = $order->id;
        $notification->from_id = $idGui;
        $notification->to_id = $idGui;
        $notification->from_address_id = '27280';
        $notification->to_address_id = '27280';
        $notification->description = "";
        $notification->status_id = StatusEnum::Create;

        $notification->save();
    }
}
