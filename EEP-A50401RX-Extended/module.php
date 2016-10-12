<?
	class EEPA50401RXextended extends IPSModule
	{
		public function Create() 
		{
			//Never delete this line!
			parent::Create();
		}
    
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterVariableFloat("HUM", "Humidity", "", 0);
			$this->RegisterVariableFloat("TMP", "Temperature", "", 0);
			$this->RegisterVariableFloat("VLT", "Battery", "", 0);
			
			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");
		}
		
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* IOT_Send($id, $text);
		*
		*public function Send($Text)
		*{
		*	$this->SendDataToParent(json_encode(Array("DataID" => "{B87AC955-F258-468B-92FE-F4E0866A9E18}", "Buffer" => $Text)));
		*}
   		 */
		
		public function ReceiveData($JSONString)
		{
			//$data = json_decode($JSONString);
			//IPS_LogMessage("IOTest", utf8_decode($data));
			IPS_LogMessage("EnoceanGatewayData", $JSONString);
      			//Parse and write values to our variables
			//$this->ParseData($JSONString);
			$this->SetValueFloat("TMP", $JSONString["DataByte1"]);
			$this->SendDebug("EnoceanGatewayData", $JSONString, 0);
		}
    
		private function ParseData($spezData)
		{
		// $searchPattern2 = '55 00 0a 07 01 eb a5';
		// $spezBuffer[0] => Sync Byte => 0x55;
		// $spezBuffer[1] => HighByte DataLength
		// $spezBuffer[2] => LowByte DataLength => 10 Databytes
		// $spezBuffer[3] => Optional Length
		// $spezBuffer[4] => Packet Type => 01=RADIO_ERP1
		// $spezBuffer[5] => CRC8 from spezData[1] to spezData[4]
		// $spezBuffer[6] => RadioType => 0xA5 => Radio type 4BS
		// $spezBuffer[7] => Databyte 3 => Voltage of Goldcap (usually this byte is not used!)
		// $spezBuffer[8] => Databyte 2 => Humidity
		// $spezBuffer[9] => Databyte 1 => Temperature    
		// $spezBuffer[10] => Databyte 0.Bit7 ... Bit4 => not unsed
		// $spezBuffer[10] => Databyte 0.Bit3 => [0=Teach-in telegram; 1=Data telegram]  
		// $spezBuffer[10] => Databyte 0.Bit2 => not used    
		// $spezBuffer[10] => Databyte 0.Bit1 => T-Sensor Availability [0=not available; 1=available]
		// $spezBuffer[11..14] => SenderID
		// $spezBuffer[15] => Telegram control bits
		}
		
		private function SetValueFloat($Ident, $value)
		{
			$id = $this->GetIDForIdent($Ident);
			SetValueFloat($id, floatval($value));
		}
		
		protected function SendDebug($Message, $Data, $Format)
		{
			if (is_array($Data))
			{
			    foreach ($Data as $Key => $DebugData)
			    {
						$this->SendDebug($Message . ":" . $Key, $DebugData, 0);
			    }
			}
			else if (is_object($Data))
			{
			    foreach ($Data as $Key => $DebugData)
			    {
						$this->SendDebug($Message . "." . $Key, $DebugData, 0);
			    }
			}
			else
			{
			    parent::SendDebug($Message, $Data, $Format);
			}
		} 
    
	}
?>
