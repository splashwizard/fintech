<?php
namespace App\Utils\GameUtils;

use stdClass;

class TransferWallet
{
    protected $AppID;
    protected $SecretKey;
    protected $ApiUrl;
    protected $GameUrl;
    public function __construct(){
        $this->AppID = 'TG57';
        $this->SecretKey = 's5g5t5wf4mtek';
        $this->ApiUrl = 'http://api688.net/';
        $this->GameUrl = 'http://www.gwc688.net/';
    }
    public function GetSignature($fields)
    {
        ksort($fields);
        $signature = urlencode(base64_encode(hash_hmac("sha1", urldecode(http_build_query($fields,'', '&')), $this->SecretKey, TRUE)));

        return $signature;
    }

    public function GetListGame()
    {
        try {
            $fields = [
                'Method' => 'ListGames',
                'Timestamp' => time()
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $response = new stdClass();
            $result = json_decode($data, true);
            if (isset($result['ListGames'])) {
                $response->Success = true;
                $response->Games = $result['ListGames'];
            } else {
                $response->Success = false;
                $response->Message = $result['Message'];
            }

            return $response;
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function GetPlayGameUrl($username, $gameCode)
    {
        try {
            $fields = [
                'Method' => 'PLAY',
                'Timestamp' => time(),
                'Username' => $username
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            $response = new stdClass();
            if (isset($result['Token'])) {
                $forwardUrl = $this->GameUrl . "?token=" . $result['Token'] . "&game=" . $gameCode . "&mobile=false";

                $response->Success = true;
                $response->ForwardUrl = $forwardUrl;
            } else {
                $response->Success = false;
                $response->Message = $result['Message'];
            }

            return $response;
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function GetPlayGameUrlWithDepositAmount($username, $amount, $requestId, $gameCode)
    {
        try {
            $fields = [
                'Method' => 'PLAY',
                'Timestamp' => time(),
                'Username' => $username,
                'Amount' => number_format($amount, 2, '.', ''),
                'RequestID' => $requestId
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            $response = new stdClass();
            if (isset($result['Token'])) {
                $forwardUrl = $this->GameUrl . "?token=" . $result['Token'] . "&game=" . $gameCode . "&mobile=false";

                $response->Success = true;
                $response->ForwardUrl = $forwardUrl;
            } else {
                $response->Success = false;
                $response->Message = $result['Message'];
            }

            return $response;
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function TransferCreditToJoker($username, $amount, $requestId)
    {
        if ($amount < 0) {
            return 'Failed';
        }

        if ($amount == 0) {
            return 'Success';
        }

        try {
            $fields = [
                'Method' => 'TC',
                'Timestamp' => time(),
                'Username' => $username,
                'Amount' => number_format($amount, 2, '.', ''),
                'RequestID' => $requestId
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode >= 400 && $httpCode < 500) {
                return 'Failed';
            } else if ($httpCode >= 500) {
                return verifyTransfer($requestId);
            } else {
                return 'Success';
            }
        } catch (Exception $e) {
            return 'Unknown';
        }

    }

    public function verifyTransfer($requestId)
    {
        try {
            $fields = [
                'Method' => 'TCH',
                'Timestamp' => time(),
                'RequestID' => $requestId
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode >= 400 && $httpCode < 500) {
                return 'Failed';
            } else if ($httpCode >= 500) {
                return 'Unknown';
            } else {
                return 'Success';
            }
        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    public function GetUserCredit($username)
    {
        try {
            $fields = [
                'Method' => 'GC',
                'Timestamp' => time(),
                'Username' => $username
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            $response = new stdClass();

            if (isset($result['Credit'])) {
                $response->Success = true;
                $response->Credit = $result['Credit'];
                $response->OutstandingCredit = $result['OutstandingCredit'];
                $response->FreeCredit = $result['FreeCredit'];
                $response->OutstandingFreeCredit = $result['OutstandingFreeCredit'];
                $response->Username = $result['Username'];
            } else {
                $response->Success = false;
                $response->Message = $result['Message'];
            }

            return $response;
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function TransferCreditOutJoker($username, $amount, $requestId)
    {
        if ($amount < 0) {
            return 'Failed';
        }

        if ($amount == 0) {
            return 'Success';
        }

        try {
            $amount = $amount * -1;

            $fields = [
                'Method' => 'TC',
                'Timestamp' => time(),
                'Username' => $username,
                'Amount' => number_format($amount, 2, '.', ''),
                'RequestID' => $requestId
            ];

            $signature = $this->GetSignature($fields);

            $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json')
            );

            $data = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($httpCode >= 400 && $httpCode < 500) {
                return 'Failed';
            } else if ($httpCode >= 500) {
                return verifyTransfer($requestId);
            } else {
                return 'Success';
            }
        } catch (Exception $e) {
            return 'Unknown';
        }
    }

    public function GetTransactions($start, $end)
    {
        $NextId = '';
        $GameTransactions = array();

        do {
            try {
                $fields = [
                    'Method' => 'TS',
                    'Timestamp' => time(),
                    'StartDate' => date("Y-m-d h:i", $start),
                    'EndDate' => date("Y-m-d h:i", $end),
                    'NextId' => $NextId
                ];

                $signature = $this->GetSignature($fields);

                $url = $this->ApiUrl . "?AppID=" . $this->AppID . "&Signature=" . $signature;

                $postData = json_encode($fields);
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json')
                );

                $data = curl_exec($curl);

                curl_close($curl);

                $result = json_decode($data, true);
                if (isset($result['Message'])) {
                    $response = new stdClass();
                    $response->Success = false;
                    $response->Message = $result['Message'];
                    return $response;
                }

                $NextId = json_decode($data, true)['nextId'];
                array_push($GameTransactions, $data);
            } catch (Exception $e) {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $e->getMessage();
                return $response;
            }

        } while (isset($NextId) && trim($NextId) !== '');

        $response = new stdClass();
        $response->Success = true;
        $response->Transactions = $GameTransactions;
        return $response;
    }
}