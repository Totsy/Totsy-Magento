<?php

class Totsy_Cache_Backend_Redis extends Cm_Cache_Backend_Redis {

    public function getRedis() {
        return $this->_redis;
    }

    public function getCacheId($id) {
        return self::PREFIX_KEY.$id;
    }
}