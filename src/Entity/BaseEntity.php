<?php

namespace Ulib\Grabber\Entity;

class BaseEntity
{
    /**
     * @ignore
     */
    protected $childEntity;

    public function __construct()
    {
        $this->childEntity = get_called_class();
    }

    public function patch(array $data)
    {
        $childEntity = (new $this->childEntity);
        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $childEntity = call_user_func_array([$childEntity, $setter], [$value]);
        }
        return $childEntity;
    }
}
