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
	    $this->RegisterPropertyString("Queue", "");
 
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
	        $mq->srv = $this->ReadPropertyString("Server");
		$mq->port = $this->ReadPropertyString("Port");
		$mq->user = $this->ReadPropertyString("Username");
		$mq->pass = $this->ReadPropertyString("Password");
 	    	$mq->mq_queue = $this->ReadPropertyString("Queue");
		return $mq;
	}
	

        public function GetWorkWithOptions($ack_msg, $mq) {
	    // 0: $id
	    // 1: ack message
	    // 2: mq parameters 

	    try {
	    	$connection = new AMQPStreamConnection($mq->srv, $mq->port, $mq->user, $mq->pass);
	    	$channel = $connection->channel();
	    	$msg = $channel->basic_get($mq->queue);

		if (is_object($msg)) { 
	    		if ($ack_msg) { $channel->basic_ack($msg->delivery_info['delivery_tag']); }
			return $msg;
		} else { return null; }

	    } catch (Exception $e) {
	        IPS_LogMessage("SRMQ", "Exception during MQ operation: ".$e->getMessage());
		return null;
	    }

        }

	public function GetWork($ack_msg = true) {
		$this->GetWorkWithOptions($ack_msg, $this->mqConfig());
	}
    }
?>
