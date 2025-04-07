<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class TransactionFilter
{
    protected $query;

    protected $filters;

    public function __construct(Builder $query, array $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function apply(): Builder
    {
        foreach ($this->filters as $filter => $value) {
            if (method_exists($this, $filter) && ! empty($value)) {
                $this->$filter($value);
            }
        }

        return $this->query;
    }

    protected function date_from($value)
    {
        $this->query->whereDate('created_at', '>=', $value);
    }

    protected function date_to($value)
    {
        $this->query->whereDate('created_at', '<=', $value);
    }

    protected function status($value)
    {
        $this->query->where('status', $value);
    }

    protected function payment_type($value)
    {
        $this->query->where('payment_type', $value);
    }

    protected function type($value)
    {
        $this->query->where('transactionable_type', match ($value) {
            'contract' => 'App\Models\Contract',
            'subscription' => 'App\Models\ProviderSubscription',
            default => $value
        });
    }

    protected function search($value)
    {
        $this->query->where(function ($q) use ($value) {
            $q->whereHas('transactionable', function ($q) use ($value) {
                $q->where('id', 'like', "%{$value}%")
                    ->orWhere(function ($q) use ($value) {
                        $q->when($q->getModel() instanceof \App\Models\Contract, function ($q) use ($value) {
                            $q->whereHas('hotel', function ($q) use ($value) {
                                $q->where('name', 'like', "%{$value}%");
                            });
                        });
                    })
                    ->orWhere(function ($q) use ($value) {
                        $q->when($q->getModel() instanceof \App\Models\Contract, function ($q) use ($value) {
                            $q->whereHas('services.provider', function ($q) use ($value) {
                                $q->where('name', 'like', "%{$value}%");
                            });
                        })
                            ->when($q->getModel() instanceof \App\Models\ProviderSubscription, function ($q) use ($value) {
                                $q->whereHas('provider', function ($q) use ($value) {
                                    $q->where('name', 'like', "%{$value}%");
                                });
                            });
                    });
            })
                ->orWhere('gateway_transaction_id', 'like', "%{$value}%")
                ->orWhere('amount', 'like', "%{$value}%");
        });
    }
}
