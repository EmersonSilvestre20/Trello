<?php

class trello
{
    private $key      = '2555c7c6e226209b0348197853352e06';
    private $token      = 'ATTA3d8e29d82072bec85857c94b42e2003bd0b1a501bfd45b634bad01102af4818f188873DA';
    private $endpoint   = 'https://api.trello.com/1';
    private $header     = [];
    private $opt        = [];
    private $data       = [];
    private $hcode      = NULL;
    private $curl;
    private $info;

    public function __destruct()
    {
        curl_close($this->curl);
    }
    
    public function __construct() {
        
        $this->key = defined('TRELLO_KEY') ? constant('TRELLO_KEY') : $this->key;
        
        $this->token = defined('TRELLO_TOKEN') ? constant('TRELLO_TOKEN') : $this->token;
        
        $this->endpoint = defined('TRELLO_ENDPOINT') ? constant('TRELLO_ENDPOINT') : $this->endpoint;
        
        $this->curl = curl_init();
        
        $this->header = [
            'Content-Type: application/json'
        ];
        
        $this->opt = [
            CURLOPT_URL             => $this->endpoint,
            CURLOPT_HTTPHEADER      => &$this->header,
            CURLOPT_RETURNTRANSFER  => TRUE
        ];
    }
    
    private function exec()
    {
        curl_setopt_array($this->curl, $this->opt);
        
        $err = curl_error($this->curl);
        
        $res = curl_exec($this->curl);
        
        $this->hcode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        
        $this->info = curl_getinfo($this->curl);
        
        if (curl_errno($this->curl))
            return $err;
        
        return $res;
    }

    private function addDaysToDate($initialDate, $daysToAdd)
    {
        $initialTimestamp = strtotime($initialDate);
        $newTimestamp = $initialTimestamp + ($daysToAdd * 24 * 60 * 60);
        return date('Y-m-d\TH:i:s.000\Z', $newTimestamp);
    }

    public function getCard($cardname, $raw = true)#
    {
        $this->opt[CURLOPT_CUSTOMREQUEST] = 'GET';
            
        $this->opt[CURLOPT_URL] = $this->endpoint . "/search?query={$cardname}&modelTypes=cards&card_board=true&card_list=true&key={$this->key}&token={$this->token}";
        
        $res = $this->exec();
        
        if ($raw)
            return $res;
        else
            return json_decode ($res, TRUE);
    }

    public function getCardById($idcard, $raw = true)#
    {
        $this->opt[CURLOPT_CUSTOMREQUEST] = 'GET';
            
        $this->opt[CURLOPT_URL] = $this->endpoint . "/cards/{$idcard}?key={$this->key}&token={$this->token}";
        
        $res = $this->exec();
        
        if ($raw)
            return $res;
        else
            return json_decode ($res, TRUE);
    }

    public function getCardFromBoard($cardname, $boardname)#
    {
        $cards = $this->getCard($cardname, false)['cards'];
        
        if (is_array($cards) && count($cards) > 0)
        {
            foreach ($cards as $i => $card) {
                $bn = $card['board']['name'] ?? false;
                $closed = $card['closed'] ?? true;
                if ($bn === $boardname && !$closed)
                    return $card;
            }
        }
    }
    
    public function getBoard($boardname, $raw = true)#
    {
        $this->opt[CURLOPT_CUSTOMREQUEST] = 'GET';
            
        $this->opt[CURLOPT_URL] = $this->endpoint . "/search?query={$boardname}&modelTypes=boards&card_board=true&card_list=true&key={$this->key}&token={$this->token}";
        
        $res = $this->exec();
        
        if ($raw)
            return $res;
        else
            return json_decode ($res, TRUE);
    }

