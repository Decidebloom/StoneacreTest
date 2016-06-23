<?php
class Experian{
	private static $username = 'STONEACRE0034';
	private static $password = 'NCOA13LS';
	private static $URL      = 'http://www.automotivemxin.com/soap/service.asmx';
	private static $URI      = 'http://www.automotivemxin.com/';
	private $soapClient;
	public function initialise(){
			try{
			$this->soapClient = new SoapClient(null, 
			  array('trace'=>0,
					'exception'=>1,
					'soap_version'=>'1.0',
					'connection_timeout'=>1900,
			  		'location'=>$this::$URL,
			  		'uri'=>$this::$URI,
			  		'use'=>SOAP_LITERAL)
			);
			return true;
		}
		catch(SoapFault $soapFault){
			return false;
		}
	}
	private function buildRequest($reg){
		$param = "<ns1:VehicleRegInput><ns1:IsLive>true</ns1:IsLive><ns1:Username>" . 
		          $this::$username. "</ns1:Username><ns1:Password>".$this::$password.
		          "</ns1:Password><ns1:VRM>".$reg."</ns1:VRM></ns1:VehicleRegInput>";
		$enquiryRequest = new SoapVar($param,XSD_ANYXML);
 		return $enquiryRequest;
	}
	private function buildResponse($data){
		if(!isset($data->VehicleRegistration))
		{
			return array(
					'Experian'=>array(
							'status'=>'failure',
							'fault_string'=>'Error looking up VRN, may be invalid',
					)
			);
		}
		$response = array(
						'Experian'=>array(
								'status'=>'success',
								'vrm'=>$data->VehicleRegistration->VRM,
								'make'=>$data->VehicleRegistration->Make,
								'model'=>$data->VehicleRegistration->Model,
								'body_type'=>$data->VehicleRegistration->DoorPlanLiteral,
								'colour'=>$data->VehicleRegistration->Colour,
								'engine_size'=>$data->VehicleRegistration->EngineCapacity,
								'fuel_type'=>$data->VehicleRegistration->Fuel,
								'manufacture_date'=>$data->VehicleRegistration->YearOfManufacture,
								'first_registered_date'=>$data->VehicleRegistration->DateFirstRegistered
						)
		);
		return $response;
	}
	public function enquiry($reg){
		if ($this->initialise() && !empty($reg)){
			$request = $this->buildRequest($reg);
			try{
				$enquiryResponse = $this->soapClient->__soapCall('GetVehicleData',array($request), array('soapaction'=>'http://www.automotivemxin.com/GetVehicleData'));
				$response = $this->buildResponse($enquiryResponse);
				return $response;
			}
			catch(SoapFault $soapFault)
			{
				return array(
						array('Experian'=>array(
								'status'=>'Callfailure',
								'fault_string'=>$soapFault->faultstring
								)
						)
				);
			}
		}
		return array(
				array('Experian'=>array(
						'status'=>'Initfailure',
						'fault_string'=>'Initialise or no reg error'
					)
				)
		);
	}
}