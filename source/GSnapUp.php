<?php

namespace WSIServices\GSnapUp;

use \WSIServices\GSnapUp\GCloud;

class GSnapUp {

    protected $gCloud;

    protected $noop;

    /**
     * Create a new GCloud instance
     *
     * @return void
     */
    public function __construct(GCloud $gCloud, $noop = false) {
        $this->gCloud = $gCloud;

        $this->noop = $noop;
    }

    /**
     * Set no op setting for command
     *
     * @param  bool $noop  Optional, if true command is mocked, if false command is run
     * @return void
     */
    public function setNoop($noop = true) {
        $this->noop = $noop;
    }

    /**
     * Determine if no op is set on command
     *
     * @return bool  If true command will be mocked, if false command will be run
     */
    public function isNoop() {
        return $this->noop;
    }

    /**
     * Reset GCloud command
     *
     * @return void
     */
    protected function resetGCloud() {
        $this->gCloud->resetCommand();

        $this->gCloud->setOption('format', 'json');
    }

    /**
     * Return zone details for specified zones
     *
     * @param  array|bool|string $zones  Optional, zone name or array of zone names to return, or false to return all zones
     * @return array                     Details for specified zones
     */
    public function computeZonesList($zones = false) {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('zones')
            ->addArgument('list');

        if($zones) {
            if(!is_array($zones)) {
                $zones = [ $zones ];
            }

            foreach($zones as $zone) {
                $this->gCloud->addArgument($zone);
            }
        }

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

    /**
     * Return instance details for specified instances and zones
     *
     * @param  array|bool|string $instances  Optional, instance name or array of instance names to return, or false to return all instances
     * @param  array|bool|string $zones      Optional, zone name or array of zone names to return from, or false to return from all zones
     * @return array                         Details for specified instances
     */
    public function computeInstancesList($instances = false, $zone = false) {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('instances')
            ->addArgument('list');

        if($zone) {
            if(is_array($zone)) {
                $zone = implode(',', $zone);
            }

            $this->gCloud->setOption('zones', $zone);
        }

        if(!is_array($instances)) {
            $instances = [ $instances ];
        }

        foreach($instances as $instance) {
            $this->gCloud->addArgument($instance);
        }

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

    /**
     * Return disk details for specified zones
     *
     * @param  array|bool|string $zones  Optional, zone name or array of zone names to return from, or false to return from all zones
     * @return array                     Details for specified disks
     */
    public function computeDisksList($zone = false) {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('disks')
            ->addArgument('list');

        if($zone) {
            if(is_array($zone)) {
                $zone = implode(',', $zone);
            }

            $this->gCloud->setOption('zones', $zone);
        }

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

    /**
     * Return snapshots details
     *
     * @return array  Details for snapshots
     */
    public function computeSnapshotsList() {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('snapshots')
            ->addArgument('list');

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

    /**
     * Remove snapshot by name
     *
     * @param  string $snapshotName  Name of snapshot to delete
     * @return array                 Return value from deletion process
     */
    public function computeSnapshotsDelete($snapshotName) {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('snapshots')
            ->addArgument('delete')
            ->addArgument($snapshotName);

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

    /**
     * Generate snapshot for specified disk
     *
     * @param  string  $diskName      Name of disk to generate snapshot for
     * @param  string  $snapshotName  Name of snapshot to create
     * @param  string  $zone          Zone disk is in
     * @param  string  $description   Optional, description to add to snapshot
     * @param  bool    $async         Optional, if true call snapshot asynchronously, if false call snapshot and wait for completion
     * @return array                  Return value from snapshot process
     */
    public function computeDisksSnapshot($diskName, $snapshotName, $zone, $description = null, $async = false) {
        $this->resetGCloud();

        $this->gCloud
            ->addArgument('compute')
            ->addArgument('disks')
            ->addArgument('snapshot')
            ->addArgument($diskName)
            ->setOption('snapshot-names', $snapshotName)
            ->setOption('zone', $zone);

        if($description) {
            $this->gCloud->setOption('description', $description);
        }

        if($async) {
            $this->gCloud->setOption('async');
        }

        return $this->gCloud->execute($this->noop)
            ->getResult();
    }

}