    public function getLists($boardname, $raw = true)#
    {
        $board = $this->getBoard(urlencode($boardname), false);
        
        if ( !isset($board['boards'][0]['id']) || count($board['boards'])!==1 )
            return false;
        
        $boardid = $board['boards'][0]['id'];
        
        $this->opt[CURLOPT_CUSTOMREQUEST] = 'GET';
        
        $this->opt[CURLOPT_URL] = $this->endpoint . "/boards/{$boardid}/lists/?key={$this->key}&token={$this->token}";
        
        $res = $this->exec();
        
        if ($raw)
            return $res;
        else
            return json_decode ($res, TRUE);
    }

    public function getList($lists, $listname, $raw = true)#
    {
        #die($listname);
        foreach ($lists as $i => $list) {
            if ($listname === $list['name'])
                return $list;
        }
    }
    
    public function setDate($idCard, $date, $interval, $raw = true)#
    {
        /*Anos, meses e dias:
        P1Y - 1 ano
        P3M - 3 meses
        P10D - 10 dias
        P1Y2M5D - 1 ano, 2 meses e 5 dias
        Horas, minutos e segundos:
        PT1H - 1 hora
        PT30M - 30 minutos
        PT45S - 45 segundos
        PT1H30M15S - 1 hora, 30 minutos e 15 segundos
        Combinação de anos, meses, dias, horas, minutos e segundos:
        P1Y2M10DT1H30M - 1 ano, 2 meses, 10 dias, 1 hora e 30 minutos*/
        
        #$date = ( isset($t['due']) && !empty($t['due']) ) ? $t['due'] : null;
        
        $date = new DateTime($date);
        $interval = new DateInterval($interval);
        $newDate = $date->add($interval);
        $newDate = $newDate->format('Y-m-d\TH:i:s.u\Z');

        
        $this->opt[CURLOPT_CUSTOMREQUEST] = 'PUT';
        
        $this->opt[CURLOPT_URL] = $this->endpoint . "/cards/{$idCard}?due={$newDate}&key={$this->key}&token={$this->token}";
        
        $res = $this->exec();
        
        if ($raw)
            return $res;
        else
            return json_decode ($res, TRUE);
    }

    /*private function getBoard($idBoard)
    {
        $header = [
            'Accept' => 'application/json'
        ];

        $opt = [
            CURLOPT_URL => 'https://api.trello.com/1/boards/' . $idBoard . '?key=' . $this->key . '&token=' . $this->token,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $opt);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }*/

