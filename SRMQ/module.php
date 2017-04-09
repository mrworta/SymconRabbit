<?
    require __DIR__ . '/vendor/autoload.php';
    use PhpAmqpLib\Connection\AMQPStreamConnection;
    use PhpAmqpLib\Message\AMQPMessage;

    // Klassendefinition
    class SRMQ extends IPSModule {
 
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
        public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);
 
            // Selbsterstellter Code
        }
 
        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
            // Diese Zeile nicht löschen.
            parent::Create();

	    // Create Properties for settings form:
	    $this->RegisterPropertyString("Server", "");
	    $this->RegisterPropertyString("Port", "");
	    $this->RegisterPropertyString("Username", "");
	    $this->RegisterPropertyString("Password", "");
	    $this->RegisterPropertyString("vHost", "/");
	    $this->RegisterPropertyString("Queue", "");
	    $this->RegisterPropertyInteger("LogLevel", 0);

 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        */
	public function mqConfig() {
		$mq = new stdClass();
	        $mq->srv = $this->ReadPropertyString("Server");
		$mq->port = $this->ReadPropertyString("Port");
		$mq->user = $this->ReadPropertyString("Username");
		$mq->pass = $this->ReadPropertyString("Password");
		$mq->vhost = $this->ReadPropertyString("vHost");
 	    	$mq->queue = $this->ReadPropertyString("Queue");		
		$mq->LogLevel = $this->ReadPropertyInteger("LogLevel");

		return $mq;
	}
	

        public function GetWorkWithOptions(bool $ack_msg, $mq) {
	    // 0: $id
	    // 1: ack message
	    // 2: mq parameters 

	    try {
	    	$connection = new AMQPStreamConnection($mq->srv, $mq->port, $mq->user, $mq->pass, $mq->vhost);
	    	$channel = $connection->channel();
	    	$msg = $channel->basic_get($mq->queue);

		if ($ack_msg && is_object($msg)) { 
	    		$channel->basic_ack($msg->delivery_info['delivery_tag']); 
		} 

		$channel->close();
		$connection->close();

		return $msg;

	    } catch (Exception $e) {
	        if ($mq->LogLevel > 1 ) { IPS_LogMessage("SRMQ", "Exception during MQ operation: ".$e->getMessage()); }
		return null;
	    }

        }

	public function ProcessOneRPCrequest($mq, $callback) {
		$connection = new AMQPStreamConnection($mq->srv, $mq->port, $mq->user, $mq->pass, $mq->vhost);
        	$channel = $connection->channel();

        	$channel->queue_declare($mq->queue, false, false, false, false);
				
		$channel->basic_qos(null, 1, null);
		$channel->basic_consume($mq->queue, '', false, false, false, false, $callback);
        				
		// TODO: Check how longer timeout affects symcon
		$timeout = 1;
		try { 
	    		while(count($channel->callbacks)) { $channel->wait(null, false, $timeout); }
		} catch (Exception $e) {
			return null;			
		}
		
		$channel->close();
		$connection->close();


	}

	public function GetWork(bool $ack_msg = true) {
		return $this->GetWorkWithOptions($ack_msg, $this->mqConfig());
	}
	    
	public function PutMessage(string $message) {
	    $mq = $this->mqconfig();
	    try {	
		$connection = new AMQPStreamConnection($mq->srv, $mq->port, $mq->user, $mq->pass, $mq->vhost);

  		$channel = $connection->channel();
  		$channel->queue_declare($mq->queue, false, false, false, false);
  		$h_msg = new AMQPMessage($message);

  		$result = $channel->basic_publish($h_msg, '', $mq->queue);

 		$channel->close();
  		$connection->close();	
		    
		return $result;

	    } catch (Exception $e) {
	        if ($mq->LogLevel > 1 ) { IPS_LogMessage("SRMQ", "Exception during MQ operation: ".$e->getMessage()); }
		return null;
	    }		    
		    
	}
	    
    }
?>
