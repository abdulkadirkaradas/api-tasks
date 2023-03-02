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
		$validation = new Validation();
        $validate = $validation->validate($data);
		
		if(gettype($validate) === "object")
		{
			$stmt = $this->db->prepare("
			INSERT INTO construction_stages
			    (name, start_date, end_date, duration, durationUnit, color, externalId, status)
			    VALUES (:name, :start_date, :end_date, :duration, :durationUnit, :color, :externalId, :status)
			");
			$stmt->execute([
				'name' => $data->name,
				'start_date' => $data->startDate,
				'end_date' => $data->endDate,
				'duration' => $data->duration,
				'durationUnit' => $data->durationUnit,
				'color' => $data->color,
				'externalId' => $data->externalId,
				'status' => $data->status,
			]);
			return $this->getSingle($this->db->lastInsertId());
		}
		return $validate;
	}

    public function patch(ConstructionStagesCreate $data, $id)
    {
        $id = preg_replace('/<!--.*?-->/', '', $id);

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
    
            return $this->getSingle($id);
        }
		return $validate;
    }

	public function delete($id)
	{
		$id = preg_replace('/<!--.*?-->/', '', $id);
		$query = "UPDATE construction_stages SET status='DELETED' WHERE id=$id";
		$this->db->exec($query);
		return $this->getSingle($id);
	}
}