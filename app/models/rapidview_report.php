<?

class  RapidviewReport extends AppModel
{
	var $primaryKey  = 'id';
	var $useTable    = 'rapidview_accountrates';
   
	function ReportSettings($id)
	{
		if($id) {
			$sql = "SELECT * FROM rapidview_reports WHERE id={$id};";
			$results = $this->query($sql);
			if ($results){
				$results = array_shift(array_shift($results));
				return $results;
			}
		}
	}
   
}