    private function getCustomFields($idBoard)
    {
        $header = [
            'Accept' => 'application/json'
        ];

        $opt = [
            CURLOPT_URL => 'https://api.trello.com/1/boards/' . $idBoard . '/customFields?key=' . $this->key . '&token=' . $this->token,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $opt);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    private function searchCardName($cardName)
    {
        #$cardName = 'Paulo Rosa2';
        $idBoard = "65af887d2374eea1a47198b9";
        $url = "https://api.trello.com/1/boards/$idBoard/cards";

        $params = [
            'key'   => $this->key,
            'token' => $this->token,
        ];
        $url .= '?' . http_build_query($params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $cards = json_decode($response, true);

        foreach ($cards as $card) {
            if ($card['name'] === $cardName) {
                return $card;
            }
        }
        return null;
    }
    private function getCustomFieldID($cf, $name)
    {
        foreach ($cf as $field) {
            if ($field['name'] == $name) {
                return $field['id'];
            }
        }

        return false;
    }

    private function updateCustomField($idCard, $idCustomField, $valor)
    {
        $header = [
            'Content-Type' => 'application/json'
        ];

        if (is_numeric($valor)) {
            $data = [
                'value' => [
                    'number' => $valor
                ]
            ];
        } else {
            $data = [
                'value' => [
                    'text' => $valor
                ]
            ];
        }
        $data = http_build_query($data);
        $opt = [
            CURLOPT_URL => 'https://api.trello.com/1/cards/' . $idCard . '/customField/' . $idCustomField . '/item?key=' . $this->key . '&token=' . $this->token,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_RETURNTRANSFER => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $opt);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
    private function data($idCard, $dateToSet)
    {
        $url = "https://api.trello.com/1/cards/{$idCard}?key={$this->key}&token={$this->token}";

        $params = [
            'due' => $dateToSet,
        ];

        $options = [
            'http' => [
                'header' => "Content-type: application/json",
                'method' => 'PUT',
                'content' => json_encode($params),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            return false;
        } else {
            return true;
        }
    }
    private function addCard($listId, $nome, $desc)
    {
        $header = [
            'Accept' => 'application/json'
        ];

        $data = [
            'name' => $nome,
            'desc' => $desc
        ];

        $opt = [
            CURLOPT_URL => $this->endpoint . '/cards?idList=' . $listId . '&key=' . $this->key . '&token=' . $this->token,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $opt);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }

    /*public function createCard($listId, $nome, $desc, $email, $cliente, $watsapp, $data = false)
    {
        $card = $this->addCard($listId , $nome, $desc);
        $idCard = $card['id'];
        $idBoard = $card['idBoard'];

        $board = $this->getBoard($idBoard);
        $cf = $this->getCustomFields($idBoard);
        date_default_timezone_set('Africa/Luanda');
        $initialDate = date('Y-m-d\TH:i:s.000\Z');
        $newDate = $this->addDaysToDate($initialDate, 3);
        if($data === true){
            $this->data($idCard, $newDate);
        }
        

        $idCampo = $this->getCustomFieldID($cf, 'E-mail');

        $this->updateCustomField($idCard, $idCampo, $email);

        $idCampo = $this->getCustomFieldID($cf, 'WhatsApp');
        $this->updateCustomField($idCard, $idCampo, $watsapp);

        $idCampo = $this->getCustomFieldID($cf, 'Cliente');
        $this->updateCustomField($idCard, $idCampo, $cliente);

        $res = [
            'mensagem' => 'Pedido de cadastro registado com sucesso!'
        ];

        return $res;
    }*/
    
    public function createCard($listId, $nome, $desc, $dados) {
        $card = $this->addCard($listId, $nome, $desc);
        $idCard = $card['id'];
        $idBoard = $card['idBoard'];

        $board = $this->getBoard($idBoard);
        $cf = $this->getCustomFields($idBoard);
        date_default_timezone_set('Africa/Luanda');
        $initialDate = date('Y-m-d\TH:i:s.000\Z');
        $newDate = $this->addDaysToDate($initialDate, 3);
        if ($data === true) {
            $this->data($idCard, $newDate);
        }

        foreach ($dados as $key => $value) {
            $idCampo = $this->getCustomFieldID($cf, $key);
            $this->updateCustomField($idCard, $idCampo, $value);
        }
        $res = [
            'mensagem' => 'Pedido de cadastro registado com sucesso!'
        ];

        return $res;
    }


    public function updatedate($nomeDoCartao, $daysToAdd)
    {
        $card = $this->searchCardName($nomeDoCartao);

        if ($card !== null) {
            $idCard = $card['id'];
            $newDate = $this->addDaysToDate($card['due'], $daysToAdd);

            return $this->data($idCard, $newDate);
        }

        return false;
    }
}
/*$trello = new Trello('2555c7c6e226209b0348197853352e06', 'ATTA3d8e29d82072bec85857c94b42e2003bd0b1a501bfd45b634bad01102af4818f188873DA', '65af88af3589e69e427e3de9');

$numero_rastreio = 957898401;
$email = 'Bartolomeu20032023@gmail.com';
$cliente = 'Bartolomeu Justino';
$watsapp = 936032254;
$desc = "Dados do cliente
E-mail: $email 
Cliente: $cliente 
WhatsApp: $watsapp";

$res = $trello->createCard($numero_rastreio, $desc, $email, $cliente, $watsapp);
#$res = $trello->updatedate('936032254',10);
var_dump($res);*/