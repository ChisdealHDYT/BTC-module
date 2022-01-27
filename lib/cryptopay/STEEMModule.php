<?php 

namespace Payments\Crypto;

use GuzzleHttp\Client;

class STEEMModule{
    
    public function __construct()
    {
        $this->explorer_url = "https://api.chisdealhd.co.uk/v1/cryptoproxyexplorer/steem/";
        $this->client = new client();
    }

    public function existsTransaction($address, $amount, $memo, $timestamp)
    {
        try{
            $transactions = $this->getAddressTransactions($address);
		
            foreach($transactions as $transaction)
            {
                $transaction_info = $this->getTransaction($address, $transaction);

                //allowing only unconfirmed transactions & confirmed transactions newer than $timestamp
                if($transaction_info['blocktime'] != 0 && $transaction_info['blocktime'] > $timestamp)
                {
                    //transaction doesn't exist
                    return [
                        'exists' => false,
                        'txid' => ""
                    ];
                }

                foreach($transaction_info['vout'] as $vout)
                {
		    
	                  $formattedamount = $vout['value'];
	           		
                    if($formattedamount == $amount && $vout['memo'] == $memo && $vout['address'] == strtolower($address))
                    {
                        return [
                            'exists' => true,
                            'txid' => $transaction
                        ];
                    }
                }
            }

        }
        catch(\Throwable $e)
        {
            throw $e;
        }
        
    }

    //
    // Check how many confirmations does $txid have (varchar)
    //
    public function checkConfirmations($txid)
    {
        try{
            $transaction = $this->getTransaction($txid);
            return $transaction['confirmations'];
        }
        catch (\Throwable $e){
            throw $e;
        }
    }

    //
    //Get transactions from address
    //
    public function getAddressTransactions($address)
    {
        try{
            $addressEndpoint = $this->explorer_url . "address/$address";            
            $transactions = $this->client->request('GET', $addressEndpoint);

            //convert response into array
            $transactions_array = (json_decode($transactions->getBody()->getContents(), true))['transactions'];
            return $transactions_array;
        }
        catch (\Throwable $e){
            throw $e;
        }
    }

    //
    //Get trasnsaction
    //
    public function getTransaction($user, $txid)
    {
        try{
            $transactionEndpoint = $this->explorer_url . "tx/$user/$txid"; 
            $transaction = $this->client->request('GET', $transactionEndpoint);

            //convert response into array
            $transaction = json_decode($transaction->getBody()->getContents(), true);

            return $transaction;
        }
        catch (\Throwable $e){
            throw $e;
        }
    }

}
