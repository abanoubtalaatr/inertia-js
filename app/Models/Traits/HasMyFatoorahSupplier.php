<?php

namespace App\Models\Traits;

use App\Models\HotelProfile;
use App\Models\ProviderProfile;
use App\Services\MyFatoorahService;

trait HasMyFatoorahSupplier
{
    protected static function bootHasMyFatoorahSupplier()
    {
        static::saved(function ($model) {

            if ($model->isDirty([
                'name',
                'email',
                'phone',
                'is_active',
                'bank_account_number',
                'bank_id',
                'bank_name',
                'bank_account_holder_name',
                'iban',
                'swift_code',
            ])) {
                $model->syncWithMyFatoorah();
            }
        });
    }

    public function syncWithMyFatoorah()
    {
        $myFatoorahService = new MyFatoorahService;

        try {
            if ($this instanceof HotelProfile || $this instanceof ProviderProfile) {
                $account = $this->account;
                $profile = $this;
            } else {
                $account = $this;
                $profile = $this->type === 'hotel'
                    ? $this->hotel()->with('bank')->first()
                    : $this->provider()->with('bank')->first();
            }

            if ($profile || $account) {

                $data = [
                    'SupplierName' => $account->name ?? '',
                    'Email' => $account->email ?? '',
                    'Mobile' => $account->phone ?? '',
                    'BankId' => $profile->bank?->myfatoorah_bank_id ?? '',
                    'BankAccount' => $profile->bank_account_number ?? '',
                    'BankName' => $profile->bank_name ?? '',
                    'AccountHolderName' => $profile->bank_account_holder_name ?? '',
                    'IBAN' => $profile->iban ?? '',
                    'IsActive' => $account->is_active ?? true,
                ];

                if (! $account->myfatoorah_supplier_code) {

                    $response = $myFatoorahService->createSupplier($data);

                    $account->update([
                        'myfatoorah_supplier_code' => $response['Data']['SupplierCode'],
                    ]);

                } else {

                    $response = $myFatoorahService->updateSupplier(
                        $account->myfatoorah_supplier_code,
                        $data
                    );

                }

                if (! $response['IsSuccess']) {
                    if (request()->is('api/*')) {

                        $errors = collect($response['FieldsErrors'] ?? [])->map(function ($error) {
                            switch ($error['Name']) {
                                case 'BankId':
                                    return 'رقم البنك غير صحيح';
                                case 'IbanValue':
                                    return 'رقم الآيبان غير صحيح';
                                default:
                                    return $error['Error'];
                            }
                        })->join(' | ');

                        throw new \Exception('خطأ في البيانات: '.$errors);
                    }
                }

                return true;

            } else {

                \Log::info('Skipping MyFatoorah sync - no profile/account yet', [
                    'model_type' => get_class($this),
                    'model_id' => $this->id,
                ]);

                return false;
            }

        } catch (\Exception $e) {
            \Log::error('MyFatoorah sync failed', [
                'model_type' => get_class($this),
                'model_id' => $this->id,
                'error' => $e->getMessage(),
                'data' => $data ?? null,
                'response' => $response ?? null,
            ]);

            throw new \Exception($e->getMessage());
        }
    }
}
