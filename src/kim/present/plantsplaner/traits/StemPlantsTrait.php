<?php
declare(strict_types=1);

namespace kim\present\plantsplaner\traits;

use kim\present\plantsplaner\block\IPlants;
use kim\present\plantsplaner\data\BearablePlantsData;
use kim\present\plantsplaner\data\PlantsData;
use kim\present\plantsplaner\tile\Plants;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Stem;
use pocketmine\event\block\BlockGrowEvent;
use pocketmine\math\Facing;

/**
 * This trait provides a implementation for `Stem` and `IPlants` to reduce boilerplate.
 *
 * @see Stem, IPlants
 */
trait StemPlantsTrait{
    use CropsPlantsTrait;

    /** @inheritDoc */
    public function grow() : void{
        /** @var Stem|IPlants $this */
        if($this->canGrow()){
            if($this->age < 7){
                $block = clone $this;
                ++$block->age;

                $ev = new BlockGrowEvent($this, $block);
                $ev->call();
                if(!$ev->isCancelled()){
                    $pos = $this->getPos();
                    $world = $pos->getWorld();
                    $world->setBlock($pos, $ev->getNewState());
                }
            }else{
                $grow = $this->getPlant();

                $facings = Facing::HORIZONTAL;
                shuffle($facings);
                foreach($facings as $face){
                    $side = $this->getSide($face);
                    $down = $side->getSide(Facing::DOWN);
                    if($side->canBeReplaced() && ($down->getId() === BlockLegacyIds::FARMLAND || $down->getId() === BlockLegacyIds::GRASS || $down->getId() === BlockLegacyIds::DIRT)){
                        $ev = new BlockGrowEvent($side, $grow);
                        $ev->call();
                        if(!$ev->isCancelled()){
                            $pos = $side->getPos();
                            $world = $pos->getWorld();
                            $world->setBlock($pos, $ev->getNewState());
                        }
                        break;
                    }
                }
            }
        }
    }

    /** @inheritDoc */
    public function canGrow() : bool{
        /** @var Stem|IPlants $this */
        if($this->age < 7){
            return true;
        }else{
            $stemPlant = $this->getPlant();
            foreach(Facing::HORIZONTAL as $face){
                if($this->getSide($face)->isSameType($stemPlant)){
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * @inheritDoc
     * @see PlantsData::getGrowSeconds()
     * @see BearablePlantsData::getBearSeconds()
     */
    public function getGrowSeconds() : float{
        return $this->age < 7 ? $this->getPlantsData()->getGrowSeconds() : $this->getPlantsData()->getBearSeconds();
    }

    /**
     * @override to register scheduling when near block changed.
     * If fruit is breaked, adds plants to scheduling because it will have to grow again.
     */
    public function onNearbyBlockChange() : void{
        parent::onNearbyBlockChange();

        if($this->canGrow()){
            $plantsTile = $this->pos->getWorld()->getTile($this->pos);
            if($plantsTile instanceof Plants){
                $plantsTile->setLastTime(microtime(true));
            }
            $this->pos->getWorld()->scheduleDelayedBlockUpdate($this->pos, Plants::$updateDelay);
        }
    }
}