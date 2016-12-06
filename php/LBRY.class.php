<?php

class LBRYApiException extends Exception {}

class LBRY
{
  const LBRYNET_SERVER_ADDR = 'http://localhost:5279/lbryapi';

  protected static $requestId = 0;

  public static function api($method, array $params = [])
  {
    try
    {
      $ch = curl_init();

      if ($ch === false || $ch === null)
      {
        throw new LBRYApiException('Unable to initialize cURL');
      }

      curl_setopt_array($ch, [
        CURLOPT_URL            => static::LBRYNET_SERVER_ADDR,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['content-type: text/plain;charset=UTF-8'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 600,
        CURLOPT_POSTFIELDS     => json_encode([
          'id'      => ++static::$requestId,
          'jsonrpc' => '2.0',
          'method'  => $method,
          'params'  => $params ? [$params] : []
        ])
      ]);

      $rawResponse = curl_exec($ch);

      if (curl_errno($ch))
      {
        throw new LBRYApiException('Curl error: ' . $ch);
      }

      $responseContent = $rawResponse ? json_decode($rawResponse, true) : [];

      curl_close($ch);

      return  $responseContent;
    }
    catch (LBRYApiException $e)
    {
      throw new RuntimeException('Unable to connect to LBRYnet server - is LBRY daemon running at ' . static::LBRYNET_SERVER_ADDR . ' ? Initial error: ' . $e->getMessage());
    }
  }
}