<?
	class EEPA50401RXextended extends IPSModule
	{
		public function Create() 
		{
			//Never delete this line!
			parent::Create();
			$this->RegisterPropertyString("DeviceID", "");
		}
    
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterVariableFloat("HUM", "Rel. Luftfeuchtigkeit", "", 0);
			$this->RegisterVariableFloat("TMP", "Temperatur", "", 0);
			$this->RegisterVariableFloat("VLT", "Batteriespannung", "", 0);
			$this->RegisterVariableFloat("AHUM", "Abs. Luftfeuchtigkeit", "", 0);
			$this->RegisterVariableFloat("DEW", "Taupunkt", "", 0);
			
			//Connect to available enocean gateway
			$this->ConnectParent("{A52FEFE9-7858-4B8E-A96E-26E15CB944F7}");
		}
		
		/*
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
			$data = json_decode($JSONString);
			$this->SendDebug("EnoceanGatewayData", $JSONString, 0);
			// Check if received enocean deviceID is equal to entered deviceID in moduel configuration
			if (strcmp($data->{'DeviceID'}, $this->ReadPropertyString("DeviceID")) === 0)
			{
				$this->CalcProcessValues($data);
			}
			else IPS_LogMessage("EEPA50401RXextended Device IDs",
								"Enocean Sensor DeviceID: " . $data->{'DeviceID'} . 
							    " and SymconModul DeviceID: " . $this->ReadPropertyString("DeviceID") . 
								" are not equal");
			
		}
		
		private function CalcProcessValues($spezData)
		{
			// temperature = tempValue / 250 * 40 °C
			$temperature = floatval($spezData->{'DataByte1'}); 
			$temperature = $temperature / 250 * 40;
			
			// humidity = humValue / 250 * 100 %
			$humidity = floatval($spezData->{'DataByte2'}); 
			$humidity = $humidity / 250 * 100;
			
			// goldCapVoltage = voltageValue / 255 * 1,8V * 4 - usually DataByte3 is not used in enocean standard!
			$goldCapVoltage = floatval($spezData->{'DataByte3'});
			$goldCapVoltage = $goldCapVoltage / 255 * 1.8 * 4;
			
			// Calc dewpoint and abs. humidity with Magnus coefficients
			$c1 = 6.1078; 							// hPa
			$c2 = 17.08085;                  // °C
			$c3 = 234.175;                   // °C
			$mw = 18.016;                    // g/mol
			$uniGasConstant = 8.3144598;    	// J/(mol*K)
			$tempInK = $temperature + 273.15;
			// Calculate saturationVaporPressure in hPa
			$saturationVaporPressure = $c1 * exp(($c2 * $temperature) / ($c3 + $temperature));
			// Calculate vaporPressure in hPa
			$vaporPressure = $saturationVaporPressure *  $humidity / 100;
			// Calculate dewpoint in °C
			$dewpoint = (log($vaporPressure / $c1) * $c3) / ($c2 - log($saturationVaporPressure / $c1));
			// Calculate absolute humidity in g/m³
			$absHum = $mw / $uniGasConstant * $vaporPressure / $tempInK * 100;
			
			// Write calculated values to registered variables
			$this->SetValueFloat("TMP", $temperature);
			$this->SetValueFloat("HUM", $humidity);
			$this->SetValueFloat("VLT", $goldCapVoltage);
			$this->SetValueFloat("AHUM", $absHum);
			$this->SetValueFloat("DEW", $dewpoint);
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
