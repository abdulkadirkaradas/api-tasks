<?php

class Calculation
{
    private $db;
    private $durationTypes = array(
        "HOURS" => "24",
        "DAYS"  => "30",
        "WEEKS" => "7",
    );

    public function __construct()
    {
        $this->db = Api::getDb();
    }

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