<?php 

	//Token structure
	// header.payload.signature

	class JWT {
		private $header = '';
		private $payload = '';
		private $signature = '';
		private $token = '';
		private $alg = 'HS256';
		private $inside = false;

		public function __construct($secret = null, $userData = array( 'id' => 0 ), $alg = 'HS256') {
			if(count(explode('.',$secret))==3){
				$this->token = $secret;
			}
			else{
				$this->inside = true;
				$this->token = $this->generateToken($secret,$userData, $alg);
				$this->inside = false;
			}
		}

		public static function hasToken(){
			return isset($_SERVER['Authorization']);
		}

		public function getToken() {
			return $this->token;
		}

		public static function getTokenHeader() {
			return $_SERVER['Authorization'];
		}

		public function getData($token = null, $field = null) {
			if($field!=null){
				return $this->getPayload($token)->$field;
			}
			return $this->getPayload($token);
		}

		public function generateToken($secret, $userData = array( 'id' => 0 ), $alg = 'HS256'){
			if( $this->inside ) {
				$this->alg = $alg;
			}

			$header = str_replace(['+','/','='], ['-','_',''], base64_encode( json_encode(array( 
				'typ' => 'JWT',
				'alg' => $alg
			)) ) );

			if( $this->inside ) {
				$this->header = $header;
			}

			$payload =  str_replace(['+','/','='], ['-','_',''], base64_encode( json_encode($userData) ) );

			if( $this->inside ) {
				$this->payload = $payload;
			}

			$signature = str_replace(['+','/','='], ['-','_',''], base64_encode( hash_hmac('sha256', $this->header.".".$this->payload, $secret ,true) ) );

			if( $this->inside ) {
				$this->signature = $signature;
			}
			
			$token = $header.".".$payload.".".$signature;

			return $token;
		}

		public function validate( $secret ) {
			return JWT::s_validate($this->token, $secret);
		}

		public static function s_validate($token, $secret){
			//Validar token
			$header = explode('.', $token)[0];
			$payload = explode('.', $token)[1];
			$signature = explode('.', $token)[2];
			
			
			$signature2 = str_replace(['+','/','='], ['-','_',''], base64_encode( hash_hmac('sha256', $header.".".$payload, $secret , true) ) );
			if( $signature == $signature2 ) {
				
				return true;
			}
			return false;
		}

		

		private function getPayload($token = null) {
			if( $token == null ) {
				$token = $this->token;
			}
			return $this->getFromToken($token, 1);
		}

		private function getHeader($token = null) {
			if( $token == null ) {
				$token = $this->token;
			}
			return $this->getFromToken($token, 0);
		}

		private static function getFromToken($token, $index) {
			return json_decode( base64_decode( str_replace(['-','_',''], ['+','/','='], explode('.', $token)[$index] ) ) );
		}
	}