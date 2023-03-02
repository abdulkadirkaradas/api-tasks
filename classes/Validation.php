<?php

class Validation 
{    
    /**
     * patterns
     * 
     * Array contains RegEx patterns for received values.
     * 
     * @var array
     */
    private $patterns = array(
        "name" => '/[a-zA-Z].{0,254}/',
        "startDate" => '/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}/',
        "endDate" => '/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}|null/',
        "duration" => '/[a-zA-Z0-9|:|.|\/s].*|null/',
        "durationUnit" => '/^(HOURS|DAYS|WEEKS)$\b|null/',
        "color" => '/\#[a-zA-Z0-9].{0,5}|null/',
        "externalId" => '/[a-zA-Z0-9].{0,254}/',
    );
        
    /**
     * status
     * 
     * Array contains status types for validation
     * 
     * @var array
     */
    private $status = array(
        "NEW",
        "PLANNED",
        "DELETED"
    );
    
    /**
     * validate
     * 
     * The method first checks whether the retrieved object has the status key.
     * If there is it passes it to the RegEx control, otherwise it returns an appropriate error.
     * It is checked whether the values received in the RegEx control match the RegEx patterns.
     * If it fits, the object is returned, if it doesn't, it returns an appropriate error.
     * 
     * @param  mixed $object
     * @return Object
     */
    public function validate($object)
    {
        foreach($this->patterns as $pattern_key => $pattern)
        {
            foreach($object as $object_key => $value)
            {
                if($object_key == "status")
                {
                    if(!in_array($value, $this->status))
                    {
                        return "Invalid status value!";
                    }
                }
                if($pattern_key == $object_key && $value != "")
                {
                    if(!preg_match($pattern, $value))
                    {
                        return "Invalid value: " . $object_key . ": " . $value;
                    }
                }
            }
        }
        return $object;
    }
}