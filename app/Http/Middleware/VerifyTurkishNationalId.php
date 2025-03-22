<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Symfony\Component\HttpFoundation\Response;

class VerifyTurkishNationalId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $nationalId = $request->input('national_id');
        $name = mb_strtoupper(trim($request->input('name')), 'UTF-8');
        $surname = mb_strtoupper(trim($request->input('surname')), 'UTF-8');
        $birthYear = (int) $request->input('birth_year');

        if (!$nationalId || !$name || !$surname || !$birthYear) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields for TC Kimlik verification'
            ], 422);
        }

        try {
            // Clean up multiple spaces and convert to single space
            $name = preg_replace('/\s+/', ' ', trim($name));
            $surname = preg_replace('/\s+/', ' ', trim($surname));
            
            // Remove any non-numeric characters from TC Kimlik
            $nationalId = preg_replace('/\D/', '', $nationalId);

            // Convert standard characters to Turkish uppercase equivalents
            $standardToTurkish = [
                'I' => 'İ',
                'i' => 'İ',
                'ı' => 'I',
                'Ğ' => 'Ğ',
                'ğ' => 'Ğ',
                'Ü' => 'Ü',
                'ü' => 'Ü',
                'Ş' => 'Ş',
                'ş' => 'Ş',
                'Ö' => 'Ö',
                'ö' => 'Ö',
                'Ç' => 'Ç',
                'ç' => 'Ç'
            ];
            
            $name = strtr($name, $standardToTurkish);
            $surname = strtr($surname, $standardToTurkish);

            Log::info('Starting TC Kimlik verification with:', [
                'name' => $name,
                'surname' => $surname,
                'birthYear' => $birthYear,
                'nationalId' => $nationalId
            ]);

            $client = new SoapClient(
                "https://tckimlik.nvi.gov.tr/Service/KPSPublic.asmx?WSDL",
                [
                    'trace' => true,
                    'exceptions' => true,
                    'soap_version' => SOAP_1_1,
                    'encoding' => 'UTF-8',
                    'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false
                        ]
                    ])
                ]
            );

            $params = [
                'TCKimlikNo' => $nationalId,
                'Ad' => $name,
                'Soyad' => $surname,
                'DogumYili' => $birthYear
            ];

            Log::info('Sending SOAP request with parameters:', $params);

            $result = $client->TCKimlikNoDogrula($params);

            Log::info('SOAP Request Headers:', ['headers' => $client->__getLastRequestHeaders()]);
            Log::info('SOAP Request:', ['request' => $client->__getLastRequest()]);
            Log::info('SOAP Response Headers:', ['headers' => $client->__getLastResponseHeaders()]);
            Log::info('SOAP Response:', ['response' => $client->__getLastResponse()]);

            if (!isset($result->TCKimlikNoDogrulaResult)) {
                Log::error('Invalid response structure:', ['result' => $result]);
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid response from TC Kimlik service',
                    'response' => $result
                ], 500);
            }

            if (!$result->TCKimlikNoDogrulaResult) {
                Log::warning('TC Kimlik verification failed:', [
                    'provided_data' => [
                        'name' => $name,
                        'surname' => $surname,
                        'birthYear' => $birthYear,
                        'nationalId' => $nationalId
                    ]
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'One or more of the provided details do not match the official records. Please check:
                    1. Your TC Kimlik number is exactly correct
                    2. Your name is exactly as it appears on your ID (including spaces)
                    3. Your surname is exactly as it appears on your ID (including Turkish characters)
                    4. Your birth year is correct',
                    'details' => [
                        'name' => $name,
                        'surname' => $surname,
                        'birthYear' => $birthYear,
                        'nationalId' => $nationalId
                    ]
                ], 422);
            }

            Log::info('TC Kimlik verification successful');
            return $next($request);

        } catch (\Exception $e) {
            Log::error('TC Kimlik verification error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error verifying TC Kimlik: ' . $e->getMessage()
            ], 500);
        }
    }
} 