<?php

class Calculation
{
    private $db;    
    /**
     * durationTypes
     * 
     * Duration types for calculation
     * 
     * @var array
     */
    private $durationTypes = array(
        "HOURS" => "24",
        "DAYS"  => "30",
        "WEEKS" => "7",
    );

    public function __construct()
    {
        $this->db = Api::getDb();
    }
    
    /**
     * init
     * 
     * The method retrieves all records from the ConstructionStages class instance.
     * It then creates the variables id, durationUnit, startDate and endDate according to the records.
     * It then takes the differences of the two date values and converts the tjem to the ISO8601 standard.
     * It then calls the 'getDuration' function. Updates the 'duration' value related to the data from the function.
     * 
     * @return void
     */
    public function init()
    {
        $cs = new ConstructionStages();
        $records = $cs->getAll();
        foreach($records as $key => $value)
        {
            $id = $value["id"];
            $durationUnit = $value["durationUnit"];
            if($durationUnit != null || $durationUnit != "")
            {
                if(isset($value["startDate"]) && isset($value["endDate"]))
                {
                    $start = new DateTime($value["startDate"]);
                    $end = new DateTime($value["endDate"]);

                    $interval = $end->diff($start);
                    $interval->format(DateTimeInterface::ATOM);

                    $duration = $this->getDuration($interval->days, $durationUnit);

                    $query = "UPDATE construction_stages SET duration=$duration WHERE id=$id";
                    $this->db->exec($query);
                }
            }
        }
    }
    
    /**
     * getDuration
     * 
     * The method calculates and returns the duration value based on the day and durationUnit values
     * 
     * @param  mixed $days
     * @param  mixed $durationUnit
     * @return void
     */
    public function getDuration($days, $durationUnit)
    {
        $unit = $this->durationTypes[$durationUnit];
        switch($durationUnit) {
            case "HOURS":
                return $days * $unit;
                break;
            case "DAYS":
                return $days;
                break;
            case "WEEKS":
                return $days / $unit;
                break;
        }
    }
    
}