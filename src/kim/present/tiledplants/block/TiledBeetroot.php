<?php
declare(strict_types=1);

namespace kim\present\tiledplants\block;

use kim\present\tiledplants\data\PlantData;
use kim\present\tiledplants\traits\TiledCropsTrait;
use pocketmine\block\Beetroot;

final class TiledBeetroot extends Beetroot implements ITiledPlant{
    use TiledCropsTrait;

    public function getPlantData() : PlantData{
        return PlantData::BEETROOT();
    }
}