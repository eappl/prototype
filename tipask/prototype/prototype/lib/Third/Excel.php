<?php

/*

$data = array(
	array('a', '我是中国1', '023123'),
	array('b', '我是中国2', '1234567890'),
	array('b', '我是中国2', '123456789'),
	array('c', '我是中国3', '123456789076'),
	array('d', '我是中国4', '12345674532352545435'),

);
$oExcel = new Third_Excel();

$oExcel->download('adf水电费.xls')->addSheet('sheet1')->addRows($data)->closeSheet()->addSheet('sheet2')->addRows($data)->closeSheet()->close();

*/
class Third_Excel
{
	protected $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";

	protected $footer = "</Workbook>";

	protected $body = '';

	public function download($filename)
	{
		$filename = iconv("UTF-8", "GBK//IGNORE", $filename);

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"".$filename.".xls\"");

        echo stripslashes($this->header);

		return $this;
	}

	public function addSheet($sheet)
	{
		echo "<Worksheet ss:Name=\"".$sheet."\">\n";
		echo "<Table>\n";

		return $this;
	}

	public function addRows($rows)
	{
		foreach ($rows as $item) {
			$cells = '';
			foreach ($item as $value) {
				if (is_numeric($value) && substr($value, 0 , 1) != 0 && strlen($value) < 10 ) {
					$cells .= "<Cell><Data ss:Type=\"Number\">".$value."</Data></Cell>\n";
					continue;
				}

				$cells .= "<Cell><Data ss:Type=\"String\">".$value."</Data></Cell>\n";
			}

			echo "<Row>\n".$cells."</Row>\n";
		}

		return $this;
	}

	public function closeSheet()
	{
        echo "</Table>\n";
		echo "</Worksheet>\n";

		return $this;
	}

	public function close()
	{
        echo $this->footer;
		exit();
	}


	public function addSheetSimple($sheet, $docArray)
	{
		$rows = $this->getCell($docArray);

        $this->body .= "<Worksheet ss:Name=\"".$sheet."\">\n";
		$this->body .= "<Table>\n";

        $this->body .= "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"110\"/>\n";
        $this->body .= implode("\n", $rows);

        $this->body .= "</Table>\n";
		$this->body .= "</Worksheet>\n";

		return $this;
	}

	public function downloadSimple($filename)
	{
		$filename = iconv("UTF-8", "GBK//IGNORE", $filename);

        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"".$filename.".xls\"");

        echo stripslashes($this->header);
		echo $this->body;
        echo $this->footer;
		exit();
	}

	protected function getCell($docArray)
	{
		$rows = array();
		foreach ($docArray as $item) {
			$cells = '';
			foreach ($item as $value) {
				if (is_numeric($value) && substr($value, 0 , 1) != 0 && strlen($value) < 10 ) {
					$cells .= "<Cell><Data ss:Type=\"Number\">".$value."</Data></Cell>\n";
					continue;
				}

				$cells .= "<Cell><Data ss:Type=\"String\">".$value."</Data></Cell>\n";
			}

			$rows[] = "<Row>\n".$cells."</Row>\n";
		}

		return $rows;
	}

}
