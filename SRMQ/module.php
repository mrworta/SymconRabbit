<?
    require __DIR__ . '/vendor/autoload.php';
    use PhpAmqpLib\Connection\AMQPStreamConnection;

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
 
        }
 
        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
        public function ApplyChanges() {
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
        }
 
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
        public function GetWork() {
	    $mq_srv = $this->ReadPropertyString("Server");
	    $mq_port = $this->ReadPropertyString("Port");
 	    $mq_user = $this->ReadPropertyString("Username");
 	    $mq_pass = $this->ReadPropertyString("Password");

	    $connection = new AMQPStreamConnection($mq_srv, $mq_port, $mq_user, $mq_pass);
	    $channel = $connection->channel();

	    try {
	    	$msg = $channel->basic_get('symcon-alexa');
		if is_object($msg) { $work = $msg->body; } else { $work = 'No work found.'; }
	    	$channel->basic_ack($msg->delivery_info['delivery_tag']);
	    } catch (Exception $e) {
		$work = "Error."; 
		
	    }

	    return $work;
        }
    }
?>
