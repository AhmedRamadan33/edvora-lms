<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $model)
    {
        parent::__construct($model);
    }

    public function upsertForOrder(int $orderId, array $data): Payment
    {
        return $this->query()->updateOrCreate(
            ['order_id' => $orderId],
            $data
        );
    }

    public function findByProviderReference(string $provider, string $reference): ?Payment
    {
        return $this->query()
            ->where('provider', $provider)
            ->where('provider_reference', $reference)
            ->first();
    }
}
