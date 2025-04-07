<?php

namespace App\Services;

use App\Models\Bank;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MyFatoorahService
{
    protected $apiKey;

    protected $baseUrl;

    public function __construct()
    {
        // $this->apiKey = config('myfatoorah.api_key');
        $this->apiKey = 'rLtt6JWvbUHDDhsZnfpAhpYk4dxYDQkbcPTyGaKp2TYqQgG7FGZ5Th_WD53Oq8Ebz6A53njUoo1w3pjU1D4vs_ZMqFiz_j0urb_BH9Oq9VZoKFoJEDAbRZepGcQanImyYrry7Kt6MnMdgfG5jn4HngWoRdKduNNyP4kzcp3mRv7x00ahkm9LAK7ZRieg7k1PDAnBIOG3EyVSJ5kK4WLMvYr7sCwHbHcu4A5WwelxYK0GMJy37bNAarSJDFQsJ2ZvJjvMDmfWwDVFEVe_5tOomfVNt6bOg9mexbGjMrnHBnKnZR1vQbBtQieDlQepzTZMuQrSuKn-t5XZM7V6fCW7oP-uXGX-sMOajeX65JOf6XVpk29DP6ro8WTAflCDANC193yof8-f5_EYY-3hXhJj7RBXmizDpneEQDSaSz5sFk0sV5qPcARJ9zGG73vuGFyenjPPmtDtXtpx35A-BVcOSBYVIWe9kndG3nclfefjKEuZ3m4jL9Gg1h2JBvmXSMYiZtp9MR5I6pvbvylU_PP5xJFSjVTIz7IQSjcVGO41npnwIxRXNRxFOdIUHn0tjQ-7LwvEcTXyPsHXcMD8WtgBh-wxR8aKX7WPSsT1O8d8reb2aR7K3rkV3K82K_0OgawImEpwSvp9MNKynEAJQS6ZHe_J_l77652xwPNxMRTMASk1ZsJL';
        //  $this->apiKey = "F_VcRHESpr0NG3lQoWd2mcfpCAix5Sos7Q64Og3C-ONKPFMt6_5rs6b4b46thDS4avq4g4yQnBSlu9WXpdPNQP8Q6lPX9qiVg8Xt6clRlR2tgavdnrDlmdSqJdoeBbHDFqk6BH5GJqpaTnhmTHRIqvY3BJ-6COLcqktWpzpWEm_nT5Rr2tvB_BWFsZn2qBV3iGaRbiiKRU79znR7QsiEKDjCOz-EASFw_eWI-CBUff7r81F00G-mBS5VD-v6iBYDZXX1SLPJ6xKy-aRg3p2Re9mZ0JtuHTPr7J0B-lvFLS8J3iyg2V1DM1Zz1apkVFzKLL7rCW_d-cbSXPgX7eqOo7vBO3zDZezGHPQxp4YF0RFj_BRfc8ZeyvONfVQ9O33vlSw1xFSWejEc0XBNvvVZy3z4bGQowx3-H0Y2uSF6LxhH87jxLhbfzaI7HSL_tXePwRr-gI-jNizkR7VFnxPsbkbTGE2Qlnptz5yHtRyIql873uF-WvJULMmh8S_g2CxUfK2iSpotw5xu2IzXE-rm9VdJoXAL1lbpORy6tuwhey2W7qdnf9jwcvOz1obs2d8bp6YSVKHXLZd7D6Vr56pnZkJAqkOJPab86YP4BBgq0r0nHT90Nmt0OcpL6LHGpK--FovmE8iqDYhRmnRm2UHdsRtl17YAJBwor0-cTVEFMuCzkpxPL-db2l1m0-gtoWemPs47ajQb6NUcPlDGc9Rv340n9r7q3kw1LBCIcwDKvbmVPYn-";
        $this->baseUrl = 'https://apitest.myfatoorah.com';

    }

    protected function get($endpoint)
    {
        try {
            \Log::info('Sending GET request to MyFatoorah:', ['endpoint' => $endpoint]);
            \Log::info('API Key:', ['apiKey' => $this->apiKey]);
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl.$endpoint);

            // dd($this->baseUrl . $endpoint);

            return $response->json() ?? [];

        } catch (\Exception $e) {
            \Log::error('MyFatoorah API Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    protected function post($endpoint, $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->post($this->baseUrl.$endpoint, $data);

            return $response->json();

        } catch (\Exception $e) {
            \Log::error('MyFatoorah API Exception', [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    protected function put($endpoint, $data)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ])->put($this->baseUrl.$endpoint, $data);

            return $response->json();

        } catch (\Exception $e) {
            \Log::error('MyFatoorah API Exception', [
                'endpoint' => $endpoint,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createSupplier(array $data)
    {

        return $this->post('/v2/CreateSupplier', [
            'SupplierName' => $data['SupplierName'],
            'BankAccount' => $data['BankAccount'],
            'BankName' => $data['BankName'],
            'BankAccountHolderName' => $data['AccountHolderName'],
            'IBAN' => $data['IBAN'],
            'Email' => $data['Email'],
            'Mobile' => $data['Mobile'],
            'IsActive' => $data['IsActive'],
            'BankId' => $data['BankId'],
        ]);

    }

    public function updateSupplier(string $supplierCode, array $data)
    {
        return $this->post('/v2/EditSupplier', [
            'SupplierCode' => $supplierCode,
            'SupplierName' => $data['SupplierName'],
            'Email' => $data['Email'],
            'Mobile' => $data['Mobile'],
            'BankId' => $data['BankId'],
            'BankAccount' => $data['BankAccount'],
            'BankName' => $data['BankName'],
            'BankAccountHolderName' => $data['AccountHolderName'],
            'IBAN' => $data['IBAN'],
            'IsActive' => $data['IsActive'] ?? true,
        ]);
    }

    public function createBankTransferInvoice(array $data)
    {

        return $this->post('/v2/SendPayment', [
            'NotificationOption' => 'ALL',
            'CustomerName' => $data['CustomerName'],
            'DisplayCurrencyIso' => $data['DisplayCurrencyIso'],
            'MobileCountryCode' => '+966',
            'CustomerMobile' => $data['CustomerMobile'],
            'CustomerEmail' => $data['CustomerEmail'],
            'InvoiceValue' => $data['InvoiceValue'],
            'CallBackUrl' => $data['CallBackUrl'],
            'ErrorUrl' => $data['ErrorUrl'],
            'Language' => 'ar',
            'Suppliers' => $data['Suppliers'],
            'PaymentMethodId' => $data['PaymentMethodId'],
        ]);
    }

    public function syncBanks()
    {
        try {
            $banks = $this->getBanks();

            \Log::info('Starting bank sync', [
                'banks_count' => count($banks),
            ]);

            if (empty($banks)) {
                \Log::warning('No banks received from MyFatoorah');

                return false;
            }

            $syncedCount = 0;
            foreach ($banks as $bank) {
                if (! isset($bank['Value']) || ! isset($bank['Text'])) {
                    \Log::warning('Invalid bank data', ['bank' => $bank]);

                    continue;
                }

                try {
                    $result = Bank::updateOrCreate(
                        ['myfatoorah_bank_id' => $bank['Value']],
                        [
                            'name' => $bank['Text'],
                            'is_active' => true,
                        ]
                    );
                    \Log::info('Bank synced', [
                        'bank_id' => $result->id,
                        'name' => $result->name,
                    ]);
                    $syncedCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to sync bank', [
                        'bank' => $bank,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \Log::info('Bank sync completed', [
                'total_synced' => $syncedCount,
            ]);

            return $syncedCount > 0;

        } catch (\Exception $e) {
            \Log::error('Bank sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function getBanks()
    {
        \Log::info('Fetching banks from MyFatoorah...');

        return $this->get('/v2/GetBanks');
    }

    public function createPayment(array $data, $suppliers = [])
    {
        $postFields = [
            'InvoiceValue' => $data['amount'],
            'CustomerName' => $data['name'],
            'CustomerEmail' => $data['email'],
            'CustomerMobile' => $data['mobile'],
            'CustomerReference' => $data['id'],
            'Language' => app()->getLocale() ?? 'ar',
            'Currency' => 'SAR',
            'DisplayCurrencyIso' => 'SAR',
            'CallBackUrl' => $data['return_url'],
            'ErrorUrl' => $data['error_url'] ?? $data['return_url'],
            'PaymentMethodId' => '2', //  2 = Visa/MasterCard
            'InvoiceItems' => [
                [
                    'ItemName' => $data['description'],
                    'Quantity' => 1,
                    'UnitPrice' => $data['amount'],
                    'VatRate' => $data['vat'] ?? 0,
                ],
            ],

        ];

        if (! empty($suppliers)) {
            foreach ($suppliers as $supplier) {
                $postFields['Suppliers'][] = [
                    'SupplierCode' => $supplier['supplier_code'],
                    'ProposedShare' => $supplier['subtotal'],
                    'InvoiceShare' => $supplier['invoice_share'],
                ];
            }
        }

        return $this->post('/v2/ExecutePayment', $postFields);
        // $payment['Data']['InvoiceURL']
    }

    public function getPaymentStatus($paymentId)
    {
        $data = [
            'Key' => $paymentId,
            'KeyType' => 'PaymentId', //  "InvoiceId" , "InvoiceReference"
        ];

        return $this->post('/v2/GetPaymentStatus', $data);
    }

    public function transferBalance($data, $type)
    {
        // $type  = pull , push
        $request = [
            'SupplierCode' => $data['supplier_code'],
            'TransferAmount' => $data['amount'],
            'TransferType' => $type,
            'InternalNotes' => $data['notes'] ?? '',
        ];

        return $this->post('/v2/TransferBalance', $request);
    }

    public function getSuppliers()
    {
        return $this->get('/v2/GetSuppliers');
    }

    public function getSupplierDashboard($supplierCode)
    {
        try {
            return $this->get("/v2/GetSupplierDashboard?SupplierCode={$supplierCode}");
        } catch (\Exception $e) {
            Log::error('MyFatoorah get supplier dashboard failed', [
                'error' => $e->getMessage(),
                'supplier_code' => $supplierCode,
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
