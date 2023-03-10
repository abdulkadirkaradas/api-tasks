<?php

class ConstructionStages
{
	private $db;

	public function __construct()
	{
		$this->db = Api::getDb();
	}

	public function getAll()
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
		");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function getSingle($id)
	{
		$stmt = $this->db->prepare("
			SELECT
				ID as id,
				name, 
				strftime('%Y-%m-%dT%H:%M:%SZ', start_date) as startDate,
				strftime('%Y-%m-%dT%H:%M:%SZ', end_date) as endDate,
				duration,
				durationUnit,
				color,
				externalId,
				status
			FROM construction_stages
			WHERE ID = :id
		");
		$stmt->execute(['id' => $id]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function post(ConstructionStagesCreate $data)
	{
		$calculator = new Calculation();
		$validation = new Validation();
        $validate = $validation->validate($data);
		
		if(gettype($validate) === "object")
		{
			$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");

			$start = new DateTime($data->startDate);
            $end = new DateTime($data->endDate);
			$stmt->execute([
				'name' => $data->name,
                "start_date" => $start->format(DateTimeInterface::ATOM),
                "end_date" => $end->format(DateTimeInterface::ATOM),
				'duration' => $data->duration,
				'durationUnit' => $data->durationUnit,
				'color' => $data->color,
				'externalId' => $data->externalId,
				'status' => $data->status,
			]);
			$calculator->init();
			return $this->getSingle($this->db->lastInsertId());
		}
		return $validate;
	}
    
    /**
     * patch
     * 
	 * Method receive request bag as a ConConstructionStagesCreate type.
	 * Calls Validation and Calculation class and create instances of classes.
	 * It then validates the received request bag with RegEx patterns.
	 * After this validation, the method checks the validation return type and status.
	 * If the status is not 'DELETED', the method prepares the query string and runs the query.
	 * After the query is run, the time is calculated and updated for the relevant record.
	 * Finally, the relevant record is returned.
	 * 
     * @param  ConstructionStagesCreate $data
     * @param  mixed $id
     * @return void
     */
    public function patch(ConstructionStagesCreate $data, $id)
    {
        $id = preg_replace('/<!--.*?-->/', '', $id);

		$calculator = new Calculation();
        $validation = new Validation();
        $validate = $validation->validate($data);
        
        if(gettype($validate) === "object")
        {
            $query = "UPDATE construction_stages SET";
			
            $start = new DateTime($data->startDate);
            $end = new DateTime($data->endDate);
            $params = array(
                "name" => $data->name,
                "start_date" => $start->format(DateTimeInterface::ATOM),
                "end_date" => $end->format(DateTimeInterface::ATOM),
                "duration" => $data->duration,
                "durationUnit" => $data->durationUnit,
                "color" => $data->color,
                "externalId" => $data->externalId,
                "status" => $data->status,
            );
    
            foreach ($params as $key => $value)
            {
                if($value != null)
                {
                    $query .= " $key= '$value',";
                }
            }
            $query = substr($query, 0, -1);
            $query .= " WHERE id=" . $id;
            $this->db->exec($query);
			$calculator->init();
    
            return $this->getSingle($id);
        }
		return $validate;
    }
	
	/**
	 * delete
	 * 
	 * The corresponding record is updated with the status value 'DELETED' according to the id value.
	 * 
	 * @param  mixed $id
	 * @return void
	 */
	public function delete($id)
	{
		$id = preg_replace('/<!--.*?-->/', '', $id);
		$query = "UPDATE construction_stages SET status='DELETED' WHERE id=$id";
		$this->db->exec($query);
		return $this->getSingle($id);
	}